<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class WaitForSecondsRealtime extends YieldInstruction
{
    private readonly float $resumeAt;

    public function __construct(
        private readonly float $duration,
    ) {
        $this->resumeAt = Time::unscaledTime() + max(0.0, $duration);
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function keepWaiting(): bool
    {
        return Time::unscaledTime() < $this->resumeAt;
    }
}
