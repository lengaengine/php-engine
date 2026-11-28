<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Adds empty space above a serialized field in the Inspector.
 *
 * Use it to separate groups of fields without a label. Use {@see Header} for
 * a labelled group:
 *
 * ```php
 * use Lenga\Engine\Attributes\Header;
 * use Lenga\Engine\Attributes\Space;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class PlayerController extends Behaviour
 * {
 *     public float $speed = 5.0;
 *
 *     #[Space(16)]
 *     #[Header('Stamina')]
 *     public float $maxStamina = 100.0;
 * }
 * ```
 *
 * The editor reads the attribute from the script's source, so pass the
 * height as a plain number. Write the height even when you want the default:
 * the Inspector adds no gap for `#[Space]` without an argument.
 *
 * `SpaceAttribute` is another name for this attribute. Write `Space`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Space
{
    /**
     * Creates the attribute with the height of the gap.
     *
     * @param float $height Height of the gap in Inspector pixels. Defaults to `8.0` in PHP.
     */
    public function __construct(
        /** Height of the gap in Inspector pixels. */
        public readonly float $height = 8.0,
    )
    {
    }
}
