<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\MathUtil;

/**
 * Represents the listener used to hear audio in a scene.
 */
final class AudioListener extends Component
{
    private const float MIN_VOLUME = 0.0;
    private const float MAX_VOLUME = 1.0;

    private static float $volume = 0.0;

    /**
     * Returns the global listener volume.
     */
    public static function getVolume(): float
    {
        return MathUtil::clamp(self::$volume, self::MIN_VOLUME, self::MAX_VOLUME);
    }

    /**
     * Sets the global listener volume, clamped between silent and full volume.
     */
    public static function setVolume(float $volume): void
    {
        self::$volume = MathUtil::clamp($volume, self::MIN_VOLUME, self::MAX_VOLUME);
    }
}
