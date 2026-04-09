<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

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
            \lenga_internal_ui_button_set_text($this->getId(), $value);
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
            \lenga_internal_ui_button_set_font_size($this->getId(), $value);
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
            \lenga_internal_ui_button_set_font_path($this->getId(), $value);
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
            \lenga_internal_ui_button_set_interactable($this->getId(), $value);
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
     * Gets the current text color.
     *
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getTextColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['textColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 255),
            'g' => (int) ($value['g'] ?? 255),
            'b' => (int) ($value['b'] ?? 255),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    /**
     * Sets the text color.
     */
    public function setTextColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_button_set_text_color($this->getId(), $red, $green, $blue, $alpha);
    }

    /**
     * Gets the default (idle) background color.
     *
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getBackgroundColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['backgroundColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 36),
            'g' => (int) ($value['g'] ?? 78),
            'b' => (int) ($value['b'] ?? 130),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    /**
     * Sets the default (idle) background color.
     */
    public function setBackgroundColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_button_set_background_color($this->getId(), $red, $green, $blue, $alpha);
    }

    /**
     * Sets the default (idle) background image.
     *
     * @param string $filename The relative path
     * @return void
     */
    public function setBackgroundImage(string $filename): void
    {
        \lenga_internal_ui_button_set_background_image($this->getId(), $filename);
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
        \lenga_internal_ui_button_set_hover_image($this->getId(), $filename);
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
        \lenga_internal_ui_button_set_pressed_image($this->getId(), $filename);
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
        \lenga_internal_ui_button_set_disabled_image($this->getId(), $filename);
    }

    public function getDisabledImage(): string
    {
        return (string) ($this->getState()['disabledImagePath'] ?? '');
    }

    /**
     * Gets the background color used while hovered.
     *
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getHoverColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['hoverColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 48),
            'g' => (int) ($value['g'] ?? 102),
            'b' => (int) ($value['b'] ?? 168),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    /**
     * Sets the background color used while hovered.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     */
    public function setHoverColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_button_set_hover_color($this->getId(), $red, $green, $blue, $alpha);
    }

    /**
     * Gets the background color used while pressed.
     *
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getPressedColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['pressedColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 24),
            'g' => (int) ($value['g'] ?? 58),
            'b' => (int) ($value['b'] ?? 97),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    /**
     * Sets the background color used while pressed.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     */
    public function setPressedColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_button_set_pressed_color($this->getId(), $red, $green, $blue, $alpha);
    }

    /**
     * Gets the background color used while not interactable.
     *
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getDisabledColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['disabledColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 56),
            'g' => (int) ($value['g'] ?? 66),
            'b' => (int) ($value['b'] ?? 82),
            'a' => (int) ($value['a'] ?? 180),
        ];
    }

    /**
     * Sets the background color used while not interactable.
     */
    public function setDisabledColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_button_set_disabled_color($this->getId(), $red, $green, $blue, $alpha);
    }
}
