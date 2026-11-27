<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;
use Stringable;
use function abs;
use function fmod;
use function hexdec;
use function json_encode;
use function max;
use function min;
use function preg_match;
use function round;
use function sprintf;
use function strlen;
use function substr;

/**
 * A color with red, green, blue and alpha (opacity) channels, each from 0.0 to 1.0.
 *
 * Use colors for renderer tints, materials, lights and UI. Create one with
 * the constructor from 0.0 to 1.0 values, with {@see Color::fromRGBA()} from
 * 0 to 255 values, with {@see Color::fromHex()} from a `#RRGGBB` string, or
 * with a named color such as {@see Color::red()}. Values outside the range
 * are clamped.
 *
 * A Color cannot be changed after it is created. To change a color, create
 * a new one, for example with {@see Color::lerp()}.
 *
 * ```php
 * $renderer->color = Color::fromRGBA(35, 221, 246);
 * $faded = Color::lerp(Color::white(), Color::clear(), 0.5);
 * ```
 */
final class Color implements Stringable
{
    /** Smallest channel value, `0.0`. */
    public const float MIN_VALUE = 0.0;
    /** Largest channel value, `1.0`. */
    public const float MAX_VALUE = 1.0;
    /** Smallest channel value in 0 to 255 form, `0`. */
    public const int MIN_BYTE_VALUE = 0;
    /** Largest channel value in 0 to 255 form, `255`. */
    public const int MAX_BYTE_VALUE = 255;

    /** Red channel, from 0.0 to 1.0. Read-only. */
    public float $r {
        get => $this->red;
    }

    /** Green channel, from 0.0 to 1.0. Read-only. */
    public float $g {
        get => $this->green;
    }

    /** Blue channel, from 0.0 to 1.0. Read-only. */
    public float $b {
        get => $this->blue;
    }

    /** Alpha (opacity) channel, from 0.0 (transparent) to 1.0 (opaque). Read-only. */
    public float $a {
        get => $this->alpha;
    }

    private float $red;
    private float $green;
    private float $blue;
    private float $alpha;

    /**
     * Creates a color from channel values between 0.0 and 1.0.
     *
     * Values outside that range are clamped. With no arguments the color is
     * opaque black.
     *
     * @param float $r Red, from 0.0 to 1.0.
     * @param float $g Green, from 0.0 to 1.0.
     * @param float $b Blue, from 0.0 to 1.0.
     * @param float $a Alpha (opacity), from 0.0 (transparent) to 1.0 (opaque).
     */
    public function __construct(
        float $r = self::MIN_VALUE,
        float $g = self::MIN_VALUE,
        float $b = self::MIN_VALUE,
        float $a = self::MAX_VALUE,
    ) {
        $this->red = self::clampUnit($r);
        $this->green = self::clampUnit($g);
        $this->blue = self::clampUnit($b);
        $this->alpha = self::clampUnit($a);
    }

    private static function clampUnit(float $value): float
    {
        return MathUtil::clamp($value, self::MIN_VALUE, self::MAX_VALUE);
    }

    private static function normalizeHue(float $value): float
    {
        $hue = fmod($value, 360.0);

        return $hue < 0.0 ? $hue + 360.0 : $hue;
    }

    /**
     * Creates an opaque black color.
     */
    public static function black(): self
    {
        return new self(0.0, 0.0, 0.0, 1.0);
    }

    /**
     * Creates an opaque blue color.
     */
    public static function blue(): self
    {
        return new self(0.0, 0.0, 1.0, 1.0);
    }

    /**
     * Creates a fully transparent black color.
     */
    public static function clear(): self
    {
        return new self(0.0, 0.0, 0.0, 0.0);
    }

    /**
     * Creates an opaque cyan color.
     */
    public static function cyan(): self
    {
        return new self(0.0, 1.0, 1.0, 1.0);
    }

    /**
     * Creates an opaque gray color.
     */
    public static function gray(): self
    {
        return new self(0.5, 0.5, 0.5, 1.0);
    }

    /**
     * Creates an opaque green color.
     */
    public static function green(): self
    {
        return new self(0.0, 1.0, 0.0, 1.0);
    }

    /**
     * Creates an opaque magenta color.
     */
    public static function magenta(): self
    {
        return new self(1.0, 0.0, 1.0, 1.0);
    }

    /**
     * Creates an opaque red color.
     */
    public static function red(): self
    {
        return new self(1.0, 0.0, 0.0, 1.0);
    }

    /**
     * Creates an opaque white color.
     */
    public static function white(): self
    {
        return new self(1.0, 1.0, 1.0, 1.0);
    }

    /**
     * Creates an opaque yellow color.
     */
    public static function yellow(): self
    {
        return new self(1.0, 1.0, 0.0, 1.0);
    }

