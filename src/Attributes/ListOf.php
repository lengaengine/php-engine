<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Describes the element type of an inspector-visible array property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ListOf
{
    /**
     * @param class-string $listType
     */
    public function __construct(public readonly string $listType)
    {
    }
}
