<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Selects how scene colors are mapped to the range the display can show.
 *
 * Use it with {@see \Lenga\Engine\Rendering\TonemappingOverride}.
 */
enum TonemappingMode: string
{
    /** Leaves scene colors as they are. */
    case None = 'None';

    /** Compresses bright colors into the display range while keeping hue and contrast close to the original. */
    case Neutral = 'Neutral';

    /** Applies the ACES filmic curve, which adds contrast and rolls bright colors off softly. */
    case Aces = 'ACES';
}
