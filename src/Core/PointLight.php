<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Internal\ColorBridge;
use function is_array;

/**
 * Lights a 3D scene from a point, shining equally in every direction, like a bulb or a torch.
 *
 * The light sits at its GameObject's position and reaches as far as
 * {@see PointLight::$range}. Change {@see PointLight::$intensity} over time
 * for effects such as a flickering flame.
 */
final class PointLight extends Component
{
    /**
     * Wraps an existing PointLight component.
     *
     * The engine creates PointLight wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PointLight');
    }

    /**
     * How bright the light is.
     *
     * `0.0` gives no light; higher values brighten everything the light
     * reaches. Defaults to `1.0`.
     */
    #[Min(0)]
    public float $intensity {
        get {
            return (float) ($this->getState()['intensity'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('point_light_set_intensity', $this->componentId, $value);
        }
    }

    /**
     * How far the light reaches from its position, in world units.
     *
     * Objects further away than the range are not lit by it. Defaults to `8.0`.
     */
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
     * The colour of the light.
     *
     * Defaults to white.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('point_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the light colour as red, green, blue and alpha values from 0 to 255.
     *
     * {@see PointLight::$color} gives the same colour as a {@see Color}.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getColor(): array
    {
        return $this->color->toRGBA();
    }

    /**
     * Sets the light colour from a {@see Color}, an array or separate channel values.
     *
     * `$light->setColor(255, 200, 100)` and
     * `$light->color = Color::fromRGBA(255, 200, 100, 255)` do the same.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A Color, an array of channels, or the red channel from 0 to 255.
     * @param int|null $green The green channel from 0 to 255, when `$red` is a number.
     * @param int|null $blue The blue channel from 0 to 255, when `$red` is a number.
     * @param int $alpha The alpha channel from 0 to 255, when `$red` is a number.
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
