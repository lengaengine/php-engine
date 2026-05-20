<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\GamepadAxis;
use Lenga\Engine\Enumerations\GamepadButton;
use Lenga\Engine\Enumerations\KeyCode;
use OutOfBoundsException;
use function is_array;
use function is_numeric;
use function is_string;

/**
 * Class Input
 *
 * Provides static methods to access input states such as axes and key presses.
 */
final class Input
{
    /** Number of touches captured for the current frame. */
    public static int $touchCount = 0;

    /**
     * Touch snapshots captured for the current frame.
     *
     * @var list<Touch>
     */
    public static array $touches = [];

    /** Whether the active backend reports real touch pressure values. */
    public static bool $touchPressureSupported = false;

    /** Whether the active backend reports touch input support. */
    public static bool $touchSupported = false;

    private function __construct() {}

    /**
     * Retrieves the value of the specified input axis.
     *
     * @param string $axis The name of the input axis.
     * @return float The value of the input axis, or 0.0 if not valid.
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
     * Retrieves the raw value of the specified input axis.
     *
     * @param string $axis The name of the input axis.
     * @return float The raw value of the input axis, or 0.0 if not valid.
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
     * @param int|string|KeyCode $key The key identifier or raylib key code.
     * @return bool True if the key is pressed, false otherwise.
     */
    public static function getKey(int|string|KeyCode $key): bool
    {
        return self::isKeyDown($key);
    }

    /**
     * Checks if the specified key was pressed down this frame.
     *
     * @param int|string|KeyCode $key The key identifier or raylib key code.
     * @return bool True if the key was pressed down this frame, false otherwise.
     */
    public static function getKeyDown(int|string|KeyCode $key): bool
    {
        return self::isKeyPressed($key);
    }

    /**
     * Checks if the specified key was released this frame.
     *
     * @param int|string|KeyCode $key The key identifier or raylib key code.
     * @return bool True if the key was released this frame, false otherwise.
     */
    public static function getKeyUp(int|string|KeyCode $key): bool
    {
        return self::isKeyReleased($key);
    }

    /** Reads virtual buttons such as `Jump`. */
    public static function getButton(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button', $axis);
    }

    /** Reads whether a virtual button was pressed this frame. */
    public static function getButtonDown(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button_down', $axis);
    }

    /** Reads whether a virtual button was released this frame. */
    public static function getButtonUp(string $axis): bool
    {
        return (bool) NativeEngine::call('input_get_button_up', $axis);
    }

    /** Reads whether the given mouse button is currently held. */
    public static function getMouseButton(int $button): bool
    {
        return (bool) NativeEngine::call('input_get_mouse_button', $button);
    }

    /** Reads whether the given mouse button was pressed this frame. */
    public static function getMouseButtonDown(int $button): bool
    {
        return (bool) NativeEngine::call('input_get_mouse_button_down', $button);
    }

    /** Reads whether the given mouse button was released this frame. */
    public static function getMouseButtonUp(int $button): bool
    {
        return (bool) NativeEngine::call('input_get_mouse_button_up', $button);
    }

    /**
     * Returns a touch snapshot by frame-stable index.
     *
     * @throws OutOfBoundsException when the touch index is not available.
     */
    public static function getTouch(int $index): Touch
    {
        $touch = NativeEngine::call('input_get_touch', $index);
        if (!is_array($touch)) {
            throw new OutOfBoundsException("Touch index {$index} is not available in the current frame.");
        }

        return Touch::fromNativeData($touch);
    }

    /** Raylib-style alias for `IsKeyPressed()`. */
    public static function isKeyPressed(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key_down', $resolvedKey);
    }

    /** Raylib-style alias for `IsKeyDown()`. */
    public static function isKeyDown(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key', $resolvedKey);
    }

    /** Raylib-style alias for `IsKeyReleased()`. */
    public static function isKeyReleased(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_get_key_up', $resolvedKey);
    }

    /** Raylib-style alias for `IsKeyUp()`. */
    public static function isKeyUp(int|string|KeyCode $key): bool
    {
        $resolvedKey = self::resolveKeyCode($key);
        if ($resolvedKey === null) {
            return false;
        }

        return (bool) NativeEngine::call('input_is_key_up', $resolvedKey);
    }

    /** Mirrors raylib's `IsGamepadAvailable()`. */
    public static function isGamepadAvailable(int $gamepad): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_available', $gamepad);
    }

    /** Mirrors raylib's `GetGamepadName()`. */
    public static function getGamepadName(int $gamepad): string
    {
        $name = NativeEngine::call('input_get_gamepad_name', $gamepad);

        return is_string($name) ? $name : '';
    }

    /** Mirrors raylib's `IsGamepadButtonPressed()`. */
    public static function isGamepadButtonPressed(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_pressed',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /** Mirrors raylib's `IsGamepadButtonDown()`. */
    public static function isGamepadButtonDown(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_down',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /** Mirrors raylib's `IsGamepadButtonReleased()`. */
    public static function isGamepadButtonReleased(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_released',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /** Mirrors raylib's `IsGamepadButtonUp()`. */
    public static function isGamepadButtonUp(int $gamepad, int|GamepadButton $button): bool
    {
        return (bool) NativeEngine::call('input_is_gamepad_button_up',
            $gamepad,
            self::resolveGamepadButton($button),
        );
    }

    /** Mirrors raylib's `GetGamepadButtonPressed()`. */
    public static function getGamepadButtonPressed(): int
    {
        $button = NativeEngine::call('input_get_gamepad_button_pressed');

        return is_numeric($button) ? (int) $button : GamepadButton::UNKNOWN->value;
    }

    /** Mirrors raylib's `GetGamepadAxisCount()`. */
    public static function getGamepadAxisCount(int $gamepad): int
    {
        return (int) NativeEngine::call('input_get_gamepad_axis_count', $gamepad);
    }

    /** Mirrors raylib's `GetGamepadAxisMovement()`. */
    public static function getGamepadAxisMovement(int $gamepad, int|GamepadAxis $axis): float
    {
        $movement = NativeEngine::call('input_get_gamepad_axis_movement',
            $gamepad,
            self::resolveGamepadAxis($axis),
        );

        return is_numeric($movement) ? (float) $movement : 0.0;
    }

    /** Mirrors raylib's `SetGamepadMappings()`. */
    public static function setGamepadMappings(string $mappings): int
    {
        return (int) NativeEngine::call('input_set_gamepad_mappings', $mappings);
    }

    /**
     * Synchronizes frame-stable touch properties from the native runtime.
     *
     * @internal Called by the C++ runtime once per input frame.
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
