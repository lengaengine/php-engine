<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\GamepadAxis;
use Lenga\Engine\Enumerations\GamepadButton;
use Lenga\Engine\Enumerations\KeyCode;
use Lenga\Engine\Enumerations\MouseButton;
use OutOfBoundsException;
use function is_array;
use function is_numeric;
use function is_string;

/**
 * Reads the keyboard, mouse, gamepads, touch and the input axes set up in the
 * project.
 *
 * Call these methods from {@see Behaviour::update()}: each value describes the
 * current frame. For keys, {@see Input::getKeyDown()} is true only on the
 * frame a key goes down, {@see Input::getKeyUp()} only on the frame it is let
 * go, and {@see Input::getKey()} while it is held. Mouse buttons and named
 * buttons follow the same pattern. The `isKey...()` and `isGamepadButton...()`
 * methods use `Pressed` for the frame a key goes down, `Released` for the
 * frame it is let go, `Down` while it is held and `Up` while it is not.
 *
 * For movement, prefer named axes such as `Horizontal` and `Vertical` with
 * {@see Input::getAxis()}. You can change which keys, buttons and sticks
 * drive an axis in Project Settings > Input without changing your scripts.
 *
 * @example examples/Core/Input.php Move a character with the input axes and jump with a button
 */
final class Input
{
    /**
     * The number of touches on the screen this frame.
     *
     * The engine refreshes it once per frame. Defaults to `0`.
     */
    public static int $touchCount = 0;

    /**
     * The touches on the screen this frame, one {@see Touch} per finger.
     *
     * The engine refreshes the list once per frame.
     *
     * @var list<Touch>
     */
    public static array $touches = [];

    /**
     * Whether the device reports how hard each touch presses.
     *
     * When it is `false`, touch pressure values are not real measurements.
     */
    public static bool $touchPressureSupported = false;

    /**
     * Whether the device supports touch input.
     */
    public static bool $touchSupported = false;

    private function __construct() {}

