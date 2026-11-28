<?php

namespace Lenga\Engine\Enumerations;

/**
 * A shape for the mouse pointer.
 *
 * No member of the scripting API takes or returns a MouseCursor, so scripts
 * cannot change the pointer shape with it.
 */
enum MouseCursor: int
{
    /** The system's default pointer. */
    case DEFAULT = 0;

    /** An arrow. */
    case ARROW = 1;

    /** The text cursor shown over editable text. */
    case IBEAM = 2;

    /** A cross. */
    case CROSSHAIR = 3;

    /** A pointing hand, as shown over links. */
    case POINTING_HAND = 4;

    /** A left and right resize arrow. */
    case RESIZE_EW = 5;

    /** An up and down resize arrow. */
    case RESIZE_NS = 6;

    /** A diagonal resize arrow from the top left to the bottom right. */
    case RESIZE_NWSE = 7;

    /** A diagonal resize arrow from the top right to the bottom left. */
    case RESIZE_NESW = 8;

    /** A four-way arrow for moving or resizing in any direction. */
    case RESIZE_ALL = 9;

    /** A sign that the action is not allowed. */
    case NOT_ALLOWED = 10;
}