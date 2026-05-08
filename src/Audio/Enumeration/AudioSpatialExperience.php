<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio\Enumeration;

/**
 * Describes how spatial audio should be interpreted by the runtime.
 */
enum AudioSpatialExperience
{
    case Bypassed;
    case HeadTracked;
    case Fixed;
}
