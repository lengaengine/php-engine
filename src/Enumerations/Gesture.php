<?php

namespace Lenga\Engine\Enumerations;

enum Gesture: int
{
    case NONE        = 0;        // No gesture
    case TAP         = 1;        // Tap gesture
    case DOUBLETAP   = 2;        // Double tap gesture
    case HOLD        = 4;        // Hold gesture
    case DRAG        = 8;        // Drag gesture
    case SWIPE_RIGHT = 16;       // Swipe right gesture
    case SWIPE_LEFT  = 32;       // Swipe left gesture
    case SWIPE_UP    = 64;       // Swipe up gesture
    case SWIPE_DOWN  = 128;      // Swipe down gesture
    case PINCH_IN    = 256;      // Pinch in gesture
    case PINCH_OUT   = 512;       // Pinch out gesture
}
