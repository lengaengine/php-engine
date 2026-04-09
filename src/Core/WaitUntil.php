<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class WaitUntil extends YieldInstruction
{
    /**
     * @param callable(): bool $predicate
     */
    public function __construct(
        private readonly mixed $predicate,
    ) {}

    public function keepWaiting(): bool
    {
        return !((bool) ($this->predicate)());
    }
}
