<?php

namespace Lenga\Engine\Enumerations;

enum MouseButton: int
{
    case LEFT      = 0;    // Mouse button left
    case RIGHT     = 1;    // Mouse button right
    case MIDDLE    = 2;    // Mouse button middle (pressed wheel)
    case SIDE      = 3;    // Mouse button side (advanced mouse device)
    case EXTRA     = 4;    // Mouse button extra (advanced mouse device)
    case FORWARD   = 5;    // Mouse button forward (advanced mouse device)
    case BACK      = 6;    // Mouse button back (advanced mouse device)
}
