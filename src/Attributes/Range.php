<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Range
{
    /**
     * Use the Range attribute to enforce a value range on a serialized numeric field in the Inspector window.
     * When this attribute is used, the float or int will be shown as a slider in the Inspector instead of the default number field.
     * 
     * @param float|int $minValue The minimum value allowed.
     * @param float|int $maxValue The maximum value allowed.
     */
    public function __construct(
        public readonly float|int $minValue,
        public readonly float|int $maxValue,
    )
    {
    }
}