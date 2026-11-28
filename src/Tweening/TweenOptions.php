<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

/**
 * Settings for how a tween plays: delay, easing, time scale and relative motion.
 *
 * Start from {@see TweenOptions::make()} and chain the settings you need. Each
 * method returns a new copy, so one options value can be reused for several tweens.
 *
 * ```php
 * Tween::moveLocalTo(
 *     $this->transform,
 *     new Vector3(0.0, 0.35, 0.0),
 *     0.25,
 *     TweenOptions::make()->relative()->ease(EasingFunction::EaseOutCubic),
 * );
 * ```
 */
final class TweenOptions
{
    /**
     * Creates options with the given settings. {@see TweenOptions::make()} with the chained methods reads more clearly in most scripts.
     *
     * @param float $delay Seconds to wait before the tween starts moving.
     * @param EasingFunction $easingFunction The curve the tween follows.
     * @param bool $useUnscaledTime Whether the tween runs in real time, ignoring game pause and time scale.
     * @param bool $relative Whether the tween's target is an offset added to the start value instead of an exact end value.
     */
    public function __construct(
        public float $delay = 0.0,
        public EasingFunction $easingFunction = EasingFunction::Linear,
        public bool $useUnscaledTime = false,
        public bool $relative = false,
    ) {
    }

    /**
     * Returns options with the defaults: no delay, {@see EasingFunction::Linear}, scaled time and an exact target.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Returns a copy that waits before the tween starts moving.
     *
     * The start value is read when the tween is created, not when the delay ends.
     * The delay counts in scaled or unscaled time to match {@see TweenOptions::unscaled()}.
     *
     * @param float $delay Delay in seconds. Values below 0 are treated as 0.
     */
    public function delay(float $delay): self
    {
        $clone = clone $this;
        $clone->delay = max(0.0, $delay);
        return $clone;
    }

    /**
     * Returns a copy that uses the given easing curve.
     *
     * @param EasingFunction $easingFunction The curve the tween follows from start to end.
     */
    public function ease(EasingFunction $easingFunction): self
    {
        $clone = clone $this;
        $clone->easingFunction = $easingFunction;
        return $clone;
    }

    /**
     * Returns a copy that runs in real time, so the tween keeps moving while the game is paused or slowed.
     *
     * By default tweens use scaled game time and stop when the game is paused
     * or the time scale is 0. Use unscaled time for pause menus, selection
     * highlights and loading indicators.
     *
     * @param bool $useUnscaledTime True for real time, false for scaled game time.
     */
    public function unscaled(bool $useUnscaledTime = true): self
    {
        $clone = clone $this;
        $clone->useUnscaledTime = $useUnscaledTime;
        return $clone;
    }

    /**
     * Returns a copy that treats the tween's target as an offset from the start value.
     *
     * With relative motion, `Tween::moveLocalTo($transform, new Vector3(0.0, 1.5, 0.0), 0.3, ...)`
     * moves the object 1.5 units up from wherever it is now.
     *
     * @param bool $relative True to add the target to the start value, false to use it as the exact end value.
     */
    public function relative(bool $relative = true): self
    {
        $clone = clone $this;
        $clone->relative = $relative;
        return $clone;
    }
}
