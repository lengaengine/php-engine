<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Tooltip
{
    /**
     * Use the Tooltip attribute to add a tooltip to a serialized field in the Inspector window.
     * 
     * @param string $tooltipText The text to display as the tooltip.
     */
    public function __construct(
        public readonly string $tooltipText,
    )
    {
    }
}