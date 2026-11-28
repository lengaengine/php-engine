<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Internal\ColorBridge;

/**
 * A UI element that shows a value within a range as a filled track with an optional handle.
 *
 * Use it for health bars, charge meters, loading indicators and volume
 * controls. It has three visual parts: the background track, the fill that
 * grows with {@see Slider::$value}, and the handle. Each part has a color, an
 * optional image and a size. Animate the value smoothly with
 * {@see \Lenga\Engine\Tweening\Tween::uiValueTo()}.
 *
 * ```php
 * $this->healthBar->maxValue = 100.0;
 * $this->healthBar->value = $this->health;
 * ```
 */
final class Slider extends UIElement
{
    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = 'Slider';

    /**
     * Whether the player can change the slider with the pointer, keyboard or gamepad.
     *
     * A slider that is not interactable draws its track in {@see Slider::$disabledColor}, its fill in
     * {@see Slider::$disabledFillColor} and its handle in {@see Slider::$disabledHandleColor}. Defaults to `true`.
     */
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_slider_set_interactable', $this->getId(), $value);
        }
    }

    /** Whether the pointer is over the slider. Defaults to `false`. */
    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    /** Whether the slider is the selected element of its canvas and has keyboard and gamepad focus. Defaults to `false`. */
    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    /** Whether the slider is being pressed. Defaults to `false`. */
    public bool $pressed {
        get {
            return (bool) ($this->getState()['pressed'] ?? false);
        }
    }

    /**
     * The value at the empty end of the track.
     *
     * Setting it above {@see Slider::$maxValue} raises the maximum to match, and
     * the current value is clamped into the new range. Defaults to `0.0`.
     */
    public float $minValue {
        get {
            return (float) ($this->getState()['minValue'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_min_value', $this->getId(), $value);
        }
    }

    /**
     * The value at the full end of the track.
     *
     * Setting it below {@see Slider::$minValue} lowers the minimum to match, and
     * the current value is clamped into the new range. Defaults to `1.0`.
     */
    public float $maxValue {
        get {
            return (float) ($this->getState()['maxValue'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_max_value', $this->getId(), $value);
        }
    }

    /**
     * The slider's current value.
     *
     * Values outside {@see Slider::$minValue} to {@see Slider::$maxValue} are
     * clamped, and rounded when {@see Slider::$wholeNumbers} is true. Defaults to `0.0`.
     */
    public float $value {
        get {
            return (float) ($this->getState()['value'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_value', $this->getId(), $value);
        }
    }

    /**
     * Whether the value snaps to whole numbers.
     *
     * Turning it on rounds the current value. Defaults to `false`.
     */
    public bool $wholeNumbers {
        get {
            return (bool) ($this->getState()['wholeNumbers'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('ui_slider_set_whole_numbers', $this->getId(), $value);
        }
    }

    /**
     * Whether the handle is drawn. Turn it off for display-only bars such as health or progress.
     *
     * Defaults to `true`.
     */
    public bool $showHandle {
        get {
            return (bool) ($this->getState()['showHandle'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_slider_set_show_handle', $this->getId(), $value);
        }
    }

    /**
     * The slider track background color.
     *
     * Defaults to a dark slate blue, `(56, 66, 82)`.
     */
    public Color $backgroundColor {
        get => ColorBridge::fromState($this->getState()['backgroundColor'] ?? null, Color::fromRGBA(56, 66, 82));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the slider track background color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getBackgroundColor(): array
    {
        return $this->backgroundColor->toRGBA();
    }

    /**
     * Sets the slider track background color.
     *
     * Same as assigning {@see Slider::$backgroundColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setBackgroundColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Project-relative path to an image for the background track. An empty string draws the track in {@see Slider::$backgroundColor}.
     *
     * Defaults to an empty string.
     */
    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_background_image', $this->getId(), $value);
        }
    }

    /**
     * Size of the background track in pixels, centered in the element.
     *
     * A component of 0 uses the automatic size on that axis: the element's width, or a height based on the element's height. Defaults to `(0, 0)`.
     */
    public Vector2 $backgroundSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['backgroundSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            NativeEngine::call('ui_slider_set_background_size', $this->getId(), $value->x, $value->y);
        }
    }

    /**
     * The slider filled-region color.
     *
     * Defaults to a teal green, `(34, 197, 155)`.
     */
    public Color $fillColor {
        get => ColorBridge::fromState($this->getState()['fillColor'] ?? null, Color::fromRGBA(34, 197, 155));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_fill_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the slider filled-region color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getFillColor(): array
    {
        return $this->fillColor->toRGBA();
    }

    /**
     * Sets the slider filled-region color.
     *
     * Same as assigning {@see Slider::$fillColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setFillColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_fill_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Project-relative path to an image for the filled region. An empty string draws the fill in {@see Slider::$fillColor}.
     *
     * Defaults to an empty string.
     */
    public string $fillImage {
        get {
            return (string) ($this->getState()['fillImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_fill_image', $this->getId(), $value);
        }
    }

    /**
     * Size of the fill at full value, in pixels.
     *
     * The fill is never larger than the track. A component of 0 uses the track's size on that axis. Defaults to `(0, 0)`.
     */
    public Vector2 $fillSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['fillSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            NativeEngine::call('ui_slider_set_fill_size', $this->getId(), $value->x, $value->y);
        }
    }

    /**
     * The slider handle color.
     *
     * Defaults to white.
     */
    public Color $handleColor {
        get => ColorBridge::fromState($this->getState()['handleColor'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_handle_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the slider handle color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getHandleColor(): array
    {
        return $this->handleColor->toRGBA();
    }

    /**
     * Sets the slider handle color.
     *
     * Same as assigning {@see Slider::$handleColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setHandleColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_handle_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Project-relative path to an image for the handle. An empty string draws the handle in {@see Slider::$handleColor}.
     *
     * Defaults to an empty string.
     */
    public string $handleImage {
        get {
            return (string) ($this->getState()['handleImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_handle_image', $this->getId(), $value);
        }
    }

    /**
     * Size of the handle in pixels.
     *
     * A component of 0 uses the automatic size on that axis, based on the element and track height. Defaults to `(0, 0)`.
     */
    public Vector2 $handleSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['handleSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            NativeEngine::call('ui_slider_set_handle_size', $this->getId(), $value->x, $value->y);
        }
    }

    /**
     * The track color while the slider is not interactable.
     *
     * Defaults to a translucent slate, `Color::fromRGBA(56, 66, 82, 180)`.
     */
    public Color $disabledColor {
        get => ColorBridge::fromState($this->getState()['disabledColor'] ?? null, Color::fromRGBA(56, 66, 82, 180));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_disabled_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * The fill color while the slider is not interactable. It tints {@see Slider::$fillImage} too.
     *
     * Defaults to a mid grey, `Color::fromRGBA(118, 118, 118)`, which stays legible on light and dark backgrounds.
     */
    public Color $disabledFillColor {
        get => ColorBridge::fromState($this->getState()['disabledFillColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_disabled_fill_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * The handle color while the slider is not interactable. It tints {@see Slider::$handleImage} too.
     *
     * Defaults to the same mid grey as {@see Slider::$disabledFillColor}, `Color::fromRGBA(118, 118, 118)`.
     */
    public Color $disabledHandleColor {
        get => ColorBridge::fromState($this->getState()['disabledHandleColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_slider_set_disabled_handle_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }
}
