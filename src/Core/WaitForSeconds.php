<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Coroutine wait that resumes after a number of seconds of gameplay time.
 *
 * Yield it from a coroutine started with {@see Behaviour::startCoroutine()}. It counts
 * {@see Time::time()}, so it follows the time scale and stops counting while gameplay is
 * paused. The remaining time is still there when gameplay resumes. Use
 * {@see WaitForSecondsRealtime} for a wait that keeps counting during a pause.
 *
 * ```php
 * public function start(): void
 * {
 *     $this->startCoroutine($this->runIntro());
 * }
 *
 * private function runIntro(): \Generator
 * {
 *     $this->showBanner('Ready');
 *     yield new WaitForSeconds(1.0);
 *
 *     $this->showBanner('Go');
 *     yield new WaitForSeconds(0.5);
 *
 *     $this->hideBanner();
 * }
 * ```
 *
 * The countdown starts when the object is created, so create it in the `yield`
 * statement.
 */
final class WaitForSeconds extends YieldInstruction
{
    private readonly float $resumeAt;

    /**
     * Creates a wait that ends the given number of gameplay seconds from now.
     *
     * @param float $duration Seconds of gameplay time to wait. Negative values wait as if
     *     they were `0`, which resumes the coroutine
     *     after the next `update()` call.
     */
    public function __construct(
        private readonly float $duration,
    ) {
        $this->resumeAt = Time::time() + max(0.0, $duration);
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
     * Returns `true` until {@see Time::time()} reaches the end of the wait.
     */
    public function keepWaiting(): bool
    {
        return Time::time() < $this->resumeAt;
    }
}
