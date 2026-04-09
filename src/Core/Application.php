<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Engine/application-level utilities.
 *
 * This class intentionally mirrors the idea of Application.* in other engines:
 * - Application::quit() – request engine shutdown from script code.
 * - Application::pause() / resume() – control the running game simulation.
 */
final class Application
{
    private function __construct()
    {
        // static-only utility class
    }

    /**
     * Request the running game to shut down.
     *
     * This maps to the native engine "quit" flag and will cause the main
     * loop to exit cleanly at the end of the current frame.
     */
    public static function quit(): void
    {
        \lenga_internal_engine_quit();
    }

    public static function pause(): void
    {
        \lenga_internal_engine_set_paused(true);
    }

    public static function resume(): void
    {
        \lenga_internal_engine_set_paused(false);
    }

    public static function togglePause(): void
    {
        \lenga_internal_engine_toggle_paused();
    }

    public static function isPaused(): bool
    {
        return (bool) \lenga_internal_engine_is_paused();
    }
}
