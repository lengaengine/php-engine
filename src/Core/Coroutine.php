<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Generator;
use Throwable;

/**
 * A handle to a running coroutine, returned by {@see Behaviour::startCoroutine()}.
 *
 * Keep the handle when you may need to stop the coroutine early or check
 * whether it has finished. Stop it with {@see Behaviour::stopCoroutine()}
 * or {@see Coroutine::stop()}.
 *
 * ```php
 * private ?Coroutine $blink = null;
 *
 * public function onEnable(): void
 * {
 *     $this->blink = $this->startCoroutine($this->blinkRoutine());
 * }
 *
 * public function onDisable(): void
 * {
 *     if ($this->blink !== null) {
 *         $this->stopCoroutine($this->blink);
 *         $this->blink = null;
 *     }
 * }
 * ```
 */
final class Coroutine
{
    private static int $nextId = 1;

    private readonly int $id;
    private bool $started = false;
    private bool $finished = false;
    private mixed $currentYield = null;

    /**
     * Wraps a generator as a coroutine.
     *
     * @internal Behaviours create coroutines. Start one with {@see Behaviour::startCoroutine()}.
     *
     * @param Generator $generator The generator the coroutine runs.
     */
    public function __construct(
        private readonly Generator $generator,
    ) {
        $this->id = self::$nextId++;
    }

    /**
     * Returns a number that identifies this coroutine, unique among the coroutines in the running game.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns true when the coroutine has run to its end or was stopped.
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * Stops the coroutine. It does not run again.
     */
    public function stop(): void
    {
        $this->finished = true;
        $this->currentYield = null;
    }

    /**
     * Returns true when this coroutine runs the given generator.
     *
     * @internal Used by {@see Behaviour::stopCoroutine()} to find a coroutine by its generator.
     *
     * @param Generator $generator The generator to compare.
     */
    public function matchesGenerator(Generator $generator): bool
    {
        return $this->generator === $generator;
    }

    /**
     * Runs the coroutine up to its first `yield`.
     *
     * @internal Called by {@see Behaviour::startCoroutine()}.
     */
    public function prime(): void
    {
        if ($this->started || $this->finished) {
            return;
        }

        $this->started = true;
        $this->captureCurrentYield();
    }

    /**
     * Advances the coroutine for the frame's update step.
     *
     * @internal Called by the owning Behaviour after its `update()`.
     */
    public function tick(): void
    {
        $this->tickForPhase(YieldInstruction::PHASE_UPDATE);
    }

    /**
     * Advances the coroutine for a fixed physics step.
     *
     * @internal Called by the owning Behaviour after its `fixedUpdate()`.
     */
    public function tickFixedUpdate(): void
    {
        $this->tickForPhase(YieldInstruction::PHASE_FIXED_UPDATE);
    }

    private function tickForPhase(string $phase): void
    {
        if ($this->finished) {
            return;
        }

        if (!$this->started) {
            if ($phase === YieldInstruction::PHASE_UPDATE) {
                $this->prime();
            }
            return;
        }

        // A coroutine whose code throws has ended: PHP cannot resume a
        // generator after an exception. The exception goes on to the caller.
        try {
            if ($this->currentYield instanceof YieldInstruction) {
                if ($this->currentYield->resumePhase() !== $phase) {
                    return;
                }

                if ($this->currentYield->keepWaiting()) {
                    return;
                }
            } elseif ($phase !== YieldInstruction::PHASE_UPDATE) {
                return;
            }

            $this->generator->next();
            $this->captureCurrentYield();
        } catch (Throwable $throwable) {
            $this->stop();
            throw $throwable;
        }
    }

    private function captureCurrentYield(): void
    {
        if (!$this->generator->valid()) {
            $this->finished = true;
            $this->currentYield = null;
            return;
        }

        $this->currentYield = $this->generator->current();
    }
}
