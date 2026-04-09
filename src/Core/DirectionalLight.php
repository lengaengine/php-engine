<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class DirectionalLight extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'DirectionalLight');
    }

    #[Min(0)]
    public float $intensity {
        get {
            return (float) ($this->getState()['intensity'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_directional_light_set_intensity($this->componentId, $value);
        }
    }

    public bool $shadowsEnabled {
        get {
            return (bool) ($this->getState()['shadowsEnabled'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_directional_light_set_shadows_enabled($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $shadowStrength {
        get {
            return (float) ($this->getState()['shadowStrength'] ?? 0.65);
        }

        set(float $value) {
            \lenga_internal_directional_light_set_shadow_strength($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $shadowBias {
        get {
            return (float) ($this->getState()['shadowBias'] ?? 0.0025);
        }

        set(float $value) {
            \lenga_internal_directional_light_set_shadow_bias($this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $shadowProjectionSize {
        get {
            return (float) ($this->getState()['shadowProjectionSize'] ?? 18.0);
        }

        set(float $value) {
            \lenga_internal_directional_light_set_shadow_projection_size($this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $shadowDistance {
        get {
            return (float) ($this->getState()['shadowDistance'] ?? 28.0);
        }

        set(float $value) {
            \lenga_internal_directional_light_set_shadow_distance($this->componentId, $value);
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
        \lenga_internal_directional_light_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     intensity?: float,
     *     shadowsEnabled?: bool,
     *     shadowStrength?: float,
     *     shadowBias?: float,
     *     shadowProjectionSize?: float,
     *     shadowDistance?: float,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     intensity?: float,
         *     shadowsEnabled?: bool,
         *     shadowStrength?: float,
         *     shadowBias?: float,
         *     shadowProjectionSize?: float,
         *     shadowDistance?: float,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_directional_light_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
