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
 * A reference to an imported audio asset.
 *
 * Audio clips are assigned in the Inspector and can be passed to an AudioSource
 * at runtime. The engine owns the underlying audio data; this object is the
 * script-facing handle for that asset.
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
     * The display name of the clip, derived from the assigned asset.
     */
    public string $name {
        get {
            $name = pathinfo($this->assetPath, PATHINFO_FILENAME);

            return is_string($name) && $name !== '' ? $name : basename($this->assetPath);
        }
    }

    /**
     * Whether the clip contains ambisonic audio data.
     */
    public bool $ambisonic {
        get {
            return (bool) ($this->getState()['ambisonic'] ?? false);
        }
    }

    /**
     * The number of audio channels in the clip.
     */
    public int $channels {
        get {
            return (int) ($this->getState()['channels'] ?? 0);
        }
    }

    /**
     * The clip sample rate in hertz.
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
     * Whether this clip may load its audio data asynchronously.
     */
    public bool $loadInBackground {
        get {
            return (bool) ($this->getState()['loadInBackground'] ?? false);
        }
    }

    /**
     * The current loading state for this clip's audio data.
     */
    public AudioDataLoadState $loadState {
        get {
            return AudioDataLoadState::tryFrom($this->getState()['loadState'] ?? 0) ?? AudioDataLoadState::Unloaded;
        }
    }

    /**
     * The number of sample frames in the clip.
     */
    public int $samples {
        get {
            return (int) ($this->getState()['samples'] ?? 0);
        }
    }

    /**
     * Reads sample data from the audio clip starting at the given sample offset.
     *
     * Returned sample data is interleaved by channel (for example: L, R, L, R for
     * stereo) and normalized to the range of -1.0 to 1.0.
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
     * Rehydrates an audio clip reference from scene or inspector data.
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
     * Returns true when both handles point to the same imported audio asset.
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
