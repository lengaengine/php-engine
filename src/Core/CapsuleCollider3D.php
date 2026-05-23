<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * Represents a vertical 3D capsule collider attached to a GameObject.
 */
final class CapsuleCollider3D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CapsuleCollider3D');
    }

    /**
     * The local radius of the capsule's rounded ends and cylindrical body.
     */
    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('capsule_collider3d_set_radius', $this->componentId, $value);
        }
    }

    /**
     * The local height of the capsule from end to end.
     */
    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 2.0);
        }

        set(float $value) {
            NativeEngine::call('capsule_collider3d_set_height', $this->componentId, $value);
        }
    }

    /**
     * The collider center offset relative to the GameObject transform.
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
            NativeEngine::call('capsule_collider3d_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Whether this collider reports overlaps without producing physical collision response.
     */
    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('capsule_collider3d_set_is_trigger', $this->componentId, $value);
        }
    }

    /**
     * The project-relative path to the 3D physics material asset assigned to this collider.
     */
    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('capsule_collider3d_set_material_path', $this->componentId, $value);
        }
    }

    /**
     * The resolved 3D physics material values currently applied to this collider.
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
                NativeEngine::call('capsule_collider3d_set_material_path', $this->componentId, $value->assetPath);
                return;
            }

            NativeEngine::call('capsule_collider3d_set_material',
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
     * Trigger contacts are counted when $includeTriggers is true. $layerMask filters
     * other colliders by their GameObject layer.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('capsule_collider3d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * Returns the current 3D contacts involving this collider.
     *
     * Trigger contacts are included when $includeTriggers is true. $layerMask filters
     * other colliders by their GameObject layer.
     *
     * @return list<Collision3D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('capsule_collider3d_get_contacts',
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
     * Moves this capsule kinematically and slides along blocking 3D colliders.
     *
     * @deprecated Use CharacterController::move() for kinematic movement.
     */
    public function moveAndSlide(
        Vector3 $delta,
        float $skinWidth = 0.05,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): KinematicMove3DResult {
        $transform = $this->gameObject->transform;
        $result = Physics3D::moveAndSlideCapsule(
            $transform->position,
            $this->radius,
            $this->height,
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
     *     height?: float|int,
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
         *     height?: float|int,
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
        $state = NativeEngine::call('capsule_collider3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
