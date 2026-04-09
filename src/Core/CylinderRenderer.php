<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class CylinderRenderer extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CylinderRenderer');
    }

    public Vector3 $position {
        get {
            $position = $this->getState()['position'] ?? [];

            return new Vector3(
                (float) ($position['x'] ?? 0.0),
                (float) ($position['y'] ?? 0.0),
                (float) ($position['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            \lenga_internal_cylinder_renderer_set_position($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    #[Min(0)]
    public float $topRadius {
        get {
            return (float) ($this->getState()['topRadius'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_cylinder_renderer_set_top_radius($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $bottomRadius {
        get {
            return (float) ($this->getState()['bottomRadius'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_cylinder_renderer_set_bottom_radius($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 4.0);
        }

        set(float $value) {
            \lenga_internal_cylinder_renderer_set_height($this->componentId, $value);
        }
    }

    #[Min(3)]
    public int $totalSlices {
        get {
            return (int) ($this->getState()['totalSlices'] ?? 16);
        }

        set(int $value) {
            \lenga_internal_cylinder_renderer_set_total_slices($this->componentId, $value);
        }
    }

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_cylinder_renderer_set_material_path($this->componentId, $value);
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
        \lenga_internal_cylinder_renderer_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     position?: array{x?: float, y?: float, z?: float},
     *     topRadius?: float,
     *     bottomRadius?: float,
     *     height?: float,
     *     totalSlices?: int,
     *     materialPath?: string,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     position?: array{x?: float, y?: float, z?: float},
         *     topRadius?: float,
         *     bottomRadius?: float,
         *     height?: float,
         *     totalSlices?: int,
         *     materialPath?: string,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_cylinder_renderer_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
