<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see Range} attribute. Use {@see Range} in your scripts.
 *
 * The editor reads `#[RangeAttribute(0.0, 1.0)]` the same way as `#[Range(0.0, 1.0)]`. PHP
 * itself does not treat `RangeAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[Range(0.0, 1.0)]` works in both places.
 */
final class RangeAttribute extends Range
{
}
