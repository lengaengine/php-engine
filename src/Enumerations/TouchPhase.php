<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Describes how a touch point changed during the current frame.
 */
enum TouchPhase: string
{
    case BEGAN = 'Began';
    case MOVED = 'Moved';
    case STATIONARY = 'Stationary';
    case ENDED = 'Ended';
    case CANCELED = 'Canceled';
}
