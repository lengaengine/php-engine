<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Coroutine wait that resumes once a condition becomes true.
 *
 * Yield it from a coroutine started with {@see Behaviour::startCoroutine()}. Lenga
 * calls the predicate after each of the Behaviour's `update()` calls, starting with the
 * next one after the `yield`. The coroutine continues as soon as the predicate returns
 * `true`. Because `update()` runs while gameplay is paused, the predicate is
 * still checked during a pause. {@see WaitWhile} waits for the opposite.
 *
 * ```php
 * private function waitForDoor(): \Generator
 * {
 *     yield new WaitUntil(fn (): bool => $this->door->isOpen);
 *     $this->walkThrough();
 * }
 * ```
 */
final class WaitUntil extends YieldInstruction
{
    /**
     * Creates a wait that ends when the predicate returns `true`.
     *
     * @param callable(): bool $predicate Function that returns `true` when the coroutine
     *     should continue. Its result is cast to `bool`.
     */
    public function __construct(
        private readonly mixed $predicate,
    ) {}

    /**
     * Calls the predicate and returns `true` while it returns `false`.
     */
    public function keepWaiting(): bool
    {
        return !((bool) ($this->predicate)());
    }
}
