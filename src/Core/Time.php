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

    public static function fixedStepCount(): int
    {
        return (int) lenga_internal_time_get_fixed_step_count();
    }

    public static function isPaused(): bool
    {
        return (bool) lenga_internal_time_is_paused();
    }
}
