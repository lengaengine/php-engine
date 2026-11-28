<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see Space} attribute. Use {@see Space} in your scripts.
 *
 * The editor reads `#[SpaceAttribute(12)]` the same way as `#[Space(12)]`. PHP
 * itself does not treat `SpaceAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[Space(12)]` works in both places.
 */
final class SpaceAttribute extends Space
{
}
