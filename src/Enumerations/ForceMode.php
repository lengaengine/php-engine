<?php

namespace Lenga\Engine\Enumerations;

/**
 * The ForceMode enumeration defines the mode in which a force is applied to an object in a physics simulation.
 */
enum ForceMode: int
{
    case Force = 0;
    case Acceleration = 1;
    case Impulse = 2;
    case VelocityChange = 3;
}
