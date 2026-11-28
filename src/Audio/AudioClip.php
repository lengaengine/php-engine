<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Audio\Enumeration\AudioDataLoadState;
use Lenga\Engine\Core\NativeEngine;
use function basename;
use function is_array;
use function is_string;
use function pathinfo;
use const PATHINFO_FILENAME;

/**
 * A reference to an audio file in your project, such as a `.wav`, `.mp3` or `.ogg` file.
 *
 * Expose an `AudioClip` field on a Behaviour, assign the clip in the
 * Inspector, then hand it to an {@see AudioSource} to play it:
 *
 * ```php
 * use Lenga\Engine\Audio\AudioClip;
 * use Lenga\Engine\Audio\AudioSource;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class MenuSounds extends Behaviour
 * {
 *     public ?AudioClip $confirm = null;
 *
 *     private ?AudioSource $audio = null;
 *
 *     public function start(): void
 *     {
 *         $this->audio = $this->gameObject->getComponent(AudioSource::class);
 *     }
 *
 *     public function playConfirm(): void
 *     {
 *         if ($this->audio === null || $this->confirm === null) {
 *             return;
 *         }
 *
 *         $this->audio->clip = $this->confirm;
 *         $this->audio->play();
 *     }
 * }
 * ```
 *
 * The properties that describe the audio, such as {@see AudioClip::$length},
 * open and decode the audio file each time you read them. Read them once and
 * keep the values rather than reading them every frame. They are `0` or
 * `false` when the file is missing or cannot be decoded, and
 * {@see AudioClip::$loadState} is then {@see AudioDataLoadState::Failed}.
 */
final class AudioClip
{
    private const string REFERENCE_KIND_KEY = '__lengaAssetKind';
    private const string REFERENCE_KIND = 'AudioClip';

    private string $assetPath;

    private function __construct(string $assetPath)
    {
        $this->assetPath = $assetPath;
    }

    /**
     * The clip's name: its file name without the folder or extension.
     */
    public string $name {
        get {
            $name = pathinfo($this->assetPath, PATHINFO_FILENAME);

            return is_string($name) && $name !== '' ? $name : basename($this->assetPath);
        }
    }

    /**
     * Whether the clip holds ambisonic (full-sphere surround) audio.
     *
     * Lenga does not detect ambisonic files, so this is `false` for every clip.
     */
    public bool $ambisonic {
        get {
            return (bool) ($this->getState()['ambisonic'] ?? false);
        }
    }

    /**
     * The number of audio channels in the clip, such as `1` for mono or `2` for stereo.
     */
    public int $channels {
        get {
            return (int) ($this->getState()['channels'] ?? 0);
        }
    }

    /**
     * The clip's sample rate in hertz, such as `44100`.
     */
    public int $frequency {
        get {
            return (int) ($this->getState()['frequency'] ?? 0);
        }
    }

    /**
     * The clip duration in seconds.
     */
    public float $length {
        get {
            return (float) ($this->getState()['length'] ?? 0.0);
        }
    }

    /**
     * Whether the clip's audio data may load in the background while the game keeps running.
     *
     * Lenga has no background loading setting for clips, so this is `false`
     * for every clip.
     */
    public bool $loadInBackground {
        get {
            return (bool) ($this->getState()['loadInBackground'] ?? false);
        }
    }

    /**
     * Whether the clip's audio data is loaded, loading, unloaded or failed to load.
     *
     * {@see AudioDataLoadState::Loaded} when the file can be decoded and
     * {@see AudioDataLoadState::Failed} when it is missing or cannot be
     * decoded. Defaults to {@see AudioDataLoadState::Unloaded} when the clip
     * names no file.
     */
    public AudioDataLoadState $loadState {
        get {
            return AudioDataLoadState::tryFrom($this->getState()['loadState'] ?? 0) ?? AudioDataLoadState::Unloaded;
        }
    }

    /**
     * The number of sample frames in the clip, one frame per point in time across all channels.
     */
    public int $samples {
        get {
            return (int) ($this->getState()['samples'] ?? 0);
        }
    }

    /**
     * Reads the clip's sample values, starting at a sample offset.
     *
     * Use it to analyse audio from a script, for example to draw a waveform.
     * The values are interleaved by channel (left, right, left, right for
     * stereo) and range from -1.0 to 1.0.
     *
     * @param int $offsetSamples Zero-based start index in the clip sample buffer.
     * @param int|null $sampleCount Maximum number of interleaved samples to read, or null for the remainder.
     * @return list<float>|false Interleaved sample values, or false when unavailable.
     */
    public function getData(int $offsetSamples, ?int $sampleCount = null): array|false
    {
        $data = NativeEngine::call(
            'audio_clip_get_data',
            $this->__serialize(),
            $offsetSamples,
            $sampleCount,
        );

        return is_array($data) ? $data : false;
    }

    /**
     * @return array{__lengaAssetKind: string, assetPath: string}
     */
    public function __serialize(): array
    {
        return [
            self::REFERENCE_KIND_KEY => self::REFERENCE_KIND,
            'assetPath' => $this->assetPath,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->assetPath = self::readAssetPath($data);
    }

    /**
     * Creates a clip reference from saved scene or Inspector data.
     *
     * @internal Lenga calls this when it loads clip fields and AudioSource
     *     clips. Scripts get clips from `AudioClip` fields set in the Inspector.
     *
     * @param mixed $value A clip, a project-relative asset path, or saved clip data.
     * @return self|null The clip, or null when the value names no clip.
     */
    public static function fromSerializedReference(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        $assetPath = self::readAssetPath($value);

        return $assetPath === '' ? null : new self($assetPath);
    }

    /**
     * Checks whether two clip references point to the same audio file.
     *
     * Use it to compare clips: `===` is false for two separate references to
     * the same file.
     *
     * @param AudioClip|null $other The clip to compare with. Null never matches.
     */
    public function matches(?self $other): bool
    {
        return $other !== null && $this->assetPath === $other->assetPath;
    }

    /**
     * @return array{
     *     ambisonic?: bool,
     *     channels?: int,
     *     frequency?: int,
     *     length?: float,
     *     loadInBackground?: bool,
     *     loadState?: int,
     *     samples?: int
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     ambisonic?: bool,
         *     channels?: int,
         *     frequency?: int,
         *     length?: float,
         *     loadInBackground?: bool,
         *     loadState?: int,
         *     samples?: int
         * } $state
         */
        $state = NativeEngine::call('audio_clip_get_state', $this->__serialize());

        return is_array($state) ? $state : [];
    }

    private static function readAssetPath(mixed $value): string
    {
        if ($value instanceof self) {
            return $value->assetPath;
        }

        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        $kind = $value[self::REFERENCE_KIND_KEY] ?? null;
        if ($kind !== null && $kind !== self::REFERENCE_KIND) {
            return '';
        }

        foreach (['assetPath', 'path', 'clipPath'] as $key) {
            if (isset($value[$key]) && is_string($value[$key])) {
                return $value[$key];
            }
        }

        return '';
    }
}
