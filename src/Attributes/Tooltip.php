<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Shows a hint when the pointer rests on a serialized field in the Inspector.
 *
 * Use the tooltip to explain what a value does or which unit it uses:
 *
 * ```php
 * use Lenga\Engine\Attributes\Tooltip;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class PlayerController extends Behaviour
 * {
 *     #[Tooltip('Movement speed in units per second.')]
 *     public float $speed = 5.0;
 * }
 * ```
 *
 * The hint appears over the field's label and its value. The attribute only
 * changes the Inspector. `TooltipAttribute` is another name for this
 * attribute. Write `Tooltip`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Tooltip
{
    /**
     * Creates the attribute with the hint text.
     *
     * @param string $tooltipText The text shown in the hint.
     */
    public function __construct(
        public readonly string $tooltipText,
    )
    {
    }
}
