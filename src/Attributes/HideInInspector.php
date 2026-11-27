<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Keeps a property out of the Inspector and out of saved scene data.
 *
 * Public properties of a Behaviour are shown in the Inspector and saved with
 * the scene. Add `#[HideInInspector]` to a public property that other scripts
 * need to reach but designers should not edit:
 *
 * ```php
 * use Lenga\Engine\Attributes\HideInInspector;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class Enemy extends Behaviour
 * {
 *     public int $maxHealth = 100;
 *
 *     #[HideInInspector]
 *     public int $currentHealth = 0;
 *
 *     public function start(): void
 *     {
 *         $this->currentHealth = $this->maxHealth;
 *     }
 * }
 * ```
 *
 * A hidden property keeps the value it gets from its default or from your
 * code.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class HideInInspector
{
}
