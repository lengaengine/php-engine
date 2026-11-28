<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * A gamepad button, named by where it sits on the controller.
 *
 * The left face buttons are the D-pad. The right face buttons are the four
 * action buttons: A, B, X and Y on Xbox controllers. Pass a button to
 * {@see \Lenga\Engine\Core\Input::isGamepadButtonPressed()} and the other
 * gamepad button methods with the gamepad's number:
 *
 * ```php
 * if (Input::isGamepadButtonPressed(0, GamepadButton::RIGHT_FACE_DOWN)) {
 *     // Jump.
 * }
 * ```
 */
enum GamepadButton: int
{
    /** No button. {@see \Lenga\Engine\Core\Input::getGamepadButtonPressed()} returns its value when no button is pressed. */
    case UNKNOWN = 0;

    /** D-pad up. */
    case LEFT_FACE_UP = 1;

    /** D-pad right. */
    case LEFT_FACE_RIGHT = 2;

    /** D-pad down. */
    case LEFT_FACE_DOWN = 3;

    /** D-pad left. */
    case LEFT_FACE_LEFT = 4;

    /** The top action button: Y on Xbox controllers, Triangle on PlayStation controllers. */
    case RIGHT_FACE_UP = 5;

    /** The right action button: B on Xbox controllers, Circle on PlayStation controllers. */
    case RIGHT_FACE_RIGHT = 6;

    /** The bottom action button: A on Xbox controllers, Cross on PlayStation controllers. */
    case RIGHT_FACE_DOWN = 7;

    /** The left action button: X on Xbox controllers, Square on PlayStation controllers. */
    case RIGHT_FACE_LEFT = 8;

    /** The left bumper: LB on Xbox controllers, L1 on PlayStation controllers. */
    case LEFT_TRIGGER_1 = 9;

    /** The left trigger pressed as a button: LT on Xbox controllers, L2 on PlayStation controllers. */
    case LEFT_TRIGGER_2 = 10;

    /** The right bumper: RB on Xbox controllers, R1 on PlayStation controllers. */
    case RIGHT_TRIGGER_1 = 11;

    /** The right trigger pressed as a button: RT on Xbox controllers, R2 on PlayStation controllers. */
    case RIGHT_TRIGGER_2 = 12;

    /** The left centre button: View or Back on Xbox controllers, Share or Select on PlayStation controllers. */
    case MIDDLE_LEFT = 13;

    /** The centre button: the Xbox button or the PS button. */
    case MIDDLE = 14;

    /** The right centre button: Menu or Start on Xbox controllers, Options or Start on PlayStation controllers. */
    case MIDDLE_RIGHT = 15;

    /** Pressing the left stick in (L3). */
    case LEFT_THUMB = 16;

    /** Pressing the right stick in (R3). */
    case RIGHT_THUMB = 17;
}
