<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Describes how a touch point changed during the current frame.
 */
enum TouchPhase: string
{
    /** The finger touched the screen this frame. */
    case BEGAN = 'Began';

    /** The finger moved since the previous frame. */
    case MOVED = 'Moved';

    /** The finger is still touching the screen and has not moved since the previous frame. */
    case STATIONARY = 'Stationary';

    /** The finger left the screen this frame. */
    case ENDED = 'Ended';

    /** The system stopped tracking the touch. Lenga does not report this phase; a lifted finger is reported as `ENDED`. */
    case CANCELED = 'Canceled';
}
