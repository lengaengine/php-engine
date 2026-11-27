<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

use function is_array;

/**
 * Draws a 3D capsule, a cylinder with rounded ends, at the GameObject's position.
 *
 * The capsule stands upright along the GameObject's Y axis and moves,
 * rotates and scales with its Transform. Set its look with the material
 * inherited from {@see Renderer}. Drawing a capsule does not make it
 * collide; add a {@see CapsuleCollider3D} for that.
 */
final class CapsuleRenderer extends Renderer
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
        parent::__construct($gameObject, $componentId, 'CapsuleRenderer');
    }

    /**
     * Position of the capsule's centre relative to the GameObject, in local units. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $center {
        get {
            $center = $this->getState()['center'] ?? [];

            return new Vector3(
                (float) ($center['x'] ?? 0.0),
                (float) ($center['y'] ?? 0.0),
                (float) ($center['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('capsule_renderer_set_center', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Radius of the capsule's body and rounded ends, in local units. Defaults to `0.5`.
     */
    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('capsule_renderer_set_radius', $this->componentId, $value);
        }
    }

    /**
     * Height of the capsule from end to end, including both rounded ends, in local units. Defaults to `2`.
     */
    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 2.0);
        }

        set(float $value) {
            NativeEngine::call('capsule_renderer_set_height', $this->componentId, $value);
        }
    }

    /**
     * Number of segments around the capsule. More segments look rounder and cost more to draw.
     *
     * The Inspector accepts 3 or more. Defaults to `16`.
     */
    #[Min(3)]
    public int $totalSlices {
        get {
            return (int) ($this->getState()['totalSlices'] ?? 16);
        }

        set(int $value) {
            NativeEngine::call('capsule_renderer_set_total_slices', $this->componentId, $value);
        }
    }

    /**
     * Number of segments that shape each rounded end. More segments look smoother.
     *
     * The Inspector accepts 1 or more. Defaults to `8`.
     */
    #[Min(1)]
    public int $totalRings {
        get {
            return (int) ($this->getState()['totalRings'] ?? 8);
        }

        set(int $value) {
            NativeEngine::call('capsule_renderer_set_total_rings', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     center?: array{x?: float, y?: float, z?: float},
     *     radius?: float,
     *     height?: float,
     *     totalSlices?: int,
     *     totalRings?: int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     center?: array{x?: float, y?: float, z?: float},
         *     radius?: float,
         *     height?: float,
         *     totalSlices?: int,
         *     totalRings?: int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('capsule_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
