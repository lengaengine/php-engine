<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Internal\ColorBridge;
use function max;

/**
 * UI button element with configurable label, colors, interaction state, and typography.
 */
final class Button extends UIElement
{
    /**
     * Visible button label.
     */
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_button_set_text', $this->getId(), $value);
        }
    }

    /**
     * Text size in pixels.
     */
    public float $fontSize {
        get {
            return (float) ($this->getState()['fontSize'] ?? 24.0);
        }

        set(float $value) {
            NativeEngine::call('ui_button_set_font_size', $this->getId(), $value);
        }
    }

    /**
     * Font asset path used for rendering button text.
     */
    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_button_set_font_path', $this->getId(), $value);
        }
    }

    /**
     * Whether the button can receive pointer input.
     */
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_button_set_interactable', $this->getId(), $value);
        }
    }

    /**
     * Whether the pointer is currently over the button.
     */
    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    /**
     * Whether the button currently has keyboard/gamepad focus.
     */
    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    /**
     * Whether the button is currently pressed.
     */
    public bool $pressed {
        get {
            return (bool) ($this->getState()['pressed'] ?? false);
        }
    }

    /**
     * Whether the button was pressed during the current frame.
     */
    public bool $pressedThisFrame {
        get {
            return (bool) ($this->getState()['pressedThisFrame'] ?? false);
        }
    }

    /**
     * Whether the button was released during the current frame.
     */
    public bool $releasedThisFrame {
        get {
            return (bool) ($this->getState()['releasedThisFrame'] ?? false);
        }
    }

    /**
     * Whether a full click interaction occurred during the current frame.
     */
    public bool $clicked {
        get {
            return (bool) ($this->getState()['clicked'] ?? false);
        }
    }

    /**
     * The button label color.
     */
    public Color $textColor {
        get => ColorBridge::fromState($this->getState()['textColor'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_text_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the current text color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getTextColor(): array
    {
        return $this->textColor->toRGBA();
    }

    /**
     * Sets the text color.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setTextColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_text_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The default background color.
     */
    public Color $backgroundColor {
        get => ColorBridge::fromState($this->getState()['backgroundColor'] ?? null, Color::fromRGBA(36, 78, 130));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the default (idle) background color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getBackgroundColor(): array
    {
        return $this->backgroundColor->toRGBA();
    }

    /**
     * Sets the default (idle) background color.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setBackgroundColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The button outline color.
     */
    public Color $outlineColor {
        get => ColorBridge::fromState($this->getState()['outlineColor'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_outline_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the button outline color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getOutlineColor(): array
    {
        return $this->outlineColor->toRGBA();
    }

    /**
     * Sets the button outline color.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setOutlineColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_outline_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Button outline width in UI pixels. Use 0 to hide the outline.
     */
    public float $outlineWidth {
        get {
            return (float) ($this->getState()['outlineWidth'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_button_set_outline_width', $this->getId(), max(0.0, $value));
        }
    }

    /**
     * Sets the default (idle) background image.
     *
     * @param string $filename The relative path
     * @return void
     */
    public function setBackgroundImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_background_image', $this->getId(), $filename);
    }

    /**
     * Gets the default (idle) background image path.
     */
    public function getBackgroundImage(): string
    {
        return (string) ($this->getState()['imagePath'] ?? '');
    }

    /**
     * Sets the background image used while hovered.
     */
    public function setHoverImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_hover_image', $this->getId(), $filename);
    }

    public function getHoverImage(): string
    {
        return (string) ($this->getState()['hoverImagePath'] ?? '');
    }

    /**
     * Sets the background image used while pressed.
     */
    public function setPressedImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_pressed_image', $this->getId(), $filename);
    }

    public function getPressedImage(): string
    {
        return (string) ($this->getState()['pressedImagePath'] ?? '');
    }

    /**
     * Sets the background image used while not interactable.
     */
    public function setDisabledImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_disabled_image', $this->getId(), $filename);
    }

    public function getDisabledImage(): string
    {
        return (string) ($this->getState()['disabledImagePath'] ?? '');
    }

    /**
     * The background color used while hovered.
     */
    public Color $hoverColor {
        get => ColorBridge::fromState($this->getState()['hoverColor'] ?? null, Color::fromRGBA(48, 102, 168));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_hover_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the background color used while hovered as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getHoverColor(): array
    {
        return $this->hoverColor->toRGBA();
    }

    /**
     * Sets the background color used while hovered.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setHoverColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_hover_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The background color used while pressed.
     */
    public Color $pressedColor {
        get => ColorBridge::fromState($this->getState()['pressedColor'] ?? null, Color::fromRGBA(24, 58, 97));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_pressed_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the background color used while pressed as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getPressedColor(): array
    {
        return $this->pressedColor->toRGBA();
    }

    /**
     * Sets the background color used while pressed.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setPressedColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_pressed_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The background color used while not interactable.
     */
    public Color $disabledColor {
        get => ColorBridge::fromState($this->getState()['disabledColor'] ?? null, Color::fromRGBA(56, 66, 82, 180));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_disabled_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the background color used while not interactable as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getDisabledColor(): array
    {
        return $this->disabledColor->toRGBA();
    }

    /**
     * Sets the background color used while not interactable.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setDisabledColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_disabled_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }
}
