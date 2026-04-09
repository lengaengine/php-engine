<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class SphereRenderer extends Component
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
            \lenga_internal_sphere_renderer_set_center($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_sphere_renderer_set_radius($this->componentId, $value);
        }
    }

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_sphere_renderer_set_material_path($this->componentId, $value);
        }
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getColor(): array
    {
        /** @var array{color?: array{r?: int, g?: int, b?: int, a?: int}} $state */
        $state = $this->getState();
        $color = $state['color'] ?? [];

        return [
            'r' => (int) ($color['r'] ?? 255),
            'g' => (int) ($color['g'] ?? 255),
            'b' => (int) ($color['b'] ?? 255),
            'a' => (int) ($color['a'] ?? 255),
        ];
    }

    public function setColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_sphere_renderer_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     center?: array{x?: float, y?: float, z?: float},
     *     radius?: float,
     *     materialPath?: string,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     center?: array{x?: float, y?: float, z?: float},
         *     radius?: float,
         *     materialPath?: string,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_sphere_renderer_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
