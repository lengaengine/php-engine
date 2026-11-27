<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Stops the Inspector from setting a number field below a minimum value.
 *
 * Use it on an `int` or `float` property that can grow freely but must not
 * drop below a floor, such as a speed that cannot be negative:
 *
 * ```php
 * use Lenga\Engine\Attributes\Min;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class Enemy extends Behaviour
 * {
 *     #[Min(1)]
 *     public int $maxHealth = 100;
 *
 *     #[Min(0.0)]
 *     public float $movementSpeed = 3.5;
 * }
 * ```
 *
 * The limit applies to values edited in the Inspector. Values your scripts
 * assign while the game runs are not changed. Use {@see Range} when the field
 * needs a maximum as well. The editor reads the attribute from the script's
 * source, so pass a plain number.
 *
 * `MinAttribute` is another name for this attribute. Write `Min`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Min
{
    /**
     * Creates the attribute with the lowest allowed value.
     *
     * @param float|int $minValue The lowest value the Inspector allows.
     */
    public function __construct(
        public readonly float|int $minValue,
    )
    {
    }
}
