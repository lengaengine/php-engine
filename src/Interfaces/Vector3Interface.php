<?php

declare(strict_types=1);

namespace Lenga\Engine\Interfaces;

/**
 * A value with `x`, `y` and `z` float components.
 *
 * {@see \Lenga\Engine\Core\Vector3} does not implement it, so use `Vector3`
 * as the type in your scripts.
 */
interface Vector3Interface
{
    /** The X component. */
    public float $x { get; set; }

    /** The Y component. */
    public float $y { get; set; }

    /** The Z component. */
    public float $z { get; set; }
}
