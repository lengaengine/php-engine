<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Attributes\Range;
use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\NativeEngine;
use function is_array;

/**
 * Plays audio from a GameObject.
 *
 * Attach an AudioSource to an object, assign an AudioClip in the Inspector or
 * through script, then call play() when the sound should be heard.
 */
final class AudioSource extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'AudioSource');
    }

    /**
     * The audio clip currently assigned to this source.
     *
     * Set this to one of your serialized AudioClip references before calling
     * play() when a source needs to choose sounds dynamically.
     */
    public ?AudioClip $clip {
        get {
            return AudioClip::fromSerializedReference($this->getState()['clip'] ?? null);
        }

        set(?AudioClip $value) {
            NativeEngine::call('audio_source_set_clip', $this->componentId, $value?->__serialize());
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
            NativeEngine::call('audio_source_set_play_on_awake', $this->componentId, $value);
        }
    }

    /**
     * Whether playback should restart automatically after reaching the end.
     */
    public bool $loop {
        get {
            return (bool) ($this->getState()['loop'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('audio_source_set_loop', $this->componentId, $value);
        }
    }

    /**
     * Playback volume from 0.0 to 1.0.
     */
    #[Range(0, 1)]
    public float $volume {
        get {
            return (float) ($this->getState()['volume'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('audio_source_set_volume', $this->componentId, $value);
        }
    }

    /**
     * Playback pitch multiplier. Values above 1.0 play faster and higher.
     */
    #[Range(0.01, 3)]
    public float $pitch {
        get {
            return (float) ($this->getState()['pitch'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('audio_source_set_pitch', $this->componentId, $value);
        }
    }

    /**
     * Returns true while the assigned clip is currently playing.
     */
    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    /**
     * Starts playback from the beginning of the assigned clip.
     */
    public function play(): bool
    {
        return (bool) NativeEngine::call('audio_source_play', $this->componentId);
    }

    /**
     * Stops playback and resets the source.
     */
    public function stop(): bool
    {
        return (bool) NativeEngine::call('audio_source_stop', $this->componentId);
    }

    /**
     * @return array{
     *     clip?: array{__lengaAssetKind: string, assetPath: string}|null,
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
         *     clip?: array{__lengaAssetKind: string, assetPath: string}|null,
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
