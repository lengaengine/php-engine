<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

final class SphereRenderer extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SphereRenderer');
    }

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
            NativeEngine::call('sphere_renderer_set_center', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('sphere_renderer_set_radius', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     center?: array{x?: float, y?: float, z?: float},
     *     radius?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     center?: array{x?: float, y?: float, z?: float},
         *     radius?: float,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('sphere_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
