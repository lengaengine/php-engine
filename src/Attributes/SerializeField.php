<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Shows a `protected` or `private` property in the Inspector and saves it with the scene.
 *
 * Public properties of a Behaviour are serialized without an attribute. Add
 * `#[SerializeField]` when a value should be set in the editor but stay out of
 * the class's public API:
 *
 * ```php
 * use Lenga\Engine\Attributes\SerializeField;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class PlayerController extends Behaviour
 * {
 *     #[SerializeField]
 *     private float $speed = 5.0;
 *
 *     #[SerializeField]
 *     private int $maxJumps = 2;
 *
 *     private int $jumpsRemaining = 0;
 *
 *     public function start(): void
 *     {
 *         $this->jumpsRemaining = $this->maxJumps;
 *     }
 * }
 * ```
 *
 * A `private` property declared on a base class is not shown or saved, even
 * with this attribute, because PHP does not give it to the subclass. Make it
 * `protected` instead.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SerializeField
{
}
