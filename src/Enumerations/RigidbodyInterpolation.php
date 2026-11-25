<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Controls how a rigid body smooths its Transform pose between fixed physics steps.
 */
enum RigidbodyInterpolation: string
{
    /** No smoothing; the Transform always reflects the latest fixed physics step. */
    case None = 'None';

    /** Smooths the Transform between the previous and current fixed physics step poses. */
    case Interpolate = 'Interpolate';

    /** Projects the Transform forward from the current fixed physics step using body velocity. */
    case Extrapolate = 'Extrapolate';
}
