<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Small engine-managed local preferences.
 *
 * Use Preferences for settings and tiny non-critical state such as volume,
 * language, fullscreen, or the last selected profile. It is not a save-game
 * system and should not be used for secrets.
 */
final class Preferences
{
    private function __construct()
    {
        // static-only utility class
    }

    public static function getInt(string $key, int $defaultValue = 0): int
    {
        return (int) \lenga_internal_preferences_get_int($key, $defaultValue);
    }

    public static function setInt(string $key, int $value): void
    {
        \lenga_internal_preferences_set_int($key, $value);
    }

    public static function getFloat(string $key, float $defaultValue = 0.0): float
    {
        return (float) \lenga_internal_preferences_get_float($key, $defaultValue);
    }

    public static function setFloat(string $key, float $value): void
    {
        \lenga_internal_preferences_set_float($key, $value);
    }

    public static function getString(string $key, string $defaultValue = ''): string
    {
        return (string) \lenga_internal_preferences_get_string($key, $defaultValue);
    }

    public static function setString(string $key, string $value): void
    {
        \lenga_internal_preferences_set_string($key, $value);
    }

    public static function getBool(string $key, bool $defaultValue = false): bool
    {
        return (bool) \lenga_internal_preferences_get_bool($key, $defaultValue);
    }

    public static function setBool(string $key, bool $value): void
    {
        \lenga_internal_preferences_set_bool($key, $value);
    }

    public static function hasKey(string $key): bool
    {
        return (bool) \lenga_internal_preferences_has_key($key);
    }

    public static function deleteKey(string $key): void
    {
        \lenga_internal_preferences_delete_key($key);
    }

    public static function deleteAll(): void
    {
        \lenga_internal_preferences_delete_all();
    }

    public static function save(): bool
    {
        return (bool) \lenga_internal_preferences_save();
    }
}
