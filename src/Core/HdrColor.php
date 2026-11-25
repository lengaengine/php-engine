<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

/**
 * A colour that can be brighter than white, as Unity's HDR colour fields hold one.
 *
 * It is an authored colour, 8-bit sRGB with alpha like every other colour,
 * and an intensity in exposure stops. Each stop doubles the colour: an
 * intensity of 1 is twice the authored colour and -1 is half of it. The value
 * is immutable; create a new HdrColor to change it.
 */
final readonly class HdrColor
{
    /**
     * The intensity at which an HDR colour is its authored colour.
     */
    public const float NEUTRAL_INTENSITY = 0.0;

    private const float SRGB_ENCODED_SEGMENT_END = 0.04045;
    private const float SRGB_SEGMENT_SLOPE = 12.92;
    private const float SRGB_CURVE_OFFSET = 0.055;
    private const float SRGB_CURVE_EXPONENT = 2.4;

    /**
     * Creates an HDR colour from an authored colour and an intensity in stops.
     *
     * @param Color $color The authored sRGB colour. Its alpha is kept but is not part of the HDR value.
     * @param float $intensity Exposure stops the colour is raised by. 0 leaves it as authored.
     */
    public function __construct(
        public Color $color = new Color(1.0, 1.0, 1.0, 1.0),
        public float $intensity = self::NEUTRAL_INTENSITY,
    ) {
    }

    /**
     * Returns the factor the authored colour is multiplied by: 2 to the power of the intensity.
     */
    public function intensityScale(): float
    {
        return 2.0 ** $this->intensity;
    }

    /**
     * Returns the colour decoded from sRGB to linear and multiplied by {@see intensityScale()}.
     *
     * This is the value the engine computes for the colour. Alpha is not part of it.
     *
     * @return Vector3 Linear red, green and blue in x, y and z. Channels can exceed 1.
     */
    public function linearValue(): Vector3
    {
        $channels = $this->color->toRGBA();
        $scale = $this->intensityScale();

        return new Vector3(
            self::srgbToLinear($channels['r'] / Color::MAX_BYTE_VALUE) * $scale,
            self::srgbToLinear($channels['g'] / Color::MAX_BYTE_VALUE) * $scale,
            self::srgbToLinear($channels['b'] / Color::MAX_BYTE_VALUE) * $scale,
        );
    }

    /**
     * Reads an HDR colour as a material file stores it.
     *
     * An object `{"color": "#RRGGBBAA", "intensity": stops}` gives its colour
     * and intensity. A plain hex string is the colour at the neutral intensity.
     * A missing or mistyped part keeps the fallback's value, and any other
     * value gives the fallback.
     */
    public static function fromAssetValue(mixed $value, self $fallback = new self()): self
    {
        if (is_string($value)) {
            return new self(Color::tryFromHex($value) ?? $fallback->color, self::NEUTRAL_INTENSITY);
        }

        if (!is_array($value)) {
            return $fallback;
        }

        $color = is_string($value['color'] ?? null) ? Color::tryFromHex($value['color']) : null;
        $intensity = $value['intensity'] ?? null;

        return new self(
            $color ?? $fallback->color,
            is_int($intensity) || is_float($intensity) ? (float) $intensity : $fallback->intensity,
        );
    }

    /**
     * Returns the HDR colour as a material file stores it.
     *
     * @return array{color: string, intensity: float}
     */
    public function toAssetValue(): array
    {
        return [
            'color' => $this->color->toHex(),
            'intensity' => $this->intensity,
        ];
    }

    /**
     * Decodes one sRGB-encoded channel in 0..1 to linear (IEC 61966-2-1).
     */
    private static function srgbToLinear(float $encoded): float
    {
        if ($encoded <= self::SRGB_ENCODED_SEGMENT_END) {
            return $encoded / self::SRGB_SEGMENT_SLOPE;
        }

        return (($encoded + self::SRGB_CURVE_OFFSET) / (1.0 + self::SRGB_CURVE_OFFSET)) ** self::SRGB_CURVE_EXPONENT;
    }
}
