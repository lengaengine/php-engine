<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Header
{
    /**
     * Use the Header attribute to add a header above a serialized field in the Inspector window.
     * 
     * @param string $headerText The text to display as the header.
     */
    public function __construct(
        public readonly string $headerText,
    )
    {
    }
}