<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Saves an object-typed property as a nested object and lets the Inspector edit its fields.
 *
 * Use it for a property that holds one of your own data classes, an
 * interface or an abstract class:
 *
 * ```php
 * use Lenga\Engine\Attributes\SerializeReference;
 * use Lenga\Engine\Core\Behaviour;
 *
 * interface Weapon
 * {
 *     public function attack(): void;
 * }
 *
 * final class Sword implements Weapon
 * {
 *     public float $damage = 25.0;
 *
 *     public function attack(): void
 *     {
 *     }
 * }
 *
 * final class Player extends Behaviour
 * {
 *     #[SerializeReference]
 *     public ?Weapon $weapon = null;
 * }
 * ```
 *
 * The scene stores the object's class together with its public and
 * serialized fields, so a `Sword` saved in a `Weapon` property loads back as a
 * `Sword`. The Inspector shows the fields in a nested editor and lets you
 * choose a class that fits the declared type. A nullable property can be
 * created and cleared from the Inspector. A property that is not nullable
 * and has no saved value gets a new object of its declared class when the
 * scene loads, if that class is concrete. Like {@see SerializeField}, the
 * attribute also exposes a `protected` or `private` property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SerializeReference
{
}
