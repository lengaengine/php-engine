<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\MathUtil;

/**
 * Keeps a listener volume that scripts can set and read.
 *
 * Use {@see AudioListener::setVolume()} and {@see AudioListener::getVolume()}
 * to share one volume value between scripts. The engine has no audio
 * listener of its own, so this class and its volume do not change what the
 * player hears. To change loudness, set {@see AudioSource::$volume} or call
 * {@see AudioMixer::setChannelGain()}.
 */
final class AudioListener extends Component
{
    private const float MIN_VOLUME = 0.0;
    private const float MAX_VOLUME = 1.0;

    private static float $volume = 0.0;

    /**
     * Returns the listener volume set with {@see AudioListener::setVolume()}, from `0.0` to `1.0`.
     *
     * Defaults to `0.0`.
     */
    public static function getVolume(): float
    {
        return MathUtil::clamp(self::$volume, self::MIN_VOLUME, self::MAX_VOLUME);
    }

    /**
     * Stores the listener volume, clamped between `0.0` and `1.0`.
     *
     * The value is shared by all scripts and lasts until the game stops.
     *
     * @param float $volume The new volume. `0.0` is silent and `1.0` is full volume.
     */
    public static function setVolume(float $volume): void
    {
        self::$volume = MathUtil::clamp($volume, self::MIN_VOLUME, self::MAX_VOLUME);
    }
}
