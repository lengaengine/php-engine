<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/** Selects how scene colors are mapped to the display range. */
enum TonemappingMode: string
{
    case None = 'None';
    case Neutral = 'Neutral';
    case Aces = 'ACES';
}
