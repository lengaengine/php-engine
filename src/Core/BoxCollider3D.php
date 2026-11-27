<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;

/**
 * A box-shaped collision shape for 3D physics.
 *
 * Use it for crates, walls, floors and other boxy objects.
 *
 * The engine calls `onCollisionEnter(Collision3D $collision)` and the
 * related methods on Behaviours of the GameObject when the collider touches
 * another collider. With a {@see Rigidbody3D} on the same GameObject the
 * shape moves under physics; without one it is a static shape.
 */
final class BoxCollider3D extends Component
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'BoxCollider3D');
    }

    /**
     * Width (X), height (Y) and depth (Z) of the box, in local units.
     *
     * The GameObject's scale is applied on top of this size.
     */
    public Vector3 $size {
        get {
            $state = $this->getState()['size'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('box_collider3d_set_size', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Position of the collider's centre relative to the GameObject's position, in local units.
     *
     * Defaults to `(0, 0, 0)`.
     */
    public Vector3 $offset {
        get {
            $state = $this->getState()['offset'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('box_collider3d_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Whether the collider is a trigger: it detects overlaps without blocking movement.
     *
     * Other colliders pass through a trigger, and the engine calls
     * `onTriggerEnter()`, `onTriggerStay()` and `onTriggerExit()` on
     * Behaviours instead of the collision methods. Use triggers for pickups,
     * checkpoints and sensor areas. Defaults to `false`.
     */
    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('box_collider3d_set_is_trigger', $this->componentId, $value);
        }
    }

    /**
     * Project-relative path of the {@see PhysicsMaterial3D} asset the collider uses.
     *
     * Set it to use the friction and bounciness stored in that asset. Empty
     * when the collider has no material asset. Defaults to `''`.
     */
    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('box_collider3d_set_material_path', $this->componentId, $value);
        }
    }

    /**
     * The friction and bounciness the collider uses when it touches other colliders.
     *
     * Reading it returns a copy; change the copy and assign it back to update
     * the collider. Assigning a material whose
     * {@see PhysicsMaterial3D::$assetPath} is set makes the collider use that
     * asset, the same as setting {@see BoxCollider3D::$materialPath}, and
     * ignores the material's other values.
     */
    public PhysicsMaterial3D $material {
        get {
            $state = $this->getState();
            /** @var array{
             *     dynamicFriction?: float|int,
             *     staticFriction?: float|int,
             *     friction?: float|int,
             *     bounciness?: float|int,
             *     frictionCombine?: string,
             *     bounceCombine?: string
             * } $materialState
             */
            $materialState = is_array($state['material'] ?? null) ? $state['material'] : [];

            return PhysicsMaterial3D::fromArray(
                $materialState,
                (string) ($state['materialPath'] ?? ''),
            );
        }

        set(PhysicsMaterial3D $value) {
            if ($value->assetPath !== '') {
                NativeEngine::call('box_collider3d_set_material_path', $this->componentId, $value->assetPath);
                return;
            }

            NativeEngine::call('box_collider3d_set_material',
                $this->componentId,
                $value->dynamicFriction,
                $value->staticFriction,
                $value->bounciness,
                $value->frictionCombine,
                $value->bounceCombine,
            );
        }
    }

    /**
     * Returns true when this collider is touching another 3D collider right now.
     *
     * Use it for quick checks such as "am I inside a trigger?" or "is this
     * hazard touching anything?". Call {@see BoxCollider3D::getContacts()} when you
     * need to know what it touches.
     *
     * @param bool $includeTriggers True to count overlaps with trigger colliders; false to count
     *     only solid contacts.
     * @param int|null $layerMask Bit mask of the layers whose colliders count, built with
     *     {@see Physics3D::layerMask()}. Null counts every layer.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('box_collider3d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * Returns the contacts this collider has with other 3D colliders right now.
     *
     * Each {@see Collision3D} describes one contact: the other GameObject and
     * collider, the contact point and normal. The list is empty when nothing
     * touches this collider.
     *
     * @param bool $includeTriggers True to include overlaps with trigger colliders; false to
     *     return only solid contacts.
     * @param int|null $layerMask Bit mask of the layers whose colliders count, built with
     *     {@see Physics3D::layerMask()}. Null counts every layer.
     * @return list<Collision3D> The current contacts.
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('box_collider3d_get_contacts',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );

        if (!is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (is_array($result)) {
                $contacts[] = Collision3D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision3D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     size?: array{x?: float|int, y?: float|int, z?: float|int},
     *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
     *     materialPath?: string,
     *     material?: array{
     *         dynamicFriction?: float|int,
     *         staticFriction?: float|int,
     *         friction?: float|int,
     *         bounciness?: float|int,
     *         frictionCombine?: string,
     *         bounceCombine?: string
     *     }
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     isTrigger?: bool,
         *     size?: array{x?: float|int, y?: float|int, z?: float|int},
         *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
         *     materialPath?: string,
         *     material?: array{
         *         dynamicFriction?: float|int,
         *         staticFriction?: float|int,
         *         friction?: float|int,
         *         bounciness?: float|int,
         *         frictionCombine?: string,
         *         bounceCombine?: string
         *     }
         * } $state
         */
        $state = NativeEngine::call('box_collider3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
