<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * C++ can update these internal values via the embed SAPI / extension.
 */
final class Time
{
    private function __construct() {}

    /**
     * Returns the current time since the start of the application.
     * 
     * @return float The current time in seconds.
     */
    public static function time(): float
    {
        return (float) lenga_internal_time_get_time();
    }

    /**
     * Returns the real time elapsed since application start, unaffected by pause.
     */
    public static function unscaledTime(): float
    {
        return (float) lenga_internal_time_get_unscaled_time();
    }

    /**
     * Returns the time elapsed since the last frame.
     * 
     * @return float The delta time in seconds.
     */
    public static function deltaTime(): float
    {
        return (float) lenga_internal_time_get_delta_time();
    }

    /**
     * Returns the real frame delta time, unaffected by pause.
     */
    public static function unscaledDeltaTime(): float
    {
        return (float) lenga_internal_time_get_unscaled_delta_time();
    }

    /**
     * Returns the fixed delta time value.
     * 
     * @return float The fixed delta time in seconds.
     */
    public static function fixedDeltaTime(): float
    {
        return (float) lenga_internal_time_get_fixed_delta_time();
    }

    /**
     * Returns the configured gameplay time scale.
     */
    public static function timeScale(): float
    {
        return (float) lenga_internal_time_get_time_scale();
    }

    /**
     * Returns the effective gameplay time scale after gameplay pause is applied.
     */
    public static function effectiveTimeScale(): float
    {
        return (float) lenga_internal_time_get_effective_time_scale();
    }

    public static function fixedStepCount(): int
    {
        return (int) lenga_internal_time_get_fixed_step_count();
    }

    public static function isPaused(): bool
    {
        return (bool) lenga_internal_time_is_paused();
    }

    /**
     * Sets the gameplay time scale. Values below zero are clamped by the native runtime.
     */
    public static function setTimeScale(float $scale): void
    {
        lenga_internal_time_set_time_scale($scale);
    }

    /**
     * Pauses gameplay time without invoking a full engine pause.
     */
    public static function pauseGameplay(): void
    {
        lenga_internal_time_set_gameplay_paused(true);
    }

    /**
     * Resumes gameplay time after a gameplay pause.
     */
    public static function resumeGameplay(): void
    {
        lenga_internal_time_set_gameplay_paused(false);
    }

    /**
     * Toggles gameplay pause without affecting engine/editor-level pause state.
     */
    public static function toggleGameplayPause(): void
    {
        lenga_internal_time_toggle_gameplay_paused();
    }

    /**
     * Backwards-compatible alias.
     */
    public static function toggleGameplayPaused(): void
    {
        self::toggleGameplayPause();
    }

    public static function isGameplayPaused(): bool
    {
        return (bool) lenga_internal_time_is_gameplay_paused();
    }
}
