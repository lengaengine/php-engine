<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

/**
 * The curve a tween follows from its start value to its end value.
 *
 * "In" curves start slowly and speed up, "Out" curves start quickly and settle,
 * and "InOut" curves do both. Sine curves are the gentlest, Quad curves are
 * stronger and Cubic curves are the strongest. Pass one to
 * {@see TweenOptions::ease()}.
 *
 * Good starting choices: `EaseOutQuad` for quick feedback that settles,
 * `EaseOutCubic` for snappy UI movement, and `EaseInOutSine` for calm,
 * decorative motion.
 */
enum EasingFunction: string
{
    /** Moves at a constant speed from start to end. */
    case Linear = 'Linear';
    /** Starts slowly and speeds up, following a quadratic curve. */
    case EaseInQuad = 'EaseInQuad';
    /** Starts quickly and slows down at the end, following a quadratic curve. */
    case EaseOutQuad = 'EaseOutQuad';
    /** Starts and ends slowly with the fastest motion in the middle, following a quadratic curve. */
    case EaseInOutQuad = 'EaseInOutQuad';
    /** Starts slowly and speeds up, following a cubic curve. */
    case EaseInCubic = 'EaseInCubic';
    /** Starts quickly and slows down at the end, following a cubic curve. */
    case EaseOutCubic = 'EaseOutCubic';
    /** Starts and ends slowly with the fastest motion in the middle, following a cubic curve. */
    case EaseInOutCubic = 'EaseInOutCubic';
    /** Starts slowly and speeds up, following a sine curve. */
    case EaseInSine = 'EaseInSine';
    /** Starts quickly and slows down at the end, following a sine curve. */
    case EaseOutSine = 'EaseOutSine';
    /** Starts and ends slowly with the fastest motion in the middle, following a sine curve. */
    case EaseInOutSine = 'EaseInOutSine';
}
