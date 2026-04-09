<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

enum CollisionFlags: int
{
    case None = 0;
    case Sides = 1;
    case Above = 2;
    case Below = 4;
}
