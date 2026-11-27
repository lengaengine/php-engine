<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Attributes\Range;
use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\NativeEngine;
use function is_array;

/**
 * Plays an audio clip from a GameObject.
 *
 * Add an Audio Source to a GameObject, assign its clip in the Inspector or
 * from a script, then call {@see AudioSource::play()} when the sound should be
 * heard. Turn on {@see AudioSource::$playOnAwake} and {@see AudioSource::$loop}
 * for music and ambience that should start with the scene and repeat.
 *
 * ```php
 * use Lenga\Engine\Audio\AudioSource;
 * use Lenga\Engine\Core\Behaviour;
 * use Lenga\Engine\Core\Input;
 * use Lenga\Engine\Enumerations\KeyCode;
 *
 * final class JumpSound extends Behaviour
 * {
 *     private ?AudioSource $audio = null;
 *
 *     public function start(): void
 *     {
 *         $this->audio = $this->gameObject->getComponent(AudioSource::class);
 *     }
 *
 *     public function update(): void
 *     {
 *         if (Input::getKeyDown(KeyCode::SPACE)) {
 *             $this->audio?->play();
 *         }
 *     }
 * }
 * ```
 *
 * Route the source through an {@see AudioMixer} with
 * {@see AudioSource::setOutput()} to balance groups of sounds such as music
 * and effects together.
 */
final class AudioSource extends Component
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'AudioSource');
    }

    /**
     * The audio clip this source plays, or null when none is assigned.
     *
     * To choose between several sounds from a script, expose {@see AudioClip}
     * fields on your Behaviour, assign them in the Inspector, then set this
     * property to one of them before calling {@see AudioSource::play()}.
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
     * The audio mixer this source plays through, or null to play through the default output.
     *
     * Setting it keeps the current {@see AudioSource::$outputChannelId}. To
     * change the mixer and the channel together, call
     * {@see AudioSource::setOutput()}.
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
     * The ID of the mixer channel this source plays through.
     *
     * Setting it keeps the current {@see AudioSource::$outputAudioMixer}.
     * Defaults to `master`.
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
     * Whether the source starts playing on its own when the scene starts.
     *
     * Turn it on for background music and ambience. Leave it off for sounds
     * your scripts start with {@see AudioSource::play()}. Defaults to `false`.
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
     * Whether the clip starts again from the beginning each time it ends.
     *
     * Defaults to `false`.
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
     * How loud this source plays, from `0.0` for silence to `1.0` for the clip's full volume.
     *
     * Values outside that range are clamped. Defaults to `1.0`.
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
     * How fast and how high the clip plays, as a multiplier of its original pitch.
     *
     * `1.0` plays the clip as recorded. Higher values play faster and higher,
     * lower values slower and lower. Values below `0.01` are raised to `0.01`.
     * Defaults to `1.0`.
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
     * Whether the source is playing its clip right now.
     *
     * False while the source is paused or stopped.
     */
    public bool $isPlaying {
        get {
            return (bool) ($this->getState()['isPlaying'] ?? false);
        }
    }

    /**
     * Returns whether the source is playing its clip right now.
     *
     * @deprecated Use the {@see AudioSource::$isPlaying} property instead.
     */
    public function isPlaying(): bool
    {
        return $this->isPlaying;
    }

    /**
     * Whether playback on this source is paused.
     */
    public bool $isPaused {
        get {
            return (bool) ($this->getState()['isPaused'] ?? false);
        }
    }

    /**
     * Whether the source is neither playing nor paused.
     */
    public bool $isStopped {
        get {
            return (!$this->isPlaying && !$this->isPaused);
        }
    }

    /**
     * Starts the assigned clip from the beginning.
     *
     * A clip that is already playing restarts.
     *
     * @return bool False when the source is disabled or its clip cannot be played, for example when no clip is assigned.
     */
    public function play(): bool
    {
        return (bool) NativeEngine::call('audio_source_play', $this->componentId);
    }

    /**
     * Pauses playback and keeps the clip position, so {@see AudioSource::resume()} continues from there.
     *
     * @return bool True when the source is now paused, including when it was paused already. False when nothing was playing.
     */
    public function pause(): bool
    {
        return (bool) NativeEngine::call('audio_source_pause', $this->componentId);
    }

    /**
     * Continues playback from where {@see AudioSource::pause()} stopped it.
     *
     * @return bool False when the clip could not be loaded. True otherwise, including when the source was not paused.
     */
    public function resume(): bool
    {
        return (bool) NativeEngine::call('audio_source_resume', $this->componentId);
    }

    /**
     * Stops playback and moves the clip back to the start.
     *
     * The next {@see AudioSource::play()} starts from the beginning.
     *
     * @return bool False when the component no longer exists.
     */
    public function stop(): bool
    {
        return (bool) NativeEngine::call('audio_source_stop', $this->componentId);
    }

    /**
     * Sends this source through a mixer channel, setting the mixer and the channel together.
     *
     * ```php
     * $this->source?->setOutput($this->mainMixer, 'channel-1');
     * ```
     *
     * @param AudioMixer|null $mixer The mixer to play through, or null for the default output.
     * @param string $channelId ID of a channel saved on the mixer. An empty string uses `master`. Defaults to `master`.
     * @return bool False when the component no longer exists.
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
