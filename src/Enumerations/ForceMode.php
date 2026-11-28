<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * How {@see \Lenga\Engine\Core\Rigidbody3D::addForce()} applies a vector to a 3D body.
 *
 * `Force` and `Acceleration` push the body over time; call them every
 * `fixedUpdate()` while the push lasts. `Impulse` and `VelocityChange` act once,
 * for jumps, hits and explosions. Bodies that are not dynamic do not respond to
 * `Acceleration` or `VelocityChange`.
 */
enum ForceMode: int
{
    /** A continuous push whose effect is divided by the body's mass, for effects such as wind or thrust. */
    case Force = 0;

    /** A continuous acceleration that ignores mass, so heavy and light bodies speed up equally. */
    case Acceleration = 1;

    /** An instant kick whose effect is divided by the body's mass. */
    case Impulse = 2;

    /** An instant change of velocity that ignores mass. */
    case VelocityChange = 3;
}
