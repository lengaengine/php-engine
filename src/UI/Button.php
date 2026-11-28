<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Internal\ColorBridge;
use function max;

/**
 * A clickable UI element with a text label, state colors and optional state images.
 *
 * Check {@see Button::$clicked} in `update()` to react to a click. It is true
 * for one frame after a full press and release, like a click event, which
 * makes it the right choice for menu actions:
 *
 * ```php
 * public function update(): void
 * {
 *     if ($this->playButton?->clicked) {
 *         SceneManager::loadScene('Level1');
 *     }
 * }
 * ```
 *
 * The button draws its background in the color for its current state: normal,
 * hovered or selected, pressed, or not interactable. When you assign images,
 * a state with its own image shows that image. A state without an image shows
 * the normal image with the state color laid over it, and with no images at
 * all the button draws a rounded rectangle in the state color.
 */
final class Button extends UIElement
{
    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = 'Button';

    /**
     * Visible button label. An empty string draws no label.
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
     * Font size of the label in canvas pixels. The canvas scales it with the screen.
     *
     * Defaults to `24.0`.
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
     * Project-relative path to the font used for the label. An empty string uses Lenga's default UI font.
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
     *
     * A button that is not interactable shows {@see Button::$disabledColor} and its disabled image, and draws its text in
     * {@see Button::$disabledTextColor} and its outline in {@see Button::$disabledOutlineColor}. Defaults to `true`.
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
     *
     * Defaults to `false`.
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
     *
     * It stays true for every frame the button is held, so use it for hold
     * actions such as charging. Use {@see Button::$clicked} for one-shot actions.
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
     *
     * Prefer it for menu actions and other one-shot triggers.
     */
    public bool $clicked {
        get {
            return (bool) ($this->getState()['clicked'] ?? false);
        }
    }

    /**
     * The button label color.
     *
     * Defaults to white.
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
     * Same as assigning {@see Button::$textColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setTextColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_text_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The default background color.
     *
     * Defaults to a dark blue, `(36, 78, 130)`.
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
     * Same as assigning {@see Button::$backgroundColor}, with byte channels or an array also accepted.
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
        NativeEngine::call('ui_button_set_background_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The button outline color. The outline is drawn only when {@see Button::$outlineWidth} is above 0.
     *
     * Defaults to white.
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
     * Same as assigning {@see Button::$outlineColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setOutlineColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_outline_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The text color while the button is not interactable.
     *
     * Use it with {@see Button::$disabledColor} so a button that cannot be pressed also looks it. Defaults to a mid grey,
     * `Color::fromRGBA(118, 118, 118)`, which stays legible on light and dark backgrounds.
     */
    public Color $disabledTextColor {
        get => ColorBridge::fromState($this->getState()['disabledTextColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_disabled_text_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * The outline color while the button is not interactable. The outline is drawn only when
     * {@see Button::$outlineWidth} is above 0.
     *
     * Defaults to the same mid grey as {@see Button::$disabledTextColor}, `Color::fromRGBA(118, 118, 118)`.
     */
    public Color $disabledOutlineColor {
        get => ColorBridge::fromState($this->getState()['disabledOutlineColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_button_set_disabled_outline_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Button outline width in UI pixels. Use 0 to hide the outline.
     *
     * Negative values are treated as 0. Defaults to `0.0`.
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
     * The other states use this image too when they have no image of their own.
     *
     * @param string $filename Project-relative image path, such as `Assets/Sprites/UI/ButtonIdle.png`. An empty string removes the image.
     */
    public function setBackgroundImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_background_image', $this->getId(), $filename);
    }

    /**
     * Gets the default (idle) background image path, or an empty string when none is set.
     */
    public function getBackgroundImage(): string
    {
        return (string) ($this->getState()['imagePath'] ?? '');
    }

    /**
     * Sets the background image used while hovered or selected.
     *
     * @param string $filename Project-relative image path. An empty string falls back to the default image with {@see Button::$hoverColor} laid over it.
     */
    public function setHoverImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_hover_image', $this->getId(), $filename);
    }

    /**
     * Gets the hover image path, or an empty string when none is set.
     */
    public function getHoverImage(): string
    {
        return (string) ($this->getState()['hoverImagePath'] ?? '');
    }

    /**
     * Sets the background image used while pressed.
     *
     * @param string $filename Project-relative image path. An empty string falls back to the default image with {@see Button::$pressedColor} laid over it.
     */
    public function setPressedImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_pressed_image', $this->getId(), $filename);
    }

    /**
     * Gets the pressed image path, or an empty string when none is set.
     */
    public function getPressedImage(): string
    {
        return (string) ($this->getState()['pressedImagePath'] ?? '');
    }

    /**
     * Sets the background image used while not interactable.
     *
     * @param string $filename Project-relative image path. An empty string falls back to the default image with {@see Button::$disabledColor} laid over it.
     */
    public function setDisabledImage(string $filename): void
    {
        NativeEngine::call('ui_button_set_disabled_image', $this->getId(), $filename);
    }

    /**
     * Gets the disabled image path, or an empty string when none is set.
     */
    public function getDisabledImage(): string
    {
        return (string) ($this->getState()['disabledImagePath'] ?? '');
    }

    /**
     * The background color used while hovered.
     *
     * The button also uses it while it is the selected element of its canvas. Defaults to `(48, 102, 168)`.
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
     * Same as assigning {@see Button::$hoverColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setHoverColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_hover_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The background color used while pressed.
     *
     * Defaults to `(24, 58, 97)`.
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
     * Same as assigning {@see Button::$pressedColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setPressedColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_pressed_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * The background color used while not interactable.
     *
     * Defaults to a translucent slate, `(56, 66, 82, 180)`.
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
     * Same as assigning {@see Button::$disabledColor}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setDisabledColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_button_set_disabled_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }
}
