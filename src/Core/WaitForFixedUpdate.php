<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Coroutine wait that resumes after the next fixed physics step.
 *
 * Yield it from a coroutine started with {@see Behaviour::startCoroutine()} when the
 * next lines should run in step with physics, for example to apply a force or read a
 * physics result. The coroutine continues during the next fixed step, right after the
 * Behaviour's `fixedUpdate()` call. It stays paused while gameplay is paused, because
 * fixed steps do not run then.
 *
 * ```php
 * private function launch(): \Generator
 * {
 *     yield new WaitForFixedUpdate();
 *     $this->body?->addForce(new Vector2(0.0, 12.0));
 * }
 * ```
 */
final class WaitForFixedUpdate extends YieldInstruction
{
    private readonly int $createdAtFixedStep;

    /**
     * Creates a wait that ends at the first fixed step after the current one.
     */
    public function __construct()
    {
        $this->createdAtFixedStep = Time::fixedStepCount();
    }

    /**
     * Returns {@see YieldInstruction::PHASE_FIXED_UPDATE}, so the wait is checked during fixed steps.
     */
    public function resumePhase(): string
    {
        return self::PHASE_FIXED_UPDATE;
    }

    /**
     * Returns `true` until a fixed step has run since the wait was created.
     */
    public function keepWaiting(): bool
    {
        return Time::fixedStepCount() <= $this->createdAtFixedStep;
    }
}
