<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A circular collision shape for 2D physics.
 *
 * Add it to a GameObject so it can collide with other 2D colliders. With a
 * {@see Rigidbody2D} on the same GameObject the circle moves under physics,
 * which suits balls and round characters; without one it is a static shape.
 * Set {@see CircleCollider2D::$isTrigger} to detect overlaps without
 * blocking.
 *
 * The engine calls `onCollisionEnter2D(Collision2D $collision)` and the
 * related methods on Behaviours of the GameObject when the circle touches
 * another collider. Query contacts at any time with
 * {@see CircleCollider2D::isTouching()} and {@see CircleCollider2D::getContacts()}.
 */
final class CircleCollider2D extends Component
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
        parent::__construct($gameObject, $componentId, 'CircleCollider2D');
    }

    /**
     * Radius of the circle, in world units before the GameObject's scale.
     */
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('circle_collider2d_set_radius', $this->componentId, $value);
        }
    }

    /**
     * Position of the collider's centre relative to the GameObject's position, in world units.
     *
     * Only X and Y are used. The offset grows with the GameObject's scale.
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
            NativeEngine::call('circle_collider2d_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Whether the collider is a trigger: it detects overlaps without blocking movement.
     *
     * Other colliders pass through a trigger, and the engine calls
     * `onTriggerEnter2D()`, `onTriggerStay2D()` and `onTriggerExit2D()` on
     * Behaviours of both GameObjects instead of the collision methods. Use
     * triggers for pickups, checkpoints and sensor areas. Defaults to `false`.
     */
    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('circle_collider2d_set_is_trigger', $this->componentId, $value);
        }
    }

    /**
     * Project-relative path of the {@see PhysicsMaterial2D} asset the collider uses.
     *
     * Set it to use the friction and bounciness stored in that asset. Empty
     * when the collider has no material asset. Defaults to `''`.
     */
    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('circle_collider2d_set_material_path', $this->componentId, $value);
        }
    }

    /**
     * The friction and bounciness the collider uses when it touches other colliders.
     *
     * Reading it returns a copy; change the copy and assign it back to update
     * the collider. Assigning a material whose
     * {@see PhysicsMaterial2D::$assetPath} is set makes the collider use that
     * asset, the same as setting {@see CircleCollider2D::$materialPath}, and
     * ignores the material's other values.
     */
    public PhysicsMaterial2D $material {
        get {
            $state = $this->getState();
            /** @var array{
             *     friction?: float|int,
             *     bounciness?: float|int,
             *     frictionCombine?: string,
             *     bounceCombine?: string
             * } $materialState
             */
            $materialState = \is_array($state['material'] ?? null) ? $state['material'] : [];

            return PhysicsMaterial2D::fromArray(
                $materialState,
                (string) ($state['materialPath'] ?? ''),
            );
        }

        set(PhysicsMaterial2D $value) {
            if ($value->assetPath !== '') {
                NativeEngine::call('circle_collider2d_set_material_path', $this->componentId, $value->assetPath);
                return;
            }

            NativeEngine::call('circle_collider2d_set_material',
                $this->componentId,
                $value->friction,
                $value->bounciness,
                $value->frictionCombine,
                $value->bounceCombine,
            );
        }
    }

    /**
     * Returns true when this collider is touching another 2D collider right now.
     *
     * Use it for quick checks such as "am I inside a trigger?" or "is this
     * hazard touching anything?". Call {@see CircleCollider2D::getContacts()} when you
     * need to know what it touches.
     *
     * @param bool $includeTriggers True to count overlaps with trigger colliders; false to count
     *     only solid contacts.
     * @param int|null $layerMask Bit mask of the layers whose colliders count, built with
     *     {@see Physics2D::layerMask()}. Null counts every layer.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('circle_collider2d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * Returns the contacts this collider has with other 2D colliders right now.
     *
     * Each {@see Collision2D} describes one contact: the other GameObject and
     * collider, the contact point and normal. The list is empty when nothing
     * touches this collider.
     *
     * @param bool $includeTriggers True to include overlaps with trigger colliders; false to
     *     return only solid contacts.
     * @param int|null $layerMask Bit mask of the layers whose colliders count, built with
     *     {@see Physics2D::layerMask()}. Null counts every layer.
     * @return list<Collision2D> The current contacts.
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('circle_collider2d_get_contacts',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $contacts[] = Collision2D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision2D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     radius?: float|int,
     *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
     *     materialPath?: string,
     *     material?: array{
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
         *     radius?: float|int,
         *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
         *     materialPath?: string,
         *     material?: array{
         *         friction?: float|int,
         *         bounciness?: float|int,
         *         frictionCombine?: string,
         *         bounceCombine?: string
         *     }
         * } $state
         */
        $state = NativeEngine::call('circle_collider2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
