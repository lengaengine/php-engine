<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TextArea
{
    /**
     * Use the TextArea attribute to display a multi-line text area for a serialized string field in the Inspector window.
     * 
     * @param int $minLines The minimum number of lines to display.
     * @param int $maxLines The maximum number of lines to display.
     */
    public function __construct(
        public readonly int $minLines = 3,
        public readonly int $maxLines = 10,
    )
    {
    }
}