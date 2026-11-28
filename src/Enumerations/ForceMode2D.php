<?php

namespace Lenga\Engine\Enumerations;

/**
 * How {@see \Lenga\Engine\Core\Rigidbody2D::addForce()} applies a vector to a 2D body.
 */
enum ForceMode2D: int
{
    /** A push that is combined with the other forces added during the step and applied in the next physics step. Call it every `fixedUpdate()` while the push lasts. */
    case Force = 0;

    /** An immediate change of velocity, for a single kick such as a jump. */
    case Impulse = 1;
}
