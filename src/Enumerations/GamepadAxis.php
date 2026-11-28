<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * An analogue control on a gamepad: one direction of a stick, or a trigger.
 *
 * Pass it to {@see \Lenga\Engine\Core\Input::getGamepadAxisMovement()} with the
 * gamepad's number:
 *
 * ```php
 * $horizontal = Input::getGamepadAxisMovement(0, GamepadAxis::LEFT_X);
 * ```
 */
enum GamepadAxis: int
{
    /** The left stick, left and right. */
    case LEFT_X = 0;

    /** The left stick, up and down. */
    case LEFT_Y = 1;

    /** The right stick, left and right. */
    case RIGHT_X = 2;

    /** The right stick, up and down. */
    case RIGHT_Y = 3;

    /** The left trigger: LT on Xbox controllers, L2 on PlayStation controllers. */
    case LEFT_TRIGGER = 4;

    /** The right trigger: RT on Xbox controllers, R2 on PlayStation controllers. */
    case RIGHT_TRIGGER = 5;
}
