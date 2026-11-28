<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Frame timing, time scale and gameplay pause for scripts.
 *
 * Use `Time` to make movement, cooldowns, timers and animation behave the same at
 * any frame rate. Lenga keeps two clocks:
 *
 * - Gameplay time ({@see Time::time()}, {@see Time::deltaTime()}) follows the time
 *   scale and stops while gameplay is paused. Use it for movement, gameplay timers
 *   and anything that should freeze with the game.
 * - Unscaled time ({@see Time::unscaledTime()}, {@see Time::unscaledDeltaTime()})
 *   is real time. It keeps advancing while gameplay is paused or slowed down. Use it
 *   for pause menus, UI animation and effects such as hit stop that must finish in
 *   real time.
 *
 * Both clocks start at zero when the game starts running. All values are in seconds.
 *
 * `update()` and `lateUpdate()` keep running while gameplay is paused, with
 * {@see Time::deltaTime()} at `0`. `fixedUpdate()` and physics stop until gameplay
 * resumes.
 *
 * @example examples/Core/Time.php Move at a steady speed, pause gameplay and keep a pause menu animating
 */
final class Time
{
    private function __construct() {}

    /**
     * Returns the gameplay time in seconds since the game started running.
     *
     * Gameplay time advances by {@see Time::deltaTime()} each frame, so it follows
     * the time scale and stops while gameplay is paused. Compare against it for
     * cooldowns and timers that should freeze with the game, for example
     * `Time::time() + $cooldown`.
     *
     * @return float The gameplay time in seconds.
     */
    public static function time(): float
    {
        return (float) NativeEngine::call('time_get_time');
    }

    /**
     * Returns the real time in seconds since the game started running.
     *
     * Unscaled time ignores the time scale and keeps advancing while gameplay is
     * paused. Use it for timers that must run during a pause or a slow-motion effect.
     *
     * @return float The unscaled time in seconds.
     */
    public static function unscaledTime(): float
    {
        return (float) NativeEngine::call('time_get_unscaled_time');
    }

    /**
     * Returns the gameplay time in seconds that passed since the previous frame.
     *
     * Multiply speeds by this value in `update()` to move at the same rate at any
     * frame rate: `$speed * Time::deltaTime()` gives the distance to move this frame.
     * It equals the real frame time multiplied by the time scale, and it is `0`
     * while gameplay is paused.
     *
     * @return float The frame delta time in seconds.
     */
    public static function deltaTime(): float
    {
        return (float) NativeEngine::call('time_get_delta_time');
    }

    /**
     * Returns the real time in seconds that passed since the previous frame.
     *
     * The value ignores the time scale and gameplay pause. Use it to animate menus
     * and UI that should keep moving while the game is paused.
     *
     * @return float The unscaled frame delta time in seconds.
     */
    public static function unscaledDeltaTime(): float
    {
        return (float) NativeEngine::call('time_get_unscaled_delta_time');
    }

    /**
     * Returns the length of one fixed physics step in seconds.
     *
     * `fixedUpdate()` and the physics simulation run in steps of this length. Use it
     * instead of {@see Time::deltaTime()} for calculations inside `fixedUpdate()`, such
     * as `$acceleration * Time::fixedDeltaTime()`.
     *
     * @return float The fixed delta time in seconds.
     */
    public static function fixedDeltaTime(): float
    {
        return (float) NativeEngine::call('time_get_fixed_delta_time');
    }

    /**
     * Returns the gameplay time scale set with {@see Time::setTimeScale()}.
     *
     * `1.0` is normal speed, `0.5` is half speed and `0.0` stops gameplay time.
     * Gameplay pause does not change this value. Read
     * {@see Time::effectiveTimeScale()} for the scale that is applied this frame.
     *
     * @return float The time scale. Defaults to `1.0` when the game starts.
     */
    public static function timeScale(): float
    {
        return (float) NativeEngine::call('time_get_time_scale');
    }

    /**
     * Returns the time scale that gameplay time actually uses.
     *
     * The value is `0.0` while gameplay is paused and equals {@see Time::timeScale()}
     * otherwise.
     *
     * @return float The effective time scale.
     */
    public static function effectiveTimeScale(): float
    {
        return (float) NativeEngine::call('time_get_effective_time_scale');
    }

    /**
     * Returns how many fixed physics steps have run since the game started running.
     *
     * The count goes up by one before each `fixedUpdate()` step. Compare two readings
     * to tell whether a physics step has happened in between.
     *
     * @return int The number of fixed steps so far.
     */
    public static function fixedStepCount(): int
    {
        return (int) NativeEngine::call('time_get_fixed_step_count');
    }

    /**
     * Returns whether the whole engine is paused, for example with the editor's Pause button.
     *
     * This reports engine pause, set with {@see Application::pauseEngine()} or the editor
     * transport. It stays `true` during an editor Step frame. Gameplay pause is reported
     * by {@see Time::isGameplayPaused()}, and {@see Application::isPaused()} reports
     * either kind of pause.
     */
    public static function isPaused(): bool
    {
        return (bool) NativeEngine::call('time_is_paused');
    }

    /**
     * Sets the gameplay time scale for slow motion, fast forward or hit stop.
     *
     * `1.0` is normal speed, `0.5` is half speed and `0.0` stops gameplay time.
     * Gameplay time, {@see Time::deltaTime()}, fixed steps and {@see WaitForSeconds}
     * all follow the scale. Unscaled time does not.
     *
     * @param float $scale The new time scale. Negative values are stored as `0.0`, and
     *     values that are not finite, such as `INF` or `NAN`, reset the scale to `1.0`.
     */
    public static function setTimeScale(float $scale): void
    {
        NativeEngine::call('time_set_time_scale', $scale);
    }

    /**
     * Pauses gameplay time, for example when a pause menu opens.
     *
     * Gameplay time stops, {@see Time::deltaTime()} becomes `0`, and `fixedUpdate()` and
     * physics stop. `update()`, `lateUpdate()`, input and UI keep running, so the game
     * can still react to a resume button. The time scale keeps its value and applies
     * again after {@see Time::resumeGameplay()}. {@see Application::pause()} does the same.
     */
    public static function pauseGameplay(): void
    {
        NativeEngine::call('time_set_gameplay_paused', true);
    }

    /**
     * Resumes gameplay time after {@see Time::pauseGameplay()}.
     */
    public static function resumeGameplay(): void
    {
        NativeEngine::call('time_set_gameplay_paused', false);
    }

    /**
     * Pauses gameplay if it is running, or resumes it if it is paused.
     *
     * Use it for a single pause key or button. It changes gameplay pause only and
     * leaves engine pause ({@see Time::isPaused()}) as it is.
     */
    public static function toggleGameplayPause(): void
    {
        NativeEngine::call('time_toggle_gameplay_paused');
    }

    /**
     * Pauses or resumes gameplay. Same as {@see Time::toggleGameplayPause()}.
     */
    public static function toggleGameplayPaused(): void
    {
        self::toggleGameplayPause();
    }

    /**
     * Returns whether gameplay is paused with {@see Time::pauseGameplay()} or {@see Application::pause()}.
     *
     * A time scale of `0.0` does not count as a gameplay pause. Use
     * {@see Application::isPaused()} to check for any kind of pause.
     */
    public static function isGameplayPaused(): bool
    {
        return (bool) NativeEngine::call('time_is_gameplay_paused');
    }
}
