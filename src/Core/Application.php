<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Controls the running game as a whole: quitting and pausing.
 *
 * Use {@see Application::pause()} for pause menus. It stops gameplay time:
 * {@see Time::deltaTime()} becomes zero, `fixedUpdate()` and physics stop,
 * and `WaitForSeconds` stops counting down. `update()`, `lateUpdate()`, input
 * and UI keep running, so a pause menu can still respond.
 *
 * The engine pause ({@see Application::pauseEngine()}) is the pause the
 * editor's Pause button uses. It also stops the simulation, and the editor's
 * Step button can advance it one frame at a time. Use it for debugging tools.
 *
 * All members are static; there is no Application instance.
 */
final class Application
{
    private function __construct()
    {
        // static-only utility class
    }

    /**
     * Closes the game.
     *
     * The game shuts down cleanly once the current frame has finished.
     */
    public static function quit(): void
    {
        NativeEngine::call('engine_quit');
    }

    /**
     * Pauses the engine, the same as the editor's Pause button.
     *
     * The fixed simulation and physics stop. Use it in debugging tools; for
     * pause menus use {@see Application::pause()}.
     */
    public static function pauseEngine(): void
    {
        NativeEngine::call('engine_set_paused', true);
    }

    /**
     * Resumes the engine after {@see Application::pauseEngine()}.
     */
    public static function resumeEngine(): void
    {
        NativeEngine::call('engine_set_paused', false);
    }

    /**
     * Pauses the engine when it is running, and resumes it when it is paused.
     */
    public static function toggleEnginePause(): void
    {
        NativeEngine::call('engine_toggle_paused');
    }

    /**
     * Returns true while the engine pause is on, from {@see Application::pauseEngine()} or the editor's Pause button.
     *
     * A gameplay pause from {@see Application::pause()} does not count; use
     * {@see Application::isPaused()} to check for any pause.
     */
    public static function isEnginePaused(): bool
    {
        return (bool) NativeEngine::call('engine_is_paused');
    }

    /**
     * Pauses gameplay, for example while a pause menu is open.
     *
     * Gameplay time stops: {@see Time::deltaTime()} returns zero, `fixedUpdate()`
     * and physics stop, and `WaitForSeconds` stops counting down. `update()`,
     * `lateUpdate()`, input and UI keep running. Use
     * {@see Time::unscaledDeltaTime()} to animate menus while paused.
     */
    public static function pause(): void
    {
        Time::pauseGameplay();
    }

    /**
     * Resumes gameplay after {@see Application::pause()}.
     */
    public static function resume(): void
    {
        Time::resumeGameplay();
    }

    /**
     * Pauses gameplay when it is running, and resumes it when it is paused.
     */
    public static function togglePause(): void
    {
        Time::toggleGameplayPause();
    }

    /**
     * Returns true when gameplay time is stopped for any reason.
     *
     * That is a gameplay pause from {@see Application::pause()}, the engine
     * pause, or a time scale of zero set with {@see Time::setTimeScale()}.
     * While the editor steps a paused game one frame at a time, it still
     * returns true, so a script that returns early when paused also skips
     * stepped frames.
     */
    public static function isPaused(): bool
    {
        return self::isEnginePaused() || Time::isGameplayPaused() || Time::effectiveTimeScale() <= 0.0;
    }
}
