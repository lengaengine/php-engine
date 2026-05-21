<?php

namespace Lenga\Engine\Enumerations;

enum MouseCursor: int
{
    case DEFAULT       = 0;     // Default pointer shape
    case ARROW         = 1;     // Arrow shape
    case IBEAM         = 2;     // Text writing cursor shape
    case CROSSHAIR     = 3;     // Cross shape
    case POINTING_HAND = 4;     // Pointing hand cursor
    case RESIZE_EW     = 5;     // Horizontal resize/move arrow shape
    case RESIZE_NS     = 6;     // Vertical resize/move arrow shape
    case RESIZE_NWSE   = 7;     // Top-left to bottom-right diagonal resize/move arrow shape
    case RESIZE_NESW   = 8;     // The top-right to bottom-left diagonal resize/move arrow shape
    case RESIZE_ALL    = 9;     // The omnidirectional resize/move cursor shape
    case NOT_ALLOWED   = 10;    // The operation-not-allowed shape
}