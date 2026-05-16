<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

use function is_array;

/**
 * Renders a 3D capsule primitive using the GameObject's Transform.
 */
final class CapsuleRenderer extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CapsuleRenderer');
    }

    /**
     * Local-space offset of the capsule center.
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
     * Capsule radius in local units.
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
     * Total capsule height in local units, including both rounded ends.
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
     * Number of horizontal subdivisions around the capsule.
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
     * Number of vertical subdivisions used to shape each rounded cap.
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
