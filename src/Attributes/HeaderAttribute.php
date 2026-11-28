<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see Header} attribute. Use {@see Header} in your scripts.
 *
 * The editor reads `#[HeaderAttribute('Movement')]` the same way as `#[Header('Movement')]`. PHP
 * itself does not treat `HeaderAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[Header('Movement')]` works in both places.
 */
final class HeaderAttribute extends Header
{
}
