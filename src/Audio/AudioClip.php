<?php

namespace Lenga\Engine\Audio;

use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\NativeEngine;
use function is_array;

final class AudioClip extends Component
{
    private(set) bool $ambisonic {
        get {
            return $this->ambisonic;
        }
    }

    private function getState(): array
    {
        /** @var array{
         *     clipPath?: string,
         *     playOnAwake?: bool,
         *     loop?: bool,
         *     volume?: float,
         *     pitch?: float,
         *     isPlaying?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('audio_source_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}