<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Internal\ColorBridge;
use function is_array;

/**
 * Draws a ribbon behind a moving GameObject, for balls, dashes, projectiles and weapon arcs.
 *
 * After each frame's `lateUpdate()` step, the trail samples the GameObject's world
 * position and adds a point once the object has moved at least
 * {@see TrailRenderer::$minVertexDistance}. Points stay for {@see TrailRenderer::$time}
 * seconds of gameplay time. The newest end uses {@see TrailRenderer::$startWidth} and
 * {@see TrailRenderer::$startColor} and blends toward {@see TrailRenderer::$endWidth} and
 * {@see TrailRenderer::$endColor} at the oldest end. In 2D scenes the trail draws in its
 * {@see TrailRenderer::$sortingLayer}. In 3D scenes {@see TrailRenderer::$alignment}
 * decides which way the ribbon faces.
 *
 * ```php
 * public function beginDash(): void
 * {
 *     $this->trail?->clear();
 *     if ($this->trail !== null) {
 *         $this->trail->emitting = true;
 *     }
 * }
 *
 * public function endDash(): void
 * {
 *     if ($this->trail !== null) {
 *         $this->trail->emitting = false;
 *     }
 * }
 * ```
 *
 * Call {@see TrailRenderer::clear()} after a teleport or respawn so the trail does not
 * stretch across the screen.
 */
