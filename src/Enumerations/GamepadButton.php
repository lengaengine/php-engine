<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

enum GamepadButton: int
{
    case UNKNOWN = 0;
    case LEFT_FACE_UP = 1;
    case LEFT_FACE_RIGHT = 2;
    case LEFT_FACE_DOWN = 3;
    case LEFT_FACE_LEFT = 4;
    case RIGHT_FACE_UP = 5;
    case RIGHT_FACE_RIGHT = 6;
    case RIGHT_FACE_DOWN = 7;
    case RIGHT_FACE_LEFT = 8;
    case LEFT_TRIGGER_1 = 9;
    case LEFT_TRIGGER_2 = 10;
    case RIGHT_TRIGGER_1 = 11;
    case RIGHT_TRIGGER_2 = 12;
    case MIDDLE_LEFT = 13;
    case MIDDLE = 14;
    case MIDDLE_RIGHT = 15;
    case LEFT_THUMB = 16;
    case RIGHT_THUMB = 17;
}
