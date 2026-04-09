<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Space
{
    public function __construct(
        public readonly float $height = 8.0,
    )
    {
    }
}
