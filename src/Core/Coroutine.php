<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Coroutine
{
    private static int $nextId = 1;

    private readonly int $id;
    private bool $started = false;
    private bool $finished = false;
    private mixed $currentYield = null;

    public function __construct(
        private readonly \Generator $generator,
    ) {
        $this->id = self::$nextId++;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function stop(): void
    {
        $this->finished = true;
        $this->currentYield = null;
    }

    public function matchesGenerator(\Generator $generator): bool
    {
        return $this->generator === $generator;
    }

    public function prime(): void
    {
        if ($this->started || $this->finished) {
            return;
        }

        $this->started = true;
        $this->captureCurrentYield();
    }

    public function tick(): void
    {
        $this->tickForPhase(YieldInstruction::PHASE_UPDATE);
    }

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
