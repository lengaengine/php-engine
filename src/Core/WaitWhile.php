<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Coroutine wait that resumes once a condition stops being true.
 *
 * Yield it from a coroutine started with {@see Behaviour::startCoroutine()}. Lenga
 * calls the predicate after each of the Behaviour's `update()` calls, starting with the
 * next one after the `yield`. The coroutine continues as soon as the predicate returns
 * `false`. Because `update()` runs while gameplay is paused, the predicate is
 * still checked during a pause. {@see WaitUntil} waits for the opposite.
 *
 * ```php
 * private function waitForLanding(): \Generator
 * {
 *     yield new WaitWhile(fn (): bool => !$this->body->isGrounded());
 *     $this->playLandingDust();
 * }
 * ```
 */
final class WaitWhile extends YieldInstruction
{
    /**
     * Creates a wait that lasts while the predicate returns `true`.
     *
     * @param callable(): bool $predicate Function that returns `true` while the coroutine
     *     should keep waiting. Its result is cast to `bool`.
     */
    public function __construct(
        private readonly mixed $predicate,
    ) {}

    /**
     * Calls the predicate and returns its result.
     */
    public function keepWaiting(): bool
    {
        return (bool) ($this->predicate)();
    }
}
