<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Names an icon image for a class.
 *
 * Put the attribute on the class:
 *
 * ```php
 * use Lenga\Engine\Attributes\Icon;
 * use Lenga\Engine\Core\Behaviour;
 *
 * #[Icon('Icons/enemy.png')]
 * final class Enemy extends Behaviour
 * {
 * }
 * ```
 *
 * The editor does not read this attribute, so it has no visible effect.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Icon
{
    /**
     * Creates the attribute with the path of the icon image.
     *
     * @param string $iconPath Path of the icon image file.
     */
    public function __construct(
        public readonly string $iconPath,
    )
    {
    }
}
