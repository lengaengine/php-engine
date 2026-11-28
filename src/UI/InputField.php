<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use InvalidArgumentException;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Signal;
use Lenga\Engine\Enumerations\InputFieldContentType;
use Lenga\Engine\Internal\ColorBridge;
use function max;
use function mb_strlen;
use function mb_substr;

/**
 * A UI element that holds a single line of text the player types, such as a player name, a chat line, a search box or a code.
 *
 * The player clicks or taps it, or moves focus onto it with the keyboard or a
 * gamepad and presses Submit, then types. While the field is being edited it
 * takes the keyboard: Enter submits, Escape cancels, and Up or Down moves
 * focus on. {@see InputField::$contentType} limits which characters it accepts
 * and {@see InputField::$characterLimit} how many.
 *
 * Hear what the player does through the {@see InputField::$valueChanged},
 * {@see InputField::$submitted}, {@see InputField::$cancelled} and
 * {@see InputField::$editEnded} Signals, whose listeners receive the field, or
 * read {@see InputField::$submittedThisFrame} and the other flags each frame.
 * Looking the same field up again returns the same object, so listeners stay.
 *
 * ```php
 * $nameField = Scene::getActive()?->findCanvas('Menu')?->findInputFieldByName('Player Name');
 * $nameField?->submitted->add(function (InputField $field): void {
 *     $this->saveName($field->text);
 * });
 * ```
 */
final class InputField extends UIElement
{
    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = 'InputField';

    /**
     * Dispatched when the player changes the text, or Cancel puts the earlier text back. Listeners receive the field.
     */
    public private(set) Signal $valueChanged;

    /**
     * Dispatched when the player presses Submit (Enter, or the gamepad's South button while editing). Listeners receive the field.
     */
    public private(set) Signal $submitted;

    /**
     * Dispatched when the player presses Cancel (Escape, or the gamepad's East button while editing). Listeners receive the field.
     */
    public private(set) Signal $cancelled;

    /**
     * Dispatched when editing ends for any reason: Submit, Cancel, or focus moving elsewhere. It comes after {@see InputField::$submitted} or {@see InputField::$cancelled}. Listeners receive the field.
     */
    public private(set) Signal $editEnded;

    public function __construct(string $nameValue, int $elementId, ?int $canvasId)
    {
        parent::__construct($nameValue, $elementId, $canvasId);
        $this->valueChanged = new Signal();
        $this->submitted = new Signal();
        $this->cancelled = new Signal();
        $this->editEnded = new Signal();
    }

    /**
     * The text in the field.
     *
     * Setting it leaves out characters the {@see InputField::$contentType} refuses and cuts it to {@see InputField::$characterLimit}. Defaults to an empty string.
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
     * The hint shown while the field is empty, such as `Enter your name...`. Defaults to an empty string; a field made in the editor starts with `Enter text...`.
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
     * The most characters the field holds; `0` means no limit. Lowering it cuts longer text. Defaults to `0`.
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
     * Which characters the field accepts and which keyboard phones and tablets show. Changing it removes characters the new type refuses. Defaults to {@see InputFieldContentType::Standard}.
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
     * The character Password and Pin fields show for each character of their text. Defaults to `*`.
     *
     * @throws \InvalidArgumentException When set to anything but a single printable character.
     */
    public string $maskCharacter {
        get {
            return (string) ($this->getState()['maskCharacter'] ?? '*');
        }

        set(string $value) {
            if (mb_strlen($value, 'UTF-8') !== 1 || \preg_match('/^\p{C}$/u', $value) === 1) {
                throw new InvalidArgumentException('The mask character must be one printable character.');
            }
            NativeEngine::call('ui_input_field_set_mask_character', $this->getId(), $value);
        }
    }

    /**
     * The caret's position, in characters from the start of the text. Setting it clears the selection. Defaults to `0`.
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
     * Where the selection starts, in characters from the start of the text.
     *
     * The selection runs from here to {@see InputField::$caretPosition}; it is empty when both are the same. Setting it keeps the caret where it is. Defaults to `0`.
     */
    public int $selectionAnchor {
        get {
            return (int) ($this->getState()['selectionAnchor'] ?? $this->caretPosition);
        }

        set(int $value) {
            NativeEngine::call('ui_input_field_set_selection_anchor', $this->getId(), max(0, $value));
        }
    }

    /**
     * The selected text, or an empty string when nothing is selected. Read-only.
     */
    public string $selectedText {
        get {
            $state = $this->getState();
            $anchor = (int) ($state['selectionAnchor'] ?? 0);
            $caret = (int) ($state['caretPosition'] ?? 0);

            return mb_substr((string) ($state['text'] ?? ''), \min($anchor, $caret), \abs($caret - $anchor), 'UTF-8');
        }
    }

    /**
     * Whether the player is typing in the field. Read-only; use {@see InputField::beginEditing()} and {@see InputField::endEditing()} to change it.
     */
    public bool $editing {
        get {
            return (bool) ($this->getState()['editing'] ?? false);
        }
    }

    /**
     * Whether Cancel (Escape) puts back the text the field had when editing began. Defaults to `true`.
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
     * Whether the player changed the text this frame, or Cancel put the earlier text back. True for one frame.
     */
    public bool $valueChangedThisFrame {
        get {
            return (bool) ($this->getState()['valueChangedThisFrame'] ?? false);
        }
    }

    /**
     * Whether the player pressed Submit this frame. True for one frame.
     */
    public bool $submittedThisFrame {
        get {
            return (bool) ($this->getState()['submittedThisFrame'] ?? false);
        }
    }

    /**
     * Whether the player pressed Cancel this frame. True for one frame.
     */
    public bool $cancelledThisFrame {
        get {
            return (bool) ($this->getState()['cancelledThisFrame'] ?? false);
        }
    }

