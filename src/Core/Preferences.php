<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Stores small player settings, such as volume or language, on the player's device.
 *
 * Use Preferences for settings and small non-critical values such as volume,
 * language, fullscreen, mouse sensitivity or the last selected profile. Keep
 * full save-game data in files, and never store passwords or other secrets
 * here.
 *
 * Each value is stored under a string key. The `set` methods change the
 * value in memory; call {@see Preferences::save()} to keep the change after
 * the game closes. Lenga loads saved preferences when the game starts.
 * Desktop builds keep them in files the engine manages; Web builds keep them
 * in the browser's storage for the project. Scripts use the same calls on
 * every platform and never choose a file path.
 *
 * ```php
 * $volume = Preferences::getFloat('audio.volume', 0.8);
 * Preferences::setFloat('audio.volume', 0.5);
 * Preferences::save();
 * ```
 */
final class Preferences
{
    private function __construct()
    {
        // static-only utility class
    }

    /**
     * Gets the integer saved under a key.
     *
     * @param string $key The name the value was saved under.
     * @param int $defaultValue The value to return when the key has no saved value.
     * @return int The saved value, or `$defaultValue` when there is none.
     */
    public static function getInt(string $key, int $defaultValue = 0): int
    {
        return (int) NativeEngine::call('preferences_get_int', $key, $defaultValue);
    }

    /**
     * Stores an integer under a key.
     *
     * The value is kept in memory until {@see Preferences::save()} writes it
     * to storage.
     *
     * @param string $key The name to save the value under.
     * @param int $value The value to store.
     */
    public static function setInt(string $key, int $value): void
    {
        NativeEngine::call('preferences_set_int', $key, $value);
    }

    /**
     * Gets the number saved under a key.
     *
     * @param string $key The name the value was saved under.
     * @param float $defaultValue The value to return when the key has no saved value.
     * @return float The saved value, or `$defaultValue` when there is none.
     */
    public static function getFloat(string $key, float $defaultValue = 0.0): float
    {
        return (float) NativeEngine::call('preferences_get_float', $key, $defaultValue);
    }

    /**
     * Stores a number under a key.
     *
     * The value is kept in memory until {@see Preferences::save()} writes it
     * to storage.
     *
     * @param string $key The name to save the value under.
     * @param float $value The value to store.
     */
    public static function setFloat(string $key, float $value): void
    {
        NativeEngine::call('preferences_set_float', $key, $value);
    }

    /**
     * Gets the string saved under a key.
     *
     * @param string $key The name the value was saved under.
     * @param string $defaultValue The value to return when the key has no saved value.
     * @return string The saved value, or `$defaultValue` when there is none.
     */
    public static function getString(string $key, string $defaultValue = ''): string
    {
        return (string) NativeEngine::call('preferences_get_string', $key, $defaultValue);
    }

    /**
     * Stores a string under a key.
     *
     * The value is kept in memory until {@see Preferences::save()} writes it
     * to storage.
     *
     * @param string $key The name to save the value under.
     * @param string $value The value to store.
     */
    public static function setString(string $key, string $value): void
    {
        NativeEngine::call('preferences_set_string', $key, $value);
    }

    /**
     * Gets the boolean saved under a key.
     *
     * @param string $key The name the value was saved under.
     * @param bool $defaultValue The value to return when the key has no saved value.
     * @return bool The saved value, or `$defaultValue` when there is none.
     */
    public static function getBool(string $key, bool $defaultValue = false): bool
    {
        return (bool) NativeEngine::call('preferences_get_bool', $key, $defaultValue);
    }

    /**
     * Stores a boolean under a key.
     *
     * The value is kept in memory until {@see Preferences::save()} writes it
     * to storage.
     *
     * @param string $key The name to save the value under.
     * @param bool $value The value to store.
     */
    public static function setBool(string $key, bool $value): void
    {
        NativeEngine::call('preferences_set_bool', $key, $value);
    }

    /**
     * Checks whether a value is stored under a key.
     *
     * @param string $key The name to look for.
     */
    public static function hasKey(string $key): bool
    {
        return (bool) NativeEngine::call('preferences_has_key', $key);
    }

    /**
     * Removes the value stored under a key.
     *
     * Call {@see Preferences::save()} to keep the removal after the game closes.
     *
     * @param string $key The name of the value to remove.
     */
    public static function deleteKey(string $key): void
    {
        NativeEngine::call('preferences_delete_key', $key);
    }

    /**
     * Removes every stored preference.
     *
     * Call {@see Preferences::save()} to keep the reset after the game closes.
     */
    public static function deleteAll(): void
    {
        NativeEngine::call('preferences_delete_all');
    }

    /**
     * Writes the preferences in memory to the device's storage.
     *
     * A failed write leaves the values in memory for the rest of the session.
     * In a browser, storage can be blocked by privacy settings or be full, and
     * clearing site data removes saved preferences.
     *
     * @return bool `true` when the storage accepted the write, `false` when it failed.
     */
    public static function save(): bool
    {
        return (bool) NativeEngine::call('preferences_save');
    }
}
