<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Core\NativeEngine;
use function is_array;

/**
 * A holder for the game's audio configuration.
 *
 * It has no public members, so scripts cannot read or change audio settings
 * through it.
 */
final class AudioSettings
{
    private function __construct()
    {
    }

    /**
     * @return array{speakerMode?: int}
     */
    private static function getState(): array
    {
        /** @var array{speakerMode?: int} $state */
        $state = NativeEngine::call('audio_settings_get_state');

        return is_array($state) ? $state : [];
    }
}
