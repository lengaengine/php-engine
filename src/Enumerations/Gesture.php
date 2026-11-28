<?php

namespace Lenga\Engine\Enumerations;

/**
 * A touch gesture such as a tap, a swipe or a pinch.
 *
 * The values are bit flags, so several gestures can be combined into one `int`.
 * No member of the scripting API takes or returns a Gesture.
 */
enum Gesture: int
{
    /** No gesture. */
    case NONE = 0;

    /** A single tap. */
    case TAP = 1;

    /** Two taps in quick succession. */
    case DOUBLETAP = 2;

    /** A finger held down in one place. */
    case HOLD = 4;

    /** A finger moved while it touches the screen. */
    case DRAG = 8;

    /** A quick swipe to the right. */
    case SWIPE_RIGHT = 16;

    /** A quick swipe to the left. */
    case SWIPE_LEFT = 32;

    /** A quick swipe upwards. */
    case SWIPE_UP = 64;

    /** A quick swipe downwards. */
    case SWIPE_DOWN = 128;

    /** Two fingers moving towards each other. */
    case PINCH_IN = 256;

    /** Two fingers moving apart. */
    case PINCH_OUT = 512;
}
