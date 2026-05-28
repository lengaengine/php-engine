<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class WaitForFixedUpdate extends YieldInstruction
{
    private readonly int $createdAtFixedStep;

    public function __construct()
    {
        $this->createdAtFixedStep = Time::fixedStepCount();
    }

    public function resumePhase(): string
    {
        return self::PHASE_FIXED_UPDATE;
    }

    public function keepWaiting(): bool
    {
        return Time::fixedStepCount() <= $this->createdAtFixedStep;
    }
}
