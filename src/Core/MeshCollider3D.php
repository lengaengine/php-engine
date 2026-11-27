<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * A 3D collider that uses the triangles of a mesh or model asset as its shape.
 *
 * Use it for static level geometry such as floors, ramps, terrain, race tracks
 * and level shells. Moving objects should use primitive colliders such as
 * {@see BoxCollider3D}, {@see SphereCollider3D}, {@see CapsuleCollider3D} or
 * {@see CylinderCollider3D}.
 *
 * ```php
 * $collider = $this->gameObject->getComponent(MeshCollider3D::class);
 * $collider->meshPath = 'Assets/Models/Track.glb';
 * $collider->meshIndex = 0;
 * ```
 */
final class MeshCollider3D extends Component
{
    /**
     * Wraps the mesh collider component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'MeshCollider3D');
    }

    /**
     * The project-relative path to the 3D mesh or model asset used for collision.
     *
     * Defaults to `''`.
     */
    public string $meshPath {
        get {
            return (string) ($this->getState()['meshPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('mesh_collider3d_set_mesh_path', $this->componentId, $value);
        }
    }

    /**
     * The zero-based mesh index to use when the asset contains multiple meshes.
     *
     * Use {@see MeshCollider3D::getMeshCount()} to see how many meshes the asset has.
     * Defaults to `0`.
     */
    #[Min(0)]
    public int $meshIndex {
        get {
            return (int) ($this->getState()['meshIndex'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('mesh_collider3d_set_mesh_index', $this->componentId, $value);
        }
    }

    /**
     * The collider offset from the GameObject's position, in local space.
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
            NativeEngine::call('mesh_collider3d_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Whether this collider reports overlaps without producing physical collision response.
     *
     * Defaults to `false`.
     */
    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('mesh_collider3d_set_is_trigger', $this->componentId, $value);
        }
    }

    /**
     * The project-relative path to the 3D physics material asset assigned to this collider.
     *
     * Defaults to `''`, which means no material asset is assigned.
     */
    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('mesh_collider3d_set_material_path', $this->componentId, $value);
        }
    }

    /**
     * The resolved 3D physics material values currently applied to this collider.
     *
     * Assigning a material that has an asset path assigns that asset. Assigning
     * one without an asset path applies its friction and bounce values directly.
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
                NativeEngine::call('mesh_collider3d_set_material_path', $this->componentId, $value->assetPath);
                return;
            }

            NativeEngine::call('mesh_collider3d_set_material',
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
     * Returns the number of meshes loaded from the assigned asset.
     *
     * Returns 0 when no mesh asset is assigned, the asset could not be loaded,
     * or the runtime has not loaded the mesh data yet.
     */
    public function getMeshCount(): int
    {
        return (int) ($this->getState()['meshCount'] ?? 0);
    }

    /**
     * Returns true when this collider is currently touching another 3D collider.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders count.
     * @param int|null $layerMask Bit mask of the GameObject layers to check; `null` checks all layers.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('mesh_collider3d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * Returns the current 3D contacts involving this collider.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders are included.
     * @param int|null $layerMask Bit mask of the GameObject layers to include; `null` includes all layers.
     *
     * @return list<Collision3D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('mesh_collider3d_get_contacts',
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
     *     meshPath?: string,
     *     meshIndex?: int,
     *     meshCount?: int,
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
         *     meshPath?: string,
         *     meshIndex?: int,
         *     meshCount?: int,
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
        $state = NativeEngine::call('mesh_collider3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
