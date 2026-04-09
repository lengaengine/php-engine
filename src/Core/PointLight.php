<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class PointLight extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PointLight');
    }

    #[Min(0)]
    public float $intensity {
        get {
            return (float) ($this->getState()['intensity'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_point_light_set_intensity($this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $range {
        get {
            return (float) ($this->getState()['range'] ?? 8.0);
        }

        set(float $value) {
            \lenga_internal_point_light_set_range($this->componentId, $value);
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
        \lenga_internal_point_light_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     intensity?: float,
     *     range?: float,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     intensity?: float,
         *     range?: float,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_point_light_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