    /**
     * Whether editing ended this frame, for any reason: Submit, Cancel, or focus moving elsewhere. True for one frame.
     */
    public bool $editEndedThisFrame {
        get {
            return (bool) ($this->getState()['editEndedThisFrame'] ?? false);
        }
    }

    /**
     * Whether the player can focus and edit the field. A field that is not interactable draws its {@see InputField::$disabledColor} and stops editing. Defaults to `true`.
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
     * Whether the pointer is over the field. Read-only.
     */
    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    /**
     * Whether the field has keyboard and gamepad focus. Read-only.
     */
    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    /**
     * The size of the text and placeholder, in UI pixels. Defaults to `24.0`.
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
     * Project-relative path to the font for the text and placeholder, such as `Assets/Fonts/Body.ttf`. An empty string uses the default UI font. Defaults to an empty string.
     */
    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_font_path', $this->getId(), $value);
        }
    }

    /**
     * The color of the text. Defaults to white.
     */
    public Color $textColor {
        get => ColorBridge::fromState($this->getState()['textColor'] ?? null, Color::white());

        set(Color $value) {
            $this->setColor('ui_input_field_set_text_color', $value);
        }
    }

    /**
     * The color of the placeholder. Defaults to a light grey-blue, `#94A0B2`.
     */
    public Color $placeholderColor {
        get => ColorBridge::fromState($this->getState()['placeholderColor'] ?? null, Color::fromRGBA(148, 160, 178));

        set(Color $value) {
            $this->setColor('ui_input_field_set_placeholder_color', $value);
        }
    }

    /**
     * The color drawn behind selected text. Defaults to a light blue, `#A8CEFF` at 75% opacity.
     */
    public Color $selectionColor {
        get => ColorBridge::fromState($this->getState()['selectionColor'] ?? null, Color::fromRGBA(168, 206, 255, 192));

        set(Color $value) {
            $this->setColor('ui_input_field_set_selection_color', $value);
        }
    }

    /**
     * The color of the caret. Defaults to white.
     */
    public Color $caretColor {
        get => ColorBridge::fromState($this->getState()['caretColor'] ?? null, Color::white());

        set(Color $value) {
            $this->setColor('ui_input_field_set_caret_color', $value);
        }
    }

    /**
     * The caret's width, in UI pixels. Defaults to `1.0`.
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
     * How many times a second the caret blinks; `0` keeps it steady. Defaults to `0.85`.
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
     * The background color while the field is idle. Defaults to a dark blue-grey, `#181E2A`.
     */
    public Color $backgroundColor {
        get => ColorBridge::fromState($this->getState()['backgroundColor'] ?? null, Color::fromRGBA(24, 30, 42));

        set(Color $value) {
            $this->setColor('ui_input_field_set_background_color', $value);
        }
    }

    /**
     * The background color while the pointer is over the field or it has focus. Defaults to `#222A3A`.
     */
    public Color $hoverColor {
        get => ColorBridge::fromState($this->getState()['hoverColor'] ?? null, Color::fromRGBA(34, 42, 58));

        set(Color $value) {
            $this->setColor('ui_input_field_set_hover_color', $value);
        }
    }

    /**
     * The background color while the player is typing. Defaults to `#222A3A`.
     */
    public Color $editingColor {
        get => ColorBridge::fromState($this->getState()['editingColor'] ?? null, Color::fromRGBA(34, 42, 58));

        set(Color $value) {
            $this->setColor('ui_input_field_set_editing_color', $value);
        }
    }

    /**
     * The background color while the field is not interactable. Defaults to `#384252` at 70% opacity.
     */
    public Color $disabledColor {
        get => ColorBridge::fromState($this->getState()['disabledColor'] ?? null, Color::fromRGBA(56, 66, 82, 180));

        set(Color $value) {
            $this->setColor('ui_input_field_set_disabled_color', $value);
        }
    }

    /**
     * Project-relative path to an image drawn behind the text, stretched to fill the field. An empty string draws {@see InputField::$backgroundColor} instead. Defaults to an empty string.
     */
    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_input_field_set_background_image', $this->getId(), $value);
        }
    }

    /**
     * The color of the line around the field. Defaults to `#5A6880`.
     */
    public Color $outlineColor {
        get => ColorBridge::fromState($this->getState()['outlineColor'] ?? null, Color::fromRGBA(90, 104, 128));

        set(Color $value) {
            $this->setColor('ui_input_field_set_outline_color', $value);
        }
    }

    /**
     * The width of the line around the field, in UI pixels; `0` hides it. Defaults to `1.0`.
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
     * Starts editing, with the caret at the end of the text. Does nothing when the field is not interactable.
     */
    public function beginEditing(): void
    {
        NativeEngine::call('ui_input_field_set_editing', $this->getId(), true);
    }

    /**
     * Stops editing and keeps the text. {@see InputField::$editEndedThisFrame} reports it on the next frame.
     */
    public function endEditing(): void
    {
        NativeEngine::call('ui_input_field_set_editing', $this->getId(), false);
    }

    /**
     * Selects all of the text, with the caret at the end.
     */
    public function selectAll(): void
    {
        $this->caretPosition = mb_strlen($this->text, 'UTF-8');
        $this->selectionAnchor = 0;
    }

    protected function receiveNativeEvent(string $event): void
    {
        $signal = match ($event) {
            'valueChanged' => $this->valueChanged,
            'submitted' => $this->submitted,
            'cancelled' => $this->cancelled,
            'editEnded' => $this->editEnded,
            default => null,
        };
        $signal?->dispatch($this);
    }

    private function setColor(string $binding, Color $value): void
    {
        $color = $value->toRGBA();
        NativeEngine::call($binding, $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }
}
