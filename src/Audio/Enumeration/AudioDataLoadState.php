<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio\Enumeration;

/**
 * Whether an audio clip's sample data is ready to play and read.
 *
 * Read it from {@see \Lenga\Engine\Audio\AudioClip::$loadState}.
 */
enum AudioDataLoadState: int
{
    /** The clip's data is not loaded. */
    case Unloaded = 0;

    /** The clip's data is loading. */
    case Loading = 1;

    /** The clip's data is loaded and ready. */
    case Loaded = 2;

    /** The clip's data could not be loaded, for example because the file is missing or unreadable. */
    case Failed = 3;
}
