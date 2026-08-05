<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Icon
{
    /**
     * Use the Icon attribute to set a custom icon for a class in the Editor.
     *
     * @param string $iconPath The path to the icon file.
     */
    public function __construct(
        public readonly string $iconPath,
    )
    {
    }
}
