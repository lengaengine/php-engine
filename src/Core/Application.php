<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Engine/application-level utilities.
 *
 * This class intentionally mirrors the idea of Application.* in other engines:
 * - Application::quit() – request engine shutdown from script code.
 * - Application::pauseEngine() / resumeEngine() – control engine-level pause.
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

    public static function pauseEngine(): void
    {
        \lenga_internal_engine_set_paused(true);
    }

    public static function resumeEngine(): void
    {
        \lenga_internal_engine_set_paused(false);
    }

    public static function toggleEnginePause(): void
    {
        \lenga_internal_engine_toggle_paused();
    }

    public static function isEnginePaused(): bool
    {
        return (bool) \lenga_internal_engine_is_paused();
    }

    /**
     * Backwards-compatible alias for engine-level pause.
     */
    public static function pause(): void
    {
        self::pauseEngine();
    }

    /**
     * Backwards-compatible alias for engine-level resume.
     */
    public static function resume(): void
    {
        self::resumeEngine();
    }

    /**
     * Backwards-compatible alias for engine-level pause toggle.
     */
    public static function togglePause(): void
    {
        self::toggleEnginePause();
    }

    /**
     * Backwards-compatible alias for engine-level paused state.
     */
    public static function isPaused(): bool
    {
        return self::isEnginePaused();
    }
}
