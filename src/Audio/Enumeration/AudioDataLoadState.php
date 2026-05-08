<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio\Enumeration;

/**
 * Describes whether an audio clip's sample data is available to the runtime.
 */
enum AudioDataLoadState: int
{
    case Unloaded = 0;
    case Loading = 1;
    case Loaded = 2;
    case Failed = 3;
}
