<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Engine/application-level utilities.
 *
 * Application pause is gameplay-level pause: Update and UI remain alive while
 * gameplay time is stopped. Engine pause is the editor/debug transport pause
 * that freezes simulation more aggressively.
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
     * This maps to the native engine quit flag and will cause the main
     * loop to exit cleanly at the end of the current frame.
     */
    public static function quit(): void
    {
        \Lenga\Engine\Core\NativeEngine::call('engine_quit');
    }

    /**
     * Full engine/editor transport pause. Use this for debugging/tooling, not
     * normal pause menus.
     */
    public static function pauseEngine(): void
    {
        \Lenga\Engine\Core\NativeEngine::call('engine_set_paused', true);
    }

    /**
     * Resume from full engine/editor transport pause.
     */
    public static function resumeEngine(): void
    {
        \Lenga\Engine\Core\NativeEngine::call('engine_set_paused', false);
    }

    /**
     * Toggle full engine/editor transport pause.
     */
    public static function toggleEnginePause(): void
    {
        \Lenga\Engine\Core\NativeEngine::call('engine_toggle_paused');
    }

    /**
     * True only when the low-level engine/editor transport pause is active.
     */
    public static function isEnginePaused(): bool
    {
        return (bool) \Lenga\Engine\Core\NativeEngine::call('engine_is_paused');
    }

    /**
     * Gameplay/application pause. Update and UI continue to run.
     */
    public static function pause(): void
    {
        Time::pauseGameplay();
    }

    /**
     * Resume gameplay/application pause.
     */
    public static function resume(): void
    {
        Time::resumeGameplay();
    }

    /**
     * Toggle gameplay/application pause.
     */
    public static function togglePause(): void
    {
        Time::toggleGameplayPause();
    }

    /**
     * True when the application is effectively paused from user code. This
     * includes gameplay pause, explicit engine pause, and a zero effective time
     * scale because all three mean gameplay time is stopped.
     */
    public static function isPaused(): bool
    {
        return self::isEnginePaused() || Time::isGameplayPaused() || Time::effectiveTimeScale() <= 0.0;
    }
}
