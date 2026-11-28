<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio\Enumeration;

/**
 * Names ways of presenting spatial audio to the player.
 *
 * No member of the scripting API takes or returns this enum, so choosing a
 * case has no effect.
 */
enum AudioSpatialExperience
{
    /** Spatial processing is turned off. */
    case Bypassed;

    /** Spatial audio follows the movement of the player's head. */
    case HeadTracked;

    /** Spatial audio stays fixed relative to the player's head. */
    case Fixed;
}
