<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Bit flags that say which parts of a {@see \Lenga\Engine\Core\CharacterController} touched something during a move.
 *
 * {@see \Lenga\Engine\Core\CharacterController::move()} returns the flags as an
 * `int`, and {@see \Lenga\Engine\Core\CharacterController::$collisionFlags} keeps
 * the value from the last move. Several flags can be set at once, so test each
 * one with `&`:
 *
 * ```php
 * $flags = $controller->move($motion);
 * $grounded = ($flags & CollisionFlags::Below->value) !== 0;
 * ```
 */
enum CollisionFlags: int
{
    /** The controller touched nothing. */
    case None = 0;

    /** The controller's sides touched something, such as a wall. */
    case Sides = 1;

    /** The controller's top touched something, such as a ceiling. */
    case Above = 2;

    /** The controller's bottom touched something, such as the ground. */
    case Below = 4;
}
