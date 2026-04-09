<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

abstract class YieldInstruction
{
    public const PHASE_UPDATE = 'update';
    public const PHASE_FIXED_UPDATE = 'fixedUpdate';

    public function resumePhase(): string
    {
        return self::PHASE_UPDATE;
    }

    abstract public function keepWaiting(): bool;
}