    /**
     * Linearly interpolates between two colors by a clamped ratio.
     *
     * @param self $from The color returned when `$t` is `0.0` or lower.
     * @param self $to The color returned when `$t` is `1.0` or higher.
     * @param float $t The interpolation ratio, clamped to `0.0` through `1.0`.
     */
    public static function lerp(self $from, self $to, float $t): self
    {
        $ratio = MathUtil::clamp($t, 0.0, 1.0);

        $r = MathUtil::lerp($from->r, $to->r, $ratio);
        $g = MathUtil::lerp($from->g, $to->g, $ratio);
        $b = MathUtil::lerp($from->b, $to->b, $ratio);
        $a = MathUtil::lerp($from->a, $to->a, $ratio);

        return new self($r, $g, $b, $a);
    }

    /**
     * Converts red, green and blue values to hue, saturation and value (HSV).
     *
     * Input channels are clamped to `0.0` through `1.0`. The results are
     * rounded to whole numbers.
     *
     * @param float $r Red, from 0.0 to 1.0.
     * @param float $g Green, from 0.0 to 1.0.
     * @param float $b Blue, from 0.0 to 1.0.
     * @return array{h: int, s: int, v: int} Hue in degrees from 0 to 359, and saturation and value
     *     as percentages from 0 to 100.
     */
    public static function rgbToHsv(float $r, float $g, float $b): array
    {
        $red = self::clampUnit($r);
        $green = self::clampUnit($g);
        $blue = self::clampUnit($b);

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $delta = $max - $min;

        $hue = match (true) {
            $delta === 0.0 => 0.0,
            $max === $red => 60.0 * fmod((($green - $blue) / $delta), 6.0),
            $max === $green => 60.0 * ((($blue - $red) / $delta) + 2.0),
            default => 60.0 * ((($red - $green) / $delta) + 4.0),
        };

        $hue = self::normalizeHue($hue);
        $roundedHue = (int) round($hue);

        return [
            'h' => $roundedHue === 360 ? 0 : $roundedHue,
            's' => $max === 0.0 ? 0 : (int) round(($delta / $max) * 100.0),
            'v' => (int) round($max * 100.0),
        ];
    }

    /**
     * Converts hue, saturation and value (HSV) to red, green and blue values from 0 to 255.
     *
     * Hue wraps around the 360-degree color wheel. Saturation and value are
     * clamped to percentage values from `0.0` through `100.0`.
     *
     * @param float $h Hue in degrees. Values outside 0 to 360 wrap around.
     * @param float $s Saturation as a percentage, from 0 to 100.
     * @param float $v Value (brightness) as a percentage, from 0 to 100.
     * @return array{r: int, g: int, b: int} Red, green and blue, each from 0 to 255.
     */
    public static function hsvToRgb(float $h, float $s, float $v): array
    {
        $hue = self::normalizeHue($h);
        $saturation = MathUtil::clamp($s, 0.0, 100.0) / 100.0;
        $value = MathUtil::clamp($v, 0.0, 100.0) / 100.0;
        $chroma = $value * $saturation;
        $x = $chroma * (1.0 - abs(fmod($hue / 60.0, 2.0) - 1.0));
        $m = $value - $chroma;

        [$red, $green, $blue] = match (true) {
            $hue < 60.0 => [$chroma, $x, 0.0],
            $hue < 120.0 => [$x, $chroma, 0.0],
            $hue < 180.0 => [0.0, $chroma, $x],
            $hue < 240.0 => [0.0, $x, $chroma],
            $hue < 300.0 => [$x, 0.0, $chroma],
            default => [$chroma, 0.0, $x],
        };

        return [
            'r' => self::unitToByte($red + $m),
            'g' => self::unitToByte($green + $m),
            'b' => self::unitToByte($blue + $m),
        ];
    }

    /**
     * Converts this color to normalized RGBA values.
     *
     * @return array{r: float, g: float, b: float, a: float}
     */
    public function toNormalizedRGBA(): array
    {
        return [
            'r' => $this->red,
            'g' => $this->green,
            'b' => $this->blue,
            'a' => $this->alpha,
        ];
    }

    /**
     * Returns the color as text in the form `rgba(255, 128, 0, 255)`, useful for logging.
     */
    public function __toString(): string
    {
        return sprintf(
            'rgba(%d, %d, %d, %d)',
            self::unitToByte($this->red),
            self::unitToByte($this->green),
            self::unitToByte($this->blue),
            self::unitToByte($this->alpha),
        );
    }

    private static function unitToByte(float $value): int
    {
        return self::clampByte((int) round(self::clampUnit($value) * self::MAX_BYTE_VALUE));
    }

