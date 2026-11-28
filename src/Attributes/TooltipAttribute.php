<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see Tooltip} attribute. Use {@see Tooltip} in your scripts.
 *
 * The editor reads `#[TooltipAttribute('Metres per second.')]` the same way as `#[Tooltip('Metres per second.')]`. PHP
 * itself does not treat `TooltipAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[Tooltip('Metres per second.')]` works in both places.
 */
final class TooltipAttribute extends Tooltip
{
}
