<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Internal\ColorBridge;
use function is_array;

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
            NativeEngine::call('directional_light_set_intensity', $this->componentId, $value);
        }
    }

    public bool $shadowsEnabled {
        get {
            return (bool) ($this->getState()['shadowsEnabled'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('directional_light_set_shadows_enabled', $this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $shadowStrength {
        get {
            return (float) ($this->getState()['shadowStrength'] ?? 0.65);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_strength', $this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $shadowBias {
        get {
            return (float) ($this->getState()['shadowBias'] ?? 0.0025);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_bias', $this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $shadowProjectionSize {
        get {
            return (float) ($this->getState()['shadowProjectionSize'] ?? 18.0);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_projection_size', $this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $shadowDistance {
        get {
            return (float) ($this->getState()['shadowDistance'] ?? 28.0);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_distance', $this->componentId, $value);
        }
    }

    /**
     * The light color.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('directional_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the light color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getColor(): array
    {
        return $this->color->toRGBA();
    }

    /**
     * Sets the light color.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('directional_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
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
        $state = NativeEngine::call('directional_light_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
