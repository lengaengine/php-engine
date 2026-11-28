<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see Min} attribute. Use {@see Min} in your scripts.
 *
 * The editor reads `#[MinAttribute(0)]` the same way as `#[Min(0)]`. PHP
 * itself does not treat `MinAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[Min(0)]` works in both places.
 */
final class MinAttribute extends Min
{
}
