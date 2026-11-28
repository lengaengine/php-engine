<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Shows a heading above a serialized field in the Inspector.
 *
 * Use it to group related fields. The heading appears above the field it is
 * written on, and the fields after it follow until the next heading:
 *
 * ```php
 * use Lenga\Engine\Attributes\Header;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class PlayerController extends Behaviour
 * {
 *     #[Header('Movement')]
 *     public float $speed = 5.0;
 *     public float $jumpHeight = 2.0;
 *
 *     #[Header('Health')]
 *     public int $maxHealth = 100;
 * }
 * ```
 *
 * The attribute only changes the Inspector; it has no effect while the game
 * runs. `HeaderAttribute` is another name for this attribute. Write `Header`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Header
{
    /**
     * Creates the attribute with the heading text.
     *
     * @param string $headerText The text shown as the heading.
     */
    public function __construct(
        public readonly string $headerText,
    )
    {
    }
}