    private static function clampByte(int $value): int
    {
        return max(self::MIN_BYTE_VALUE, min(self::MAX_BYTE_VALUE, $value));
    }

    /**
     * Serializes the color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function __serialize(): array
    {
        return $this->toRGBA();
    }

    /**
     * Converts this color to red, green, blue and alpha values from 0 to 255.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function toRGBA(): array
    {
        return [
            'r' => self::unitToByte($this->red),
            'g' => self::unitToByte($this->green),
            'b' => self::unitToByte($this->blue),
            'a' => self::unitToByte($this->alpha),
        ];
    }

    /**
     * Restores the color from byte-channel RGBA values.
     *
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $data
     */
    public function __unserialize(array $data): void
    {
        $color = self::fromRGBAArray($data);
        $this->red = $color->r;
        $this->green = $color->g;
        $this->blue = $color->b;
        $this->alpha = $color->a;
    }

    /**
     * Creates a color from an array of 0 to 255 values.
     *
     * The array can use the keys `r`, `g`, `b` and `a`, or the indexes 0 to 3
     * in that order. A missing channel is 255. Values are converted to whole
     * numbers and clamped to 0 through 255.
     *
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $value
     *     The channel values, for example `['r' => 255, 'g' => 128, 'b' => 0]`.
     */
    public static function fromRGBAArray(array $value): self
    {
        return self::fromRGBA(
            (int) ($value['r'] ?? $value[0] ?? self::MAX_BYTE_VALUE),
            (int) ($value['g'] ?? $value[1] ?? self::MAX_BYTE_VALUE),
            (int) ($value['b'] ?? $value[2] ?? self::MAX_BYTE_VALUE),
            (int) ($value['a'] ?? $value[3] ?? self::MAX_BYTE_VALUE),
        );
    }

    /**
     * Creates a color from red, green, blue and alpha values from 0 to 255.
     *
     * Values outside that range are clamped.
     *
     * @param int $red Red, from 0 to 255.
     * @param int $green Green, from 0 to 255.
     * @param int $blue Blue, from 0 to 255.
     * @param int $alpha Alpha (opacity), from 0 (transparent) to 255 (opaque).
     */
    public static function fromRGBA(int $red, int $green, int $blue, int $alpha = self::MAX_BYTE_VALUE): self
    {
        return new self(
            self::clampByte($red) / self::MAX_BYTE_VALUE,
            self::clampByte($green) / self::MAX_BYTE_VALUE,
            self::clampByte($blue) / self::MAX_BYTE_VALUE,
            self::clampByte($alpha) / self::MAX_BYTE_VALUE,
        );
    }

    /**
     * Creates a color from a `#RRGGBB` or `#RRGGBBAA` hex string, as asset files store colours.
     *
     * `#RRGGBB` is opaque. Hex digits may be either case.
     *
     * @param string $hex The color, for example `#FF8800` or `#FF880080`.
     * @throws InvalidArgumentException When the string is not one of the two forms.
     */
    public static function fromHex(string $hex): self
    {
        return self::tryFromHex($hex)
            ?? throw new InvalidArgumentException("Invalid hex color '{$hex}'. Use #RRGGBB or #RRGGBBAA.");
    }

    /**
     * Creates a color from a `#RRGGBB` or `#RRGGBBAA` hex string, or returns null when the string is neither.
     *
     * Use it for text that may not be a valid color, such as player input.
     *
     * @param string $hex The color, for example `#FF8800` or `#FF880080`.
     */
    public static function tryFromHex(string $hex): ?self
    {
        if (preg_match('/^#(?:[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $hex) !== 1) {
            return null;
        }

        $digits = substr($hex, 1);

        return self::fromRGBA(
            (int) hexdec(substr($digits, 0, 2)),
            (int) hexdec(substr($digits, 2, 2)),
            (int) hexdec(substr($digits, 4, 2)),
            strlen($digits) === 8 ? (int) hexdec(substr($digits, 6, 2)) : self::MAX_BYTE_VALUE,
        );
    }

    /**
     * Returns the color as an uppercase hex string, as asset files store colours.
     *
     * An opaque color is `#RRGGBB` and any other is `#RRGGBBAA`.
     */
    public function toHex(): string
    {
        $channels = $this->toRGBA();
        $hex = sprintf('#%02X%02X%02X', $channels['r'], $channels['g'], $channels['b']);

        return $channels['a'] === self::MAX_BYTE_VALUE ? $hex : $hex . sprintf('%02X', $channels['a']);
    }

    /**
     * Returns the color as JSON with 0 to 255 channel values, for example `{"r":255,"g":128,"b":0,"a":255}`.
     */
    public function toJson(): string
    {
        return (string)json_encode($this->toRGBA());
    }
}
