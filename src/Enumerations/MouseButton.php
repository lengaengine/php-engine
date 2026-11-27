<?php

namespace Lenga\Engine\Enumerations;

/**
 * A mouse button.
 *
 * Pass it to {@see \Lenga\Engine\Core\Input::getMouseButton()},
 * {@see \Lenga\Engine\Core\Input::getMouseButtonDown()} and
 * {@see \Lenga\Engine\Core\Input::getMouseButtonUp()}:
 *
 * ```php
 * if (Input::getMouseButtonDown(MouseButton::LEFT)) {
 *     // Fire.
 * }
 * ```
 */
enum MouseButton: int
{
    /** The left mouse button. */
    case LEFT = 0;

    /** The right mouse button. */
    case RIGHT = 1;

    /** The middle mouse button, usually pressing the scroll wheel. */
    case MIDDLE = 2;

    /** The side button on mice that have one. */
    case SIDE = 3;

    /** The extra button on mice that have one. */
    case EXTRA = 4;

    /** The forward button on mice that have one. */
    case FORWARD = 5;

    /** The back button on mice that have one. */
    case BACK = 6;
}
