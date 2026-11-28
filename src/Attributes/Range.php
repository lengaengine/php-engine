<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Shows a number field as a slider and keeps its Inspector value between a minimum and a maximum.
 *
 * Use it on an `int` or `float` property that designers should tune within
 * a known band:
 *
 * ```php
 * use Lenga\Engine\Attributes\Range;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class AudioManager extends Behaviour
 * {
 *     #[Range(0.0, 1.0)]
 *     public float $masterVolume = 0.8;
 *
 *     #[Range(0, 10)]
 *     public int $reverb = 3;
 * }
 * ```
 *
 * The slider and the limits apply to values edited in the Inspector. Values
 * your scripts assign while the game runs are not changed. Use {@see Min}
 * when the field only needs a lower limit. The editor reads the attribute
 * from the script's source, so pass both limits as plain numbers.
 *
 * `RangeAttribute` is another name for this attribute. Write `Range`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Range
{
    /**
     * Creates the attribute with the lowest and highest allowed values.
     *
     * @param float|int $minValue The left end of the slider and the lowest value the Inspector allows.
     * @param float|int $maxValue The right end of the slider and the highest value the Inspector allows.
     */
    public function __construct(
        public readonly float|int $minValue,
        public readonly float|int $maxValue,
    )
    {
    }
}
