<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Stringable;
use function json_encode;
use function max;
use function min;
use function round;
use function sprintf;

/**
 * Represents an RGBA color using normalized 0.0 to 1.0 channels.
 *
 * Use the constructor for normalized color values and {@see fromRGBA()} when
 * working with byte-channel colors from native engine state.
 */
final class Color implements Stringable
{
    public const float MIN_VALUE = 0.0;
    public const float MAX_VALUE = 1.0;
    public const int MIN_BYTE_VALUE = 0;
    public const int MAX_BYTE_VALUE = 255;

    public float $r {
        get => $this->red;
    }

    public float $g {
        get => $this->green;
    }

    public float $b {
        get => $this->blue;
    }

    public float $a {
        get => $this->alpha;
    }

    private float $red;
    private float $green;
    private float $blue;
    private float $alpha;

    /**
     * Creates a normalized RGBA color.
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
     * Creates a color from 0 to 255 byte-channel RGBA values.
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
     * Creates a color from native engine color state.
     *
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $value
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
     * Converts this color to 0 to 255 byte-channel RGBA values.
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
     * Returns a compact string useful for debugging.
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

    /**
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function __serialize(): array
    {
        return $this->toRGBA();
    }

    /**
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
     * JSON-encodes the byte-channel RGBA representation.
     */
    public function toJson(): string
    {
        return (string) json_encode($this->toRGBA());
    }

    private static function clampUnit(float $value): float
    {
        return MathUtil::clamp($value, self::MIN_VALUE, self::MAX_VALUE);
    }

    private static function clampByte(int $value): int
    {
        return max(self::MIN_BYTE_VALUE, min(self::MAX_BYTE_VALUE, $value));
    }

    private static function unitToByte(float $value): int
    {
        return self::clampByte((int) round(self::clampUnit($value) * self::MAX_BYTE_VALUE));
    }
}
