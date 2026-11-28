<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Internal\ColorBridge;
use function is_array;

/**
 * A light that shines in one direction across the whole 3D scene, like sunlight.
 *
 * The light has no position: every object is lit from the same direction, which
 * comes from the rotation of the light's GameObject. Rotate its
 * {@see Component::$gameObject} transform to change the time of day or the
 * shadow angle. A directional light can also cast shadows, tuned with the
 * shadow properties below.
 */
final class DirectionalLight extends Component
{
    /**
     * Wraps the directional light component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'DirectionalLight');
    }

    /**
     * How bright the light is. Higher values are brighter.
     *
     * Defaults to `1.0`.
     */
    #[Min(0)]
    public float $intensity {
        get {
            return (float) ($this->getState()['intensity'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_intensity', $this->componentId, $value);
        }
    }

    /**
     * Whether the light casts shadows. Shown as Cast Shadows in the Inspector.
     *
     * Defaults to `true`.
     */
    public bool $shadowsEnabled {
        get {
            return (bool) ($this->getState()['shadowsEnabled'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('directional_light_set_shadows_enabled', $this->componentId, $value);
        }
    }

    /**
     * How dark the shadows this light casts are.
     *
     * 0 gives no visible shadow and higher values give darker shadows. Values
     * around `0.55` to `0.75` suit most scenes. Defaults to `0.65`.
     */
    #[Min(0)]
    public float $shadowStrength {
        get {
            return (float) ($this->getState()['shadowStrength'] ?? 0.65);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_strength', $this->componentId, $value);
        }
    }

    /**
     * A small depth offset that stops surfaces from shadowing themselves.
     *
     * Raise it when you see striped or speckled shadow artifacts on lit
     * surfaces; lower it when shadows look detached from the objects that cast
     * them. Values around `0.0015` to `0.0035` suit most scenes. Defaults to `0.0025`.
     */
    #[Min(0)]
    public float $shadowBias {
        get {
            return (float) ($this->getState()['shadowBias'] ?? 0.0025);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_bias', $this->componentId, $value);
        }
    }

    /**
     * The width of the area the shadow covers, in world units. Shown as Shadow
     * Size in the Inspector.
     *
     * Smaller values give sharper shadows over a smaller area. Larger values
     * cover more of the scene, with the same shadow detail stretched over it.
     * Values around `14`
     * to `24` suit most scenes. Defaults to `18.0`.
     */
    #[Min(0.001)]
    public float $shadowProjectionSize {
        get {
            return (float) ($this->getState()['shadowProjectionSize'] ?? 18.0);
        }

        set(float $value) {
            NativeEngine::call('directional_light_set_shadow_projection_size', $this->componentId, $value);
        }
    }

    /**
     * How far from the active camera shadows are drawn, in world units.
     *
     * Values around `20` to `36` suit most scenes. Defaults to `28.0`.
     */
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
     * The color of the light.
     *
     * Defaults to white.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('directional_light_set_color', $this->componentId, $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Returns the light color as red, green, blue and alpha values from 0 to 255.
     *
     * Use {@see DirectionalLight::$color} to read it as a {@see Color}.
     *
     * @return array{r: int, g: int, b: int, a: int} The channels keyed `r`, `g`, `b` and `a`.
     */
    public function getColor(): array
    {
        return $this->color->toRGBA();
    }

    /**
     * Sets the light color from a {@see Color}, an array of channels, or separate
     * channel values from 0 to 255.
     *
     * ```php
     * $light->setColor(Color::white());
     * $light->setColor(['r' => 255, 'g' => 240, 'b' => 220]);
     * $light->setColor(255, 240, 220);
     * ```
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     *     A Color, an array keyed `r`, `g`, `b`, `a` or `0` to `3`, or the red
     *     channel from 0 to 255.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is a number.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is a number.
     * @param int $alpha The alpha channel from 0 to 255. Used only when `$red` is a number.
     * @throws \InvalidArgumentException When `$red` is a number and `$green` or `$blue` is missing.
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
