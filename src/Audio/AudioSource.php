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
     * The mixer asset that receives this source's output.
     *
     * Leave this null to send the source directly to the engine's default
     * master output.
     */
    public ?AudioMixer $outputAudioMixer {
        get {
            return AudioMixer::fromSerializedReference($this->getState()['outputAudioMixer'] ?? null);
        }

        set(?AudioMixer $value) {
            NativeEngine::call(
                'audio_source_set_output',
                $this->componentId,
                $value?->__serialize(),
                $this->outputChannelId,
            );
        }
    }

    /**
     * The mixer channel that receives this source's output.
     */
    public string $outputChannelId {
        get {
            return (string) ($this->getState()['outputChannelId'] ?? 'master');
        }

        set(string $value) {
            NativeEngine::call(
                'audio_source_set_output',
                $this->componentId,
                $this->outputAudioMixer?->__serialize(),
                $value,
            );
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
     * Returns true when playback is paused on this source.
     */
    public bool $isPaused {
        get {
            return (bool) ($this->getState()['isPaused'] ?? false);
        }
    }

    /**
     * Starts playback from the beginning of the assigned clip.
     */
    public function play(): bool
    {
        return (bool) NativeEngine::call('audio_source_play', $this->componentId);
    }

    /**
     * Pauses playback without resetting the clip position.
     */
    public function pause(): bool
    {
        return (bool) NativeEngine::call('audio_source_pause', $this->componentId);
    }

    /**
     * Resumes playback from the paused clip position.
     */
    public function resume(): bool
    {
        return (bool) NativeEngine::call('audio_source_resume', $this->componentId);
    }

    /**
     * Stops playback and resets the source.
     */
    public function stop(): bool
    {
        return (bool) NativeEngine::call('audio_source_stop', $this->componentId);
    }

    /**
     * Routes this source to a mixer channel in one operation.
     */
    public function setOutput(?AudioMixer $mixer, string $channelId = 'master'): bool
    {
        return (bool) NativeEngine::call(
            'audio_source_set_output',
            $this->componentId,
            $mixer?->__serialize(),
            $channelId,
        );
    }

    /**
     * @return array{
     *     clip?: array{__lengaAssetKind: string, assetPath: string}|null,
     *     outputAudioMixer?: array{__lengaAssetKind: string, assetPath: string}|null,
     *     outputChannelId?: string,
     *     playOnAwake?: bool,
     *     loop?: bool,
     *     volume?: float,
     *     pitch?: float,
     *     isPlaying?: bool,
     *     isPaused?: bool,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     clip?: array{__lengaAssetKind: string, assetPath: string}|null,
         *     outputAudioMixer?: array{__lengaAssetKind: string, assetPath: string}|null,
         *     outputChannelId?: string,
         *     playOnAwake?: bool,
         *     loop?: bool,
         *     volume?: float,
         *     pitch?: float,
         *     isPlaying?: bool,
         *     isPaused?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('audio_source_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
