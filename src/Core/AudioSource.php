<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Range;

final class AudioSource extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'AudioSource');
    }

    /**
     * @var string The path to the audio clip.
     */
    public string $clipPath {
        get {
            return (string) ($this->getState()['clipPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_audio_source_set_clip_path($this->componentId, $value);
        }
    }

    /**
     * @var bool When enabled the AudioSource will begin to play as soon as the
     * component/GameObject becomes active.
     */
    public bool $playOnAwake {
        get {
            return (bool) ($this->getState()['playOnAwake'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_audio_source_set_play_on_awake($this->componentId, $value);
        }
    }

    public bool $loop {
        get {
            return (bool) ($this->getState()['loop'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_audio_source_set_loop($this->componentId, $value);
        }
    }

    #[Range(0, 1)]
    public float $volume {
        get {
            return (float) ($this->getState()['volume'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_audio_source_set_volume($this->componentId, $value);
        }
    }

    #[Range(0.01, 3)]
    public float $pitch {
        get {
            return (float) ($this->getState()['pitch'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_audio_source_set_pitch($this->componentId, $value);
        }
    }

    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    public function play(): bool
    {
        return \lenga_internal_audio_source_play($this->componentId);
    }

    public function stop(): bool
    {
        return \lenga_internal_audio_source_stop($this->componentId);
    }

    /**
     * @return array{
     *     clipPath?: string,
     *     playOnAwake?: bool,
     *     loop?: bool,
     *     volume?: float,
     *     pitch?: float,
     *     isPlaying?: bool,
     *     enabled?: bool
     * }
     */
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
        $state = \lenga_internal_audio_source_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
