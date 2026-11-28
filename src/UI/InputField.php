<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Enumerations\InputFieldContentType;
use Lenga\Engine\Internal\ColorBridge;
use function max;

/**
 * A single-line text box the player can type in.
 *
 * Moving focus onto the field highlights it. Clicking it, pressing Submit
 * (Enter or the gamepad's South button) or typing starts editing. While the
 * field is being edited it takes the keyboard: Enter submits, Escape cancels,
 * and Up or Down moves focus on.
 *
 * Read what the player did this frame with `valueChangedThisFrame`,
 * `submittedThisFrame`, `cancelledThisFrame` and `editEndedThisFrame`.
 */
final class InputField extends UIElement
{
    /**
     * The text in the field. Setting it leaves out characters the content
     * type refuses and cuts it to the character limit.
     */
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_text', $this->getId(), $value);
        }
    }

    /**
     * The hint shown while the field is empty.
     */
    public string $placeholder {
        get {
            return (string) ($this->getState()['placeholder'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_placeholder', $this->getId(), $value);
        }
    }

    /**
     * The most characters the field holds. 0 means no limit. Lowering it cuts
     * longer text.
     */
    public int $characterLimit {
        get {
            return (int) ($this->getState()['characterLimit'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('ui_input_field_set_character_limit', $this->getId(), max(0, $value));
        }
    }

    /**
     * Which characters the field accepts. Changing it removes characters the
     * new type refuses.
     */
    public InputFieldContentType $contentType {
        get {
            return InputFieldContentType::tryFrom((string) ($this->getState()['contentType'] ?? ''))
                ?? InputFieldContentType::Standard;
        }

        set(InputFieldContentType $value) {
            NativeEngine::call('ui_input_field_set_content_type', $this->getId(), $value->value);
        }
    }

    /**
     * The caret's position, in characters from the start of the text.
     */
    public int $caretPosition {
        get {
            return (int) ($this->getState()['caretPosition'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('ui_input_field_set_caret_position', $this->getId(), max(0, $value));
        }
    }

    /**
     * Whether the player is typing in the field.
     */
    public bool $editing {
        get {
            return (bool) ($this->getState()['editing'] ?? false);
        }
    }

    /**
     * Whether Cancel (Escape) puts back the text the field had when editing
     * began.
     */
    public bool $restoreOnCancel {
        get {
            return (bool) ($this->getState()['restoreOnCancel'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_input_field_set_restore_on_cancel', $this->getId(), $value);
        }
    }

    /**
     * The player changed the text this frame.
     */
    public bool $valueChangedThisFrame {
        get {
            return (bool) ($this->getState()['valueChangedThisFrame'] ?? false);
        }
    }

    /**
     * The player pressed Submit (Enter) this frame.
     */
    public bool $submittedThisFrame {
        get {
            return (bool) ($this->getState()['submittedThisFrame'] ?? false);
        }
    }

    /**
     * The player pressed Cancel (Escape) this frame.
     */
    public bool $cancelledThisFrame {
        get {
            return (bool) ($this->getState()['cancelledThisFrame'] ?? false);
        }
    }

    /**
     * Editing ended this frame, for any reason: Submit, Cancel, or focus
     * moving elsewhere.
     */
    public bool $editEndedThisFrame {
        get {
            return (bool) ($this->getState()['editEndedThisFrame'] ?? false);
        }
    }

    /**
     * Whether the field can be focused and edited.
     */
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_input_field_set_interactable', $this->getId(), $value);
        }
    }

    /**
     * Whether the pointer is over the field.
     */
    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    /**
     * Whether the field has keyboard or gamepad focus.
     */
    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
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
            NativeEngine::call('ui_input_field_set_font_size', $this->getId(), $value);
        }
    }

    /**
     * Font asset path for the text and placeholder. Empty uses the default UI
     * font.
     */
    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_font_path', $this->getId(), $value);
        }
    }

    public Color $textColor {
        get => ColorBridge::fromState($this->getState()['textColor'] ?? null, Color::white());

        set(Color $value) {
            $this->setColor('ui_input_field_set_text_color', $value);
        }
    }

    public Color $placeholderColor {
        get => ColorBridge::fromState($this->getState()['placeholderColor'] ?? null, Color::fromRGBA(148, 160, 178));

        set(Color $value) {
            $this->setColor('ui_input_field_set_placeholder_color', $value);
        }
    }

    public Color $caretColor {
        get => ColorBridge::fromState($this->getState()['caretColor'] ?? null, Color::white());

        set(Color $value) {
            $this->setColor('ui_input_field_set_caret_color', $value);
        }
    }

    /**
     * Caret width in UI pixels.
     */
    public float $caretWidth {
        get {
            return (float) ($this->getState()['caretWidth'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('ui_input_field_set_caret_width', $this->getId(), max(1.0, $value));
        }
    }

    /**
     * Caret blinks per second. 0 keeps the caret steady.
     */
    public float $caretBlinkRate {
        get {
            return (float) ($this->getState()['caretBlinkRate'] ?? 0.85);
        }

        set(float $value) {
            NativeEngine::call('ui_input_field_set_caret_blink_rate', $this->getId(), max(0.0, $value));
        }
    }

    /**
     * Background color while the field is idle.
     */
    public Color $backgroundColor {
        get => ColorBridge::fromState($this->getState()['backgroundColor'] ?? null, Color::fromRGBA(24, 30, 42));

        set(Color $value) {
            $this->setColor('ui_input_field_set_background_color', $value);
        }
    }

    /**
     * Background color while the field is hovered or focused.
     */
    public Color $hoverColor {
        get => ColorBridge::fromState($this->getState()['hoverColor'] ?? null, Color::fromRGBA(34, 42, 58));

        set(Color $value) {
            $this->setColor('ui_input_field_set_hover_color', $value);
        }
    }

    /**
     * Background color while the field is being edited.
     */
    public Color $editingColor {
        get => ColorBridge::fromState($this->getState()['editingColor'] ?? null, Color::fromRGBA(34, 42, 58));

        set(Color $value) {
            $this->setColor('ui_input_field_set_editing_color', $value);
        }
    }

    /**
     * Background color while the field is not interactable.
     */
    public Color $disabledColor {
        get => ColorBridge::fromState($this->getState()['disabledColor'] ?? null, Color::fromRGBA(56, 66, 82, 180));

        set(Color $value) {
            $this->setColor('ui_input_field_set_disabled_color', $value);
        }
    }

    /**
     * Background image path. Empty draws the background color.
     */
    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_background_image', $this->getId(), $value);
        }
    }

    public Color $outlineColor {
        get => ColorBridge::fromState($this->getState()['outlineColor'] ?? null, Color::fromRGBA(90, 104, 128));

        set(Color $value) {
            $this->setColor('ui_input_field_set_outline_color', $value);
        }
    }

    /**
     * Outline width in UI pixels. 0 hides the outline.
     */
    public float $outlineWidth {
        get {
            return (float) ($this->getState()['outlineWidth'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('ui_input_field_set_outline_width', $this->getId(), max(0.0, $value));
        }
    }

    /**
     * Starts editing, with the caret at the end of the text. Does nothing if
     * the field is not interactable.
     */
    public function beginEditing(): void
    {
        NativeEngine::call('ui_input_field_set_editing', $this->getId(), true);
    }

    /**
     * Stops editing and keeps the text. `editEndedThisFrame` reports it.
     */
    public function endEditing(): void
    {
        NativeEngine::call('ui_input_field_set_editing', $this->getId(), false);
    }

    private function setColor(string $binding, Color $value): void
    {
        $color = $value->toRGBA();
        NativeEngine::call($binding, $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }
}
