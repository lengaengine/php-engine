<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Min
{
    /**
     * Use the Min attribute to enforce a minimum value on a serialized numeric field in the Inspector window.
     * 
     * @param float|int $minValue The minimum value allowed.
     */
    public function __construct(
        public readonly float|int $minValue,
    )
    {
    }
}