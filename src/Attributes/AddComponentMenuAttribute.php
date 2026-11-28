<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see AddComponentMenu} attribute. Use {@see AddComponentMenu} in your scripts.
 *
 * The editor reads `#[AddComponentMenuAttribute('Enemies/Patrol')]` the same way as `#[AddComponentMenu('Enemies/Patrol')]`. PHP
 * itself does not treat `AddComponentMenuAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[AddComponentMenu('Enemies/Patrol')]` works in both places.
 */
final class AddComponentMenuAttribute extends AddComponentMenu
{
}
