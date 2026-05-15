<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Internal\ColorBridge;

/**
 * UI slider element with value range, interaction state, and visual part styling.
 */
final class Slider extends UIElement
{
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_slider_set_interactable', $this->getId(), $value);
        }
    }

    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    public bool $pressed {
        get {
            return (bool) ($this->getState()['pressed'] ?? false);
        }
    }

    public float $minValue {
        get {
            return (float) ($this->getState()['minValue'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_min_value', $this->getId(), $value);
        }
    }

    public float $maxValue {
        get {
            return (float) ($this->getState()['maxValue'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_max_value', $this->getId(), $value);
        }
    }

    public float $value {
        get {
            return (float) ($this->getState()['value'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_slider_set_value', $this->getId(), $value);
        }
    }

    public bool $wholeNumbers {
        get {
            return (bool) ($this->getState()['wholeNumbers'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('ui_slider_set_whole_numbers', $this->getId(), $value);
        }
    }

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
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setBackgroundColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_background_image', $this->getId(), $value);
        }
    }

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
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setFillColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_fill_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    public string $fillImage {
        get {
            return (string) ($this->getState()['fillImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_fill_image', $this->getId(), $value);
        }
    }

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
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setHandleColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_slider_set_handle_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    public string $handleImage {
        get {
            return (string) ($this->getState()['handleImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_slider_set_handle_image', $this->getId(), $value);
        }
    }

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
}
