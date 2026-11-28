<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * 3D collider with a sphere shape.
 *
 * Add it to a GameObject to give it a round shape for 3D physics, for example a ball or
 * a pickup's trigger zone. Pair it with a {@see Rigidbody3D} for an object that moves
 * under physics. Set {@see SphereCollider3D::$isTrigger} to detect overlaps without
 * blocking movement. The sphere follows the GameObject's {@see Transform}, shifted by
 * {@see SphereCollider3D::$offset}.
 */
final class SphereCollider3D extends Component
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
        parent::__construct($gameObject, $componentId, 'SphereCollider3D');
    }

    /**
     * Sphere radius in local units, before the GameObject's scale is applied.
     *
     * The Inspector does not accept values below `0`. Defaults to `0.5`.
     */
    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('sphere_collider3d_set_radius', $this->componentId, $value);
        }
    }

    /**
     * Offset of the sphere's center from the GameObject's position, in local space.
     *
     * Reading it returns a new Vector3, so assign a whole vector to change it. Defaults
     * to `(0, 0, 0)`.
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
            NativeEngine::call('sphere_collider3d_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Whether this collider reports overlaps without producing physical collision response.
     *
     * A trigger lets other colliders pass through it and reports when they enter, stay
     * and leave. Defaults to `false`.
     */
    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('sphere_collider3d_set_is_trigger', $this->componentId, $value);
        }
    }

    /**
     * The project-relative path to the 3D physics material asset assigned to this collider.
     *
     * The material sets friction and bounciness. Empty when no material asset is assigned.
     * Defaults to an empty string.
     */
    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('sphere_collider3d_set_material_path', $this->componentId, $value);
        }
    }

    /**
     * The resolved 3D physics material values currently applied to this collider.
     *
     * Assigning a {@see PhysicsMaterial3D} with an asset path assigns that asset, like
     * setting {@see SphereCollider3D::$materialPath}. Assigning one without an asset path
     * applies its friction, bounciness and combine modes directly to this collider.
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
                NativeEngine::call('sphere_collider3d_set_material_path', $this->componentId, $value->assetPath);
                return;
            }

            NativeEngine::call('sphere_collider3d_set_material',
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
     * Returns true when this collider is currently touching another 3D collider.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers whose colliders count; `null`
     *     checks all layers.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('sphere_collider3d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * Returns the current 3D contacts involving this collider.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders are included.
     * @param int|null $layerMask Bit mask of the layers whose colliders are included;
     *     `null` includes all layers.
     * @return list<Collision3D> One entry per contact. Empty when nothing is touching.
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('sphere_collider3d_get_contacts',
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
     * Moves this sphere kinematically and slides along blocking 3D colliders.
     *
     * The move uses {@see Physics3D::moveAndSlideSphere()} with this collider's radius and
     * offset, ignores the collider's own GameObject, and assigns the resulting position
     * to the Transform.
     *
     * @deprecated Use {@see CharacterController::move()} for kinematic movement.
     *
     * @param Vector3 $delta The movement to attempt, in world units.
     * @param float $skinWidth Gap kept between the sphere and blocking colliders, in world units.
     * @param bool $includeTriggers Whether trigger colliders block the move.
     * @param int|null $layerMask Bit mask of the layers that can block the move; `null`
     *     checks all layers.
     * @return KinematicMove3DResult The resolved position, the movement that was applied
     *     and whether it collided or slid.
     */
    public function moveAndSlide(
        Vector3 $delta,
        float $skinWidth = 0.05,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): KinematicMove3DResult {
        $transform = $this->gameObject->transform;
        $result = Physics3D::moveAndSlideSphere(
            $transform->position,
            $this->radius,
            $delta,
            $this->gameObject,
            $this->offset,
            $skinWidth,
            $includeTriggers,
            $layerMask,
        );

        $transform->position = $result->position;
        return $result;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     radius?: float|int,
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
         *     radius?: float|int,
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
        $state = NativeEngine::call('sphere_collider3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
