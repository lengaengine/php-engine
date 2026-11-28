<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Coroutine wait that resumes after a number of seconds of real time.
 *
 * Yield it from a coroutine started with {@see Behaviour::startCoroutine()}. It counts
 * {@see Time::unscaledTime()}, so it ignores the time scale and keeps counting while
 * gameplay is paused. Use it for pause menus, UI sequences and effects such as hit stop
 * that set the time scale to `0` and must end in real time.
 *
 * ```php
 * private function hitStop(): \Generator
 * {
 *     Time::setTimeScale(0.0);
 *     yield new WaitForSecondsRealtime(0.08);
 *     Time::setTimeScale(1.0);
 * }
 * ```
 *
 * The countdown starts when the object is created, so create it in the `yield`
 * statement.
 */
final class WaitForSecondsRealtime extends YieldInstruction
{
    private readonly float $resumeAt;

    /**
     * Creates a wait that ends the given number of real seconds from now.
     *
     * @param float $duration Seconds of real time to wait. Negative values wait as if
     *     they were `0`, which resumes the coroutine
     *     after the next `update()` call.
     */
    public function __construct(
        private readonly float $duration,
    ) {
        $this->resumeAt = Time::unscaledTime() + max(0.0, $duration);
    }

    /**
     * Returns the duration passed to the constructor, in seconds.
     *
     * The value is returned as given, including negative values.
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Returns `true` until {@see Time::unscaledTime()} reaches the end of the wait.
     */
    public function keepWaiting(): bool
    {
        return Time::unscaledTime() < $this->resumeAt;
    }
}
