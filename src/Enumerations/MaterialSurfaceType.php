<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * The Surface Type of a material, as Unity URP names it.
 *
 * Opaque surfaces hide what is behind them. Transparent surfaces are combined
 * with what is behind them through the material's {@see MaterialBlendingMode}.
 * Each value is the name a material file stores.
 */
enum MaterialSurfaceType: string
{
    /** The surface hides what is behind it. */
    case Opaque = 'Opaque';

    /** The surface is combined with what is behind it through the material's {@see MaterialBlendingMode}. */
    case Transparent = 'Transparent';
}
