<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use InvalidArgumentException;
use Lenga\Engine\Core\Color;
use function is_array;

/**
 * Normalizes public PHP color values into native RGBA byte channels.
 *
 * This keeps public APIs free to accept first-class {@see Color} values while
 * preserving existing byte-channel call sites.
 */
final class ColorBridge
{
    /**
     * Reads a color from native state, falling back to the provided default.
     */
    public static function fromState(mixed $value, Color $default): Color
    {
        if (!is_array($value)) {
            return $default;
        }

        $fallback = $default->toRGBA();

        return Color::fromRGBAArray([
            'r' => $value['r'] ?? $fallback['r'],
            'g' => $value['g'] ?? $fallback['g'],
            'b' => $value['b'] ?? $fallback['b'],
            'a' => $value['a'] ?? $fallback['a'],
        ]);
    }

    /**
     * Converts a Color, native color array, or byte-channel arguments to RGBA.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $color
     * @return array{r: int, g: int, b: int, a: int}
     */
    public static function toNative(Color|array|int $color, ?int $green = null, ?int $blue = null, int $alpha = Color::MAX_BYTE_VALUE): array
    {
        if ($color instanceof Color) {
            return $color->toRGBA();
        }

        if (is_array($color)) {
            return Color::fromRGBAArray($color)->toRGBA();
        }

        if ($green === null || $blue === null) {
            throw new InvalidArgumentException('RGB color values require red, green, and blue channels.');
        }

        return Color::fromRGBA($color, $green, $blue, $alpha)->toRGBA();
    }
}
