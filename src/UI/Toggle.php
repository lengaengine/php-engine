<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Internal\ColorBridge;

/**
 * A UI element the player switches on and off: a box that shows a checkmark while it is on, with a text label beside it.
 *
 * Use it for settings such as sound on or off, subtitles or invert-Y. Read
 * {@see Toggle::$isOn} to know its state, and set it to switch the toggle from
 * a script. A toggle that is not interactable keeps its state and draws its
 * box, label and checkmark in their disabled colors.
 *
 * ```php
 * $music = $canvas->findToggleByName('Music');
 * $music->isOn = $settings->musicEnabled;
 * ```
 */
final class Toggle extends UIElement
{
    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = 'Toggle';

    /** The label drawn to the right of the box. Defaults to the element's name. */
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_toggle_set_text', $this->getId(), $value);
        }
    }

    /** The label's font size in UI pixels. Defaults to `24.0`. */
    public float $fontSize {
        get {
            return (float) ($this->getState()['fontSize'] ?? 24.0);
        }

        set(float $value) {
            NativeEngine::call('ui_toggle_set_font_size', $this->getId(), $value);
        }
    }

    /** Project-relative path to the label's font. An empty string uses the default font. Defaults to an empty string. */
    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_toggle_set_font_path', $this->getId(), $value);
        }
    }

    /** Whether the toggle is on, which shows its checkmark. Defaults to `false`. */
    public bool $isOn {
        get {
            return (bool) ($this->getState()['isOn'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('ui_toggle_set_is_on', $this->getId(), $value);
        }
    }

    /**
     * Whether the player can switch the toggle with the pointer, keyboard or gamepad.
     *
     * A toggle that is not interactable shows {@see Toggle::$disabledColor} and its disabled image, and draws its label in
     * {@see Toggle::$disabledTextColor} and its checkmark in {@see Toggle::$disabledCheckmarkColor}. Defaults to `true`.
     */
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_toggle_set_interactable', $this->getId(), $value);
        }
    }

    /** Whether the pointer is over the toggle. Defaults to `false`. */
    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    /** Whether the toggle is the selected element of its canvas and has keyboard and gamepad focus. Defaults to `false`. */
    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    /** Whether the toggle is being pressed. Defaults to `false`. */
    public bool $pressed {
        get {
            return (bool) ($this->getState()['pressed'] ?? false);
        }
    }

    /** The label color. Defaults to white. */
    public Color $textColor {
        get => ColorBridge::fromState($this->getState()['textColor'] ?? null, Color::white());

        set(Color $value) {
            $this->setColor('ui_toggle_set_text_color', $value);
        }
    }

    /** The box color. Defaults to a dark slate blue, `Color::fromRGBA(56, 66, 82)`. */
    public Color $backgroundColor {
        get => ColorBridge::fromState($this->getState()['backgroundColor'] ?? null, Color::fromRGBA(56, 66, 82));

        set(Color $value) {
            $this->setColor('ui_toggle_set_background_color', $value);
        }
    }

    /** Project-relative path to an image for the box. An empty string draws the box in {@see Toggle::$backgroundColor}. Defaults to an empty string. */
    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_toggle_set_background_image', $this->getId(), $value);
        }
    }

    /**
     * Size of the box in UI pixels, at the left of the element and centered vertically.
     *
     * A component of 0 uses the smaller of the element's width and height on that axis. Defaults to `(0, 0)`.
     */
    public Vector2 $backgroundSize {
        get => $this->vector2FromState('backgroundSize');

        set(Vector2 $value) {
            NativeEngine::call('ui_toggle_set_background_size', $this->getId(), $value->x, $value->y);
        }
    }

    /** The checkmark color. It also tints {@see Toggle::$checkmarkImage}. Defaults to a teal green, `Color::fromRGBA(34, 197, 155)`. */
    public Color $checkmarkColor {
        get => ColorBridge::fromState($this->getState()['checkmarkColor'] ?? null, Color::fromRGBA(34, 197, 155));

        set(Color $value) {
            $this->setColor('ui_toggle_set_checkmark_color', $value);
        }
    }

    /** Project-relative path to an image for the checkmark. An empty string draws a filled, rounded square. Defaults to an empty string. */
    public string $checkmarkImage {
        get {
            return (string) ($this->getState()['checkmarkImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_toggle_set_checkmark_image', $this->getId(), $value);
        }
    }

    /**
     * Size of the checkmark in UI pixels, centered in the box.
     *
     * A component of 0 fills the box on that axis, less a small margin. Defaults to `(0, 0)`.
     */
    public Vector2 $checkmarkSize {
        get => $this->vector2FromState('checkmarkSize');

        set(Vector2 $value) {
            NativeEngine::call('ui_toggle_set_checkmark_size', $this->getId(), $value->x, $value->y);
        }
    }

    /** The box color while the toggle is not interactable. Defaults to a translucent slate, `Color::fromRGBA(56, 66, 82, 180)`. */
    public Color $disabledColor {
        get => ColorBridge::fromState($this->getState()['disabledColor'] ?? null, Color::fromRGBA(56, 66, 82, 180));

        set(Color $value) {
            $this->setColor('ui_toggle_set_disabled_color', $value);
        }
    }

    /**
     * Project-relative path to an image for the box while the toggle is not interactable.
     *
     * An empty string uses {@see Toggle::$backgroundImage} with {@see Toggle::$disabledColor} laid over it. Defaults to an empty string.
     */
    public string $disabledImage {
        get {
            return (string) ($this->getState()['disabledImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_toggle_set_disabled_image', $this->getId(), $value);
        }
    }

    /**
     * The label color while the toggle is not interactable.
     *
     * Defaults to a mid grey, `Color::fromRGBA(118, 118, 118)`, which stays legible on light and dark backgrounds.
     */
    public Color $disabledTextColor {
        get => ColorBridge::fromState($this->getState()['disabledTextColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $this->setColor('ui_toggle_set_disabled_text_color', $value);
        }
    }

    /**
     * The checkmark color while the toggle is not interactable. It tints {@see Toggle::$checkmarkImage} too.
     *
     * Defaults to the same mid grey as {@see Toggle::$disabledTextColor}, `Color::fromRGBA(118, 118, 118)`.
     */
    public Color $disabledCheckmarkColor {
        get => ColorBridge::fromState($this->getState()['disabledCheckmarkColor'] ?? null, Color::fromRGBA(118, 118, 118));

        set(Color $value) {
            $this->setColor('ui_toggle_set_disabled_checkmark_color', $value);
        }
    }

    private function setColor(string $binding, Color $value): void
    {
        $color = $value->toRGBA();
        NativeEngine::call($binding, $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    private function vector2FromState(string $key): Vector2
    {
        /** @var array{x?: float, y?: float} $value */
        $value = $this->getState()[$key] ?? [];

        return new Vector2((float) ($value['x'] ?? 0.0), (float) ($value['y'] ?? 0.0));
    }
}
