<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Internal\ColorBridge;
use function is_array;

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
            NativeEngine::call('point_light_set_intensity', $this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $range {
        get {
            return (float) ($this->getState()['range'] ?? 8.0);
        }

        set(float $value) {
            NativeEngine::call('point_light_set_range', $this->componentId, $value);
        }
    }

    /**
     * The light color.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('point_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
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
        NativeEngine::call('point_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
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
        $state = NativeEngine::call('point_light_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