    /**
     * Returns the smoothed value of a named input axis, such as `Horizontal` or
     * `Vertical`.
     *
     * Axes are set up in Project Settings > Input. Movement axes usually range
     * from -1 to 1 and ease towards their target value; mouse axes such as
     * `Mouse X` report how far the mouse moved.
     *
     * @param string $axis The name of the input axis.
     * @return float The value of the input axis, or 0.0 when the axis does not exist.
     */
    public static function getAxis(string $axis): float
    {
        $value = NativeEngine::call('input_get_axis', $axis);

        if (!is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }

    /**
     * Returns the value of a named input axis without smoothing.
     *
     * Use it when movement should respond instantly, for example for precise
     * platformer controls.
     *
     * @param string $axis The name of the input axis.
     * @return float The raw value of the input axis, or 0.0 when the axis does not exist.
     */
    public static function getAxisRaw(string $axis): float
    {
        $value = NativeEngine::call('input_get_axis_raw', $axis);

        if (!is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }

    /**
     * Checks if the specified key is currently pressed.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     * @return bool True while the key is held, false otherwise or when the key name is not recognised.
     */
    public static function getKey(int|string|KeyCode $key): bool
    {
        return self::isKeyDown($key);
    }

    /**
     * Checks if the specified key was pressed down this frame.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     * @return bool True if the key was pressed down this frame, false otherwise.
     */
    public static function getKeyDown(int|string|KeyCode $key): bool
    {
        return self::isKeyPressed($key);
    }

    /**
     * Checks if the specified key was released this frame.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     * @return bool True if the key was released this frame, false otherwise.
     */
    public static function getKeyUp(int|string|KeyCode $key): bool
    {
        return self::isKeyReleased($key);
    }

    /**
     * Returns true while a named button axis, such as `Jump`, is held.
     *
     * @param string $axis The name of a button axis from Project Settings > Input, such as `'Jump'` or `'Fire'`.
     */
    public static function getButton(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button', $axis);
    }

    /**
     * Returns true on the frame a named button axis, such as `Jump`, is pressed.
     *
     * @param string $axis The name of a button axis from Project Settings > Input, such as `'Jump'` or `'Fire'`.
     */
    public static function getButtonDown(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button_down', $axis);
    }

    /**
     * Returns true on the frame a named button axis, such as `Jump`, is released.
     *
     * @param string $axis The name of a button axis from Project Settings > Input, such as `'Jump'` or `'Fire'`.
     */
    public static function getButtonUp(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button_up', $axis);
    }

    /**
     * Returns true while the given mouse button is held.
     *
     * @param MouseButton|int $button A {@see MouseButton} case, or `0` for left, `1` for right and `2` for middle.
     */
    public static function getMouseButton(MouseButton|int $button): bool
    {
        $buttonId = $button instanceof MouseButton ? $button->value : $button;
        return (bool) NativeEngine::call('input_get_mouse_button', $buttonId);
    }

    /**
     * Returns true on the frame the given mouse button is pressed.
     *
     * @param MouseButton|int $button A {@see MouseButton} case, or `0` for left, `1` for right and `2` for middle.
     */
    public static function getMouseButtonDown(MouseButton|int $button): bool
    {
        $buttonId = $button instanceof MouseButton ? $button->value : $button;
        return (bool) NativeEngine::call('input_get_mouse_button_down', $buttonId);
    }

    /**
     * Returns true on the frame the given mouse button is released.
     *
     * @param MouseButton|int $button A {@see MouseButton} case, or `0` for left, `1` for right and `2` for middle.
     */
    public static function getMouseButtonUp(MouseButton|int $button): bool
    {
        $buttonId = $button instanceof MouseButton ? $button->value : $button;
        return (bool) NativeEngine::call('input_get_mouse_button_up', $buttonId);
    }

    /**
     * Returns the mouse position in window pixels, measured from the top-left
     * corner of the game window.
     *
     * Returns `(0, 0)` when the position is not available.
     */
    public static function getMousePosition(): Vector2
    {
        $position = NativeEngine::call('input_get_mouse_position');

        if (!is_array($position)) {
            return new Vector2(0.0, 0.0);
        }

        return new Vector2(
            (float) ($position['x'] ?? 0.0),
            (float) ($position['y'] ?? 0.0),
        );
    }

    /**
     * Returns how far the mouse moved since the previous frame, in pixels.
     *
     * Returns `(0, 0)` when the movement is not available.
     *
     * @return Vector2 The movement on the x and y axes.
     */
    public static function getMousePositionDelta(): Vector2
    {
        $position = NativeEngine::call('input_get_mouse_position_delta');

        if (!is_array($position)) {
            return new Vector2(0.0, 0.0);
        }

        return new Vector2(
            (float) ($position['x'] ?? 0.0),
            (float) ($position['y'] ?? 0.0),
        );
    }

    /**
     * Returns one touch on the screen this frame.
     *
     * Indexes run from `0` to {@see Input::$touchCount} minus one and stay the
     * same for the whole frame.
     *
     * @param int $index The position of the touch in this frame's list.
     * @throws OutOfBoundsException When there is no touch at that index this frame.
     */
    public static function getTouch(int $index): Touch
    {
        $touch = NativeEngine::call('input_get_touch', $index);
        if (!is_array($touch)) {
            throw new OutOfBoundsException("Touch index {$index} is not available in the current frame.");
        }

        return Touch::fromNativeData($touch);
    }

    /**
     * Returns true on the frame the key is pressed. Same as {@see Input::getKeyDown()}.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     */
    public static function isKeyPressed(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key_down', $resolvedKey);
    }

    /**
     * Returns true while the key is held. Same as {@see Input::getKey()}.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     */
    public static function isKeyDown(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key', $resolvedKey);
    }

    /**
     * Returns true on the frame the key is released. Same as {@see Input::getKeyUp()}.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     */
    public static function isKeyReleased(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key_up', $resolvedKey);
    }

    /**
     * Returns true while the key is not held.
     *
     * @param int|string|KeyCode $key A {@see KeyCode} case, a key name such as `'W'`, `'Space'` or `'Left Shift'`, or a raw key code.
     */
    public static function isKeyUp(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_is_key_up', $resolvedKey);
    }

    /**
     * Returns true when a gamepad is connected in the given slot.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     */
    public static function isGamepadAvailable(int $gamepad): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_available', $gamepad);
    }

    /**
     * Returns the name the gamepad in the given slot reports, or an empty string.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     */
    public static function getGamepadName(int $gamepad): string
    {
        $name = NativeEngine::call('input_get_gamepad_name', $gamepad);

        return is_string($name) ? $name : '';
    }

    /**
     * Returns true on the frame the gamepad button is pressed.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     * @param int|GamepadButton $button A {@see GamepadButton} case or its integer value.
     */
    public static function isGamepadButtonPressed(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_pressed',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /**
     * Returns true while the gamepad button is held.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     * @param int|GamepadButton $button A {@see GamepadButton} case or its integer value.
     */
    public static function isGamepadButtonDown(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_down',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /**
     * Returns true on the frame the gamepad button is released.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     * @param int|GamepadButton $button A {@see GamepadButton} case or its integer value.
     */
    public static function isGamepadButtonReleased(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_released',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /**
     * Returns true while the gamepad button is not held.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     * @param int|GamepadButton $button A {@see GamepadButton} case or its integer value.
     */
    public static function isGamepadButtonUp(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_up',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /**
     * Returns the most recent gamepad button pressed on any gamepad.
     *
     * @return int The {@see GamepadButton} value, or `GamepadButton::UNKNOWN->value` when none is known.
     */
    public static function getGamepadButtonPressed(): int
    {
        $button = NativeEngine::call('input_get_gamepad_button_pressed');

        return is_numeric($button) ? (int) $button : GamepadButton::UNKNOWN->value;
    }

    /**
     * Returns how many axes the gamepad in the given slot has.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     */
    public static function getGamepadAxisCount(int $gamepad): int
    {
        return (int) NativeEngine::call('input_get_gamepad_axis_count', $gamepad);
    }

    /**
     * Returns the position of a gamepad stick or trigger axis.
     *
     * Sticks usually range from -1 to 1.
     *
     * @param int $gamepad The gamepad slot, starting at `0` for the first connected gamepad.
     * @param int|GamepadAxis $axis A {@see GamepadAxis} case or its integer value.
     * @return float The axis value, or 0.0 when it cannot be read.
     */
    public static function getGamepadAxisMovement(int $gamepad, int|GamepadAxis $axis): float
    {
        $movement = NativeEngine::call('input_get_gamepad_axis_movement',
            $gamepad,
            self::resolveGamepadAxis($axis),
        );

        return is_numeric($movement) ? (float) $movement : 0.0;
    }

    /**
     * Adds gamepad mapping definitions so the engine recognises more gamepad
     * models.
     *
     * @param string $mappings One or more mapping lines in the SDL game controller database format.
     * @return int The number of mappings accepted.
     */
    public static function setGamepadMappings(string $mappings): int
    {
        return (int) NativeEngine::call('input_set_gamepad_mappings', $mappings);
    }

    /**
     * Replaces this frame's touch values: {@see Input::$touches},
     * {@see Input::$touchCount}, {@see Input::$touchSupported} and
     * {@see Input::$touchPressureSupported}.
     *
     * @internal Called by the runtime once per input frame.
     *
     * @param list<array<string, mixed>> $touches
     */
    public static function syncTouchSnapshotFromNative(
        array $touches,
        bool $touchSupported,
        bool $touchPressureSupported,
    ): void {
        $touchSnapshots = [];
        foreach ($touches as $touch) {
            if (!is_array($touch)) {
                continue;
            }

            $touchSnapshots[] = Touch::fromNativeData($touch);
        }

        self::$touches = $touchSnapshots;
        self::$touchCount = count($touchSnapshots);
        self::$touchSupported = $touchSupported;
        self::$touchPressureSupported = $touchPressureSupported;
    }

    private static function resolveKeyCode(int|string|KeyCode $key): ?int
    {
        return KeyCode::resolve($key);
    }

    private static function resolveGamepadButton(int|GamepadButton $button): int
    {
        return $button instanceof GamepadButton ? $button->value : $button;
    }

    private static function resolveGamepadAxis(int|GamepadAxis $axis): int
    {
        return $axis instanceof GamepadAxis ? $axis->value : $axis;
    }
}
