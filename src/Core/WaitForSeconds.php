<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class WaitForSeconds extends YieldInstruction
{
    private readonly float $resumeAt;

    public function __construct(
        private readonly float $duration,
    ) {
        $this->resumeAt = Time::time() + max(0.0, $duration);
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function keepWaiting(): bool
    {
        return Time::time() < $this->resumeAt;
    }
}