final class TrailRenderer extends Renderer
{
    /**
     * {@see TrailRenderer::$alignment} value that turns the ribbon to face the camera.
     */
    public const string ALIGNMENT_VIEW = 'View';
    /**
     * {@see TrailRenderer::$alignment} value that lays the ribbon flat against the GameObject's forward (Z) axis.
     */
    public const string ALIGNMENT_TRANSFORM_Z = 'TransformZ';

    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'TrailRenderer');
    }

    /**
     * How long each trail point stays visible, in seconds of gameplay time.
     *
     * Values below `0.01` are stored as `0.01`. Defaults to `5.0`.
     */
    public float $time {
        get {
            return (float) ($this->getState()['time'] ?? 5.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_time', $this->componentId, $value);
        }
    }

    /**
     * Distance in world units the GameObject must move before the trail adds a new point.
     *
     * Smaller values give smoother curves and use more points. Negative values are stored
     * as `0`. Defaults to `0.1`.
     */
    public float $minVertexDistance {
        get {
            return (float) ($this->getState()['minVertexDistance'] ?? 0.1);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_min_vertex_distance', $this->componentId, $value);
        }
    }

    /**
     * Whether the trail adds new points as the GameObject moves.
     *
     * Set it to `false` to stop the trail growing. Existing points keep fading out over
     * {@see TrailRenderer::$time}. Defaults to `true`.
     */
    public bool $emitting {
        get {
            return (bool) ($this->getState()['emitting'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('trail_renderer_set_emitting', $this->componentId, $value);
        }
    }

    /**
     * Whether the trail destroys its GameObject when it is done.
     *
     * While emitting, the GameObject is destroyed once it has stayed still for
     * {@see TrailRenderer::$time} seconds. While not emitting, it is destroyed once the
     * last point has faded. Defaults to `false`.
     */
    public bool $autodestruct {
        get {
            return (bool) ($this->getState()['autodestruct'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('trail_renderer_set_autodestruct', $this->componentId, $value);
        }
    }

    /**
     * Ribbon width at the newest point, next to the GameObject, in world units.
     *
     * Multiplied by {@see TrailRenderer::$widthMultiplier}. Negative values are stored as
     * `0`. Defaults to `0.5`.
     */
    public float $startWidth {
        get {
            return (float) ($this->getState()['startWidth'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_start_width', $this->componentId, $value);
        }
    }

    /**
     * Ribbon width at the oldest point, in world units.
     *
     * Multiplied by {@see TrailRenderer::$widthMultiplier}. Negative values are stored as
     * `0`. Defaults to `0.0`.
     */
    public float $endWidth {
        get {
            return (float) ($this->getState()['endWidth'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_end_width', $this->componentId, $value);
        }
    }

    /**
     * Scale applied to both {@see TrailRenderer::$startWidth} and {@see TrailRenderer::$endWidth}.
     *
     * Use it to grow or shrink the whole trail at once. Negative values are stored as `0`.
     * Defaults to `1.0`.
     */
    public float $widthMultiplier {
        get {
            return (float) ($this->getState()['widthMultiplier'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_width_multiplier', $this->componentId, $value);
        }
    }

    /**
     * Which way the ribbon faces in 3D scenes.
     *
     * Use {@see TrailRenderer::ALIGNMENT_VIEW} for a ribbon that always faces the camera,
     * or {@see TrailRenderer::ALIGNMENT_TRANSFORM_Z} for a ribbon that follows the
     * GameObject's orientation, such as a sword swing. Any other value selects
     * {@see TrailRenderer::ALIGNMENT_VIEW}. Defaults to `View`.
     */
    public string $alignment {
        get {
            return (string) ($this->getState()['alignment'] ?? self::ALIGNMENT_VIEW);
        }

        set(string $value) {
            NativeEngine::call('trail_renderer_set_alignment', $this->componentId, $value);
        }
    }

    /**
     * Name of the sorting layer the trail draws in, in 2D scenes.
     *
     * Assigning an empty string selects `Default`. Defaults to `Default`.
     */
    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('trail_renderer_set_sorting_layer', $this->componentId, $value);
        }
    }

    /**
     * Draw order within the sorting layer, in 2D scenes.
     *
     * Higher values draw on top of lower values in the same
     * {@see TrailRenderer::$sortingLayer}. Defaults to `0`.
     */
    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('trail_renderer_set_order_in_layer', $this->componentId, $value);
        }
    }

    /**
     * Largest number of points the trail keeps.
     *
     * When the trail reaches this limit, each new point drops the oldest one. Increase it
     * if a long, detailed trail ends sooner than expected. Values are clamped to the
     * range `2` to `8192`. Defaults to `512`.
     */
    public int $maxPoints {
        get {
            return (int) ($this->getState()['maxPoints'] ?? 512);
        }

        set(int $value) {
            NativeEngine::call('trail_renderer_set_max_points', $this->componentId, $value);
        }
    }

    /**
     * Number of points the trail holds now. Read-only. Defaults to `0`.
     */
    public int $positionCount {
        get {
            return (int) ($this->getState()['positionCount'] ?? 0);
        }
    }

    /**
     * Removes every point from the trail at once.
     *
     * Call it after teleports, respawns and scene resets.
     */
    public function clear(): void
    {
        NativeEngine::call('trail_renderer_clear', $this->componentId);
    }

    /**
     * Adds a point to the newest end of the trail.
     *
     * Use it to draw a trail along a path you calculate yourself. The point is stamped
     * with the current gameplay time and fades like any other point. If the trail is at
     * {@see TrailRenderer::$maxPoints}, the oldest point is dropped.
     *
     * @param Vector3 $position Point in world space.
     */
    public function addPosition(Vector3 $position): void
    {
        NativeEngine::call(
            'trail_renderer_add_position',
            $this->componentId,
            $position->x,
            $position->y,
            $position->z,
        );
    }

    /**
     * Returns one of the trail's points in world space.
     *
     * @param int $index Point index, from `0` for the oldest point to
     *     {@see TrailRenderer::$positionCount} minus one for the newest.
     * @return Vector3|null The point, or `null` if the index is out of range.
     */
    public function getPosition(int $index): ?Vector3
    {
        $position = NativeEngine::call('trail_renderer_get_position', $this->componentId, $index);
        if (!is_array($position)) {
            return null;
        }

        return new Vector3(
            (float) ($position['x'] ?? 0.0),
            (float) ($position['y'] ?? 0.0),
            (float) ($position['z'] ?? 0.0),
        );
    }

    /**
     * The color at the head of the trail.
     *
     * Reading it returns a new Color, so assign a whole Color to change it. Defaults to
     * white.
     */
    public Color $startColor {
        get => $this->readColor('startColor');

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call(
                'trail_renderer_set_start_color',
                $this->componentId,
                $color['r'],
                $color['g'],
                $color['b'],
                $color['a'],
            );
        }
    }

    /**
     * Gets the color at the head of the trail as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getStartColor(): array
    {
        return $this->startColor->toRGBA();
    }

    /**
     * Sets the color at the head of the trail.
     *
     * Pass a {@see Color}, an array of channels, or separate 0 to 255 channel values.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     *     A Color, an array with `r`, `g`, `b` and `a` keys or four indexed channels, or
     *     the red channel from 0 to 255.
     * @param int|null $green Green channel from 0 to 255, used when `$red` is an int.
     * @param int|null $blue Blue channel from 0 to 255, used when `$red` is an int.
     * @param int $alpha Alpha channel from 0 (transparent) to 255 (opaque), used when
     *     `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setStartColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call(
            'trail_renderer_set_start_color',
            $this->componentId,
            $color['r'],
            $color['g'],
            $color['b'],
            $color['a'],
        );
    }

    /**
     * The color at the tail of the trail.
     *
     * Reading it returns a new Color, so assign a whole Color to change it. Defaults to
     * white.
     */
    public Color $endColor {
        get => $this->readColor('endColor');

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call(
                'trail_renderer_set_end_color',
                $this->componentId,
                $color['r'],
                $color['g'],
                $color['b'],
                $color['a'],
            );
        }
    }

    /**
     * Gets the color at the tail of the trail as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getEndColor(): array
    {
        return $this->endColor->toRGBA();
    }

    /**
     * Sets the color at the tail of the trail.
     *
     * Pass a {@see Color}, an array of channels, or separate 0 to 255 channel values.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     *     A Color, an array with `r`, `g`, `b` and `a` keys or four indexed channels, or
     *     the red channel from 0 to 255.
     * @param int|null $green Green channel from 0 to 255, used when `$red` is an int.
     * @param int|null $blue Blue channel from 0 to 255, used when `$red` is an int.
     * @param int $alpha Alpha channel from 0 (transparent) to 255 (opaque), used when
     *     `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setEndColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call(
            'trail_renderer_set_end_color',
            $this->componentId,
            $color['r'],
            $color['g'],
            $color['b'],
            $color['a'],
        );
    }

    /**
     * Returns all trail settings as the raw array the engine reports.
     *
     * @internal Engine state snapshot. Read the typed properties of this class instead.
     *
     * @return array{
     *     time?: float,
     *     minVertexDistance?: float,
     *     emitting?: bool,
     *     autodestruct?: bool,
     *     startWidth?: float,
     *     endWidth?: float,
     *     widthMultiplier?: float,
     *     alignment?: string,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     positionCount?: int,
     *     maxPoints?: int,
     *     enabled?: bool,
     *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     endColor?: array{r?: int, g?: int, b?: int, a?: int}
     * }
     */
    public function getState(): array
    {
        /** @var array{
         *     time?: float,
         *     minVertexDistance?: float,
         *     emitting?: bool,
         *     autodestruct?: bool,
         *     startWidth?: float,
         *     endWidth?: float,
         *     widthMultiplier?: float,
         *     alignment?: string,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     positionCount?: int,
         *     maxPoints?: int,
         *     enabled?: bool,
         *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     endColor?: array{r?: int, g?: int, b?: int, a?: int}
         * } $state
         */
        $state = NativeEngine::call('trail_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }

    private function readColor(string $key): Color
    {
        return ColorBridge::fromState($this->getState()[$key] ?? null, Color::white());
    }
}
