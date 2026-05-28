<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

enum GamepadAxis: int
{
    case LEFT_X = 0;
    case LEFT_Y = 1;
    case RIGHT_X = 2;
    case RIGHT_Y = 3;
    case LEFT_TRIGGER = 4;
    case RIGHT_TRIGGER = 5;
}
