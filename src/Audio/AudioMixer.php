<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Core\NativeEngine;
use function basename;
use function is_array;
use function is_string;
use function pathinfo;
use const PATHINFO_FILENAME;

/**
 * A reference to an audio mixer asset, which balances groups of sounds through named channels.
 *
 * A mixer has a Master channel and the channels you add, such as Music, SFX,
 * UI and Ambience. Route an {@see AudioSource} into a channel to control its
 * level together with the other sounds in that group. Expose an `AudioMixer`
 * field on a Behaviour and assign the mixer in the Inspector:
 *
 * ```php
 * use Lenga\Engine\Audio\AudioMixer;
 * use Lenga\Engine\Audio\AudioSource;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class LevelAudio extends Behaviour
 * {
 *     public ?AudioMixer $mainMixer = null;
 *
 *     public function start(): void
 *     {
 *         $source = $this->gameObject->getComponent(AudioSource::class);
 *         $source?->setOutput($this->mainMixer, 'channel-1');
 *     }
 * }
 * ```
 *
 * When the routing never changes, set the source's Mixer and Output Channel
 * in the Inspector instead.
 */
final class AudioMixer
{
    private const string REFERENCE_KIND_KEY = '__lengaAssetKind';
    private const string REFERENCE_KIND = 'AudioMixer';

    private string $assetPathValue;

    private function __construct(string $assetPath)
    {
        $this->assetPathValue = $assetPath;
    }

    /**
     * The mixer's name: its file name without the folder or extension.
     */
    public string $name {
        get {
            $name = pathinfo($this->assetPathValue, PATHINFO_FILENAME);

            return is_string($name) && $name !== '' ? $name : basename($this->assetPathValue);
        }
    }

    /**
     * The path of the mixer asset, relative to the project folder.
     */
    public string $assetPath {
        get {
            return $this->assetPathValue;
        }
    }

    /**
     * @return array{__lengaAssetKind: string, assetPath: string}
     */
    public function __serialize(): array
    {
        return [
            self::REFERENCE_KIND_KEY => self::REFERENCE_KIND,
            'assetPath' => $this->assetPathValue,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->assetPathValue = self::readAssetPath($data);
    }

    /**
     * Creates a mixer reference from saved scene or Inspector data.
     *
     * @internal Lenga calls this when it loads mixer fields and AudioSource
     *     outputs. Scripts get mixers from `AudioMixer` fields set in the Inspector.
     *
     * @param mixed $value A mixer, a project-relative asset path, or saved mixer data.
     * @return self|null The mixer, or null when the value names no mixer.
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
     * Checks whether two mixer references point to the same mixer asset.
     *
     * Use it to compare mixers: `===` is false for two separate references to
     * the same asset.
     *
     * @param AudioMixer|null $other The mixer to compare with. Null never matches.
     */
    public function matches(?self $other): bool
    {
        return $other !== null && $this->assetPathValue === $other->assetPath;
    }

    /**
     * Moves this mixer to a saved preset over the given time.
     *
     * Channel volume, pitch and pan blend over the transition time. The cutoff
     * of a low-pass or high-pass effect sweeps, linearly in Hz, when the effect
     * is enabled with a mix above zero both before and after the transition and
     * keeps its channel, effect ID and type. Every other effect setting, the
     * equalizer, mute, solo, effect bypass and channel output switch to the
     * preset's values when the transition starts.
     *
     * The transition runs in real time, so it continues while gameplay is
     * paused with Time::pauseGameplay(). A transition time of zero applies the
     * preset at once.
     *
     * @param string $targetPreset The name or id of a preset saved on the referenced AudioMixer asset.
     * @param float $transitionTimeInSeconds How long the blend and sweep take. Zero applies the preset at once.
     * @return bool False when the mixer asset or the preset cannot be found.
     */
    public function transitionToPreset(string $targetPreset, float $transitionTimeInSeconds = 0.0): bool
    {
        return (bool) NativeEngine::call(
            'audio_mixer_transition_to_preset',
            $this->__serialize(),
            $targetPreset,
            $transitionTimeInSeconds,
        );
    }

    /**
     * Scales the volume of every sound routed through a channel of this mixer.
     *
     * Use `0` for silence and `1` for the level saved on the mixer. Finite
     * values outside that range are clamped. The gain stays in place when the
     * mixer moves to another preset, until the game stops.
     *
     * @param string $channelId Channel ID saved on the mixer asset.
     * @param float $gain Linear volume multiplier.
     * @return bool False if the mixer or channel cannot be found or the gain is not finite.
     */
    public function setChannelGain(string $channelId, float $gain): bool
    {
        return (bool) NativeEngine::call('audio_mixer_set_channel_gain', $this->__serialize(), $channelId, $gain);
    }

    private static function readAssetPath(mixed $value): string
    {
        if ($value instanceof self) {
            return $value->assetPathValue;
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

        foreach (['assetPath', 'path', 'mixerPath'] as $key) {
            if (isset($value[$key]) && is_string($value[$key])) {
                return $value[$key];
            }
        }

        return '';
    }
}
