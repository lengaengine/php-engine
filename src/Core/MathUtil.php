<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A collection of common math functions.
 *
 * PHP port of Unity's Mathf struct. Every static method mirrors the C#
 * signature as closely as PHP's type system allows. Angles are always
 * in **radians** unless the method name says otherwise (e.g. LerpAngle,
 * MoveTowardsAngle, DeltaAngle — those work in **degrees**).
 *
 * @see https://docs.unity3d.com/ScriptReference/Mathf.html
 */
final class MathUtil
{
    // ──────────────────────────────────────────────
    //  Static Properties (constants)
    // ──────────────────────────────────────────────

    /** Degrees-to-radians conversion factor. */
    public const DEG_2_RAD = M_PI / 180.0;

    /** Radians-to-degrees conversion factor. */
    public const RAD_2_DEG = 180.0 / M_PI;

    /** The well-known 3.14159265358979… */
    public const PI = M_PI;

    /**
     * The smallest positive non-zero float that is meaningful for equality
     * comparisons. Mirrors Unity's Mathf.Epsilon (≈1.401298E-45 in C#).
     *
     * PHP's float is double-precision, so we use PHP_FLOAT_EPSILON instead
     * (≈2.22E-16). For an approximately-equal comparison on normal-range
     * numbers, use {@see approximately()} which applies a tolerance that
     * scales with the operand magnitudes.
     */
    public const EPSILON = PHP_FLOAT_EPSILON;

    /** Positive infinity. */
    public const INFINITY = INF;

    /** Negative infinity. */
    public const NEGATIVE_INFINITY = -INF;

    /** Prevent instantiation. */
    private function __construct() {}

    // ──────────────────────────────────────────────
    //  Basic math
    // ──────────────────────────────────────────────

    /**
     * Returns the absolute value of f.
     */
    public static function abs(float $f): float
    {
        return abs($f);
    }

    /**
     * Returns the smallest integer greater than or equal to f.
     */
    public static function ceil(float $f): float
    {
        return ceil($f);
    }

    /**
     * Returns the smallest integer greater than or equal to f (as int).
     */
    public static function ceilToInt(float $f): int
    {
        return (int) ceil($f);
    }

    /**
     * Returns the largest integer smaller than or equal to f.
     */
    public static function floor(float $f): float
    {
        return floor($f);
    }

    /**
     * Returns the largest integer smaller than or equal to f (as int).
     */
    public static function floorToInt(float $f): int
    {
        return (int) floor($f);
    }

    /**
     * Returns f rounded to the nearest integer.
     */
    public static function round(float $f): float
    {
        return round($f);
    }

    /**
     * Returns f rounded to the nearest integer (as int).
     */
    public static function roundToInt(float $f): int
    {
        return (int) round($f);
    }

    /**
     * Returns the mathematical sign of f: -1, 0 or 1.
     */
    public static function sign(float $f): float
    {
        if ($f > 0.0) {
            return 1.0;
        }
        if ($f < 0.0) {
            return -1.0;
        }
        return 0.0;
    }

    /**
     * Returns the square root of f.
     */
    public static function sqrt(float $f): float
    {
        return sqrt($f);
    }

    /**
     * Returns f raised to the power p.
     */
    public static function pow(float $f, float $p): float
    {
        return pow($f, $p);
    }

    /**
     * Returns e raised to the power power.
     */
    public static function exp(float $power): float
    {
        return exp($power);
    }

    /**
     * Returns the logarithm of f in a specified base.
     *
     * When $base is omitted the natural logarithm (base e) is returned,
     * matching C#'s default.
     */
    public static function log(float $f, float $base = M_E): float
    {
        if ($base === M_E) {
            return log($f);
        }

        return log($f) / log($base);
    }

    /**
     * Returns the base-10 logarithm of f.
     */
    public static function log10(float $f): float
    {
        return log10($f);
    }

    // ──────────────────────────────────────────────
    //  Trigonometry
    // ──────────────────────────────────────────────

    /**
     * Returns the arc-cosine of f (radians).
     */
    public static function acos(float $f): float
    {
        return acos($f);
    }

    /**
     * Returns the arc-sine of f (radians).
     */
    public static function asin(float $f): float
    {
        return asin($f);
    }

    /**
     * Returns the arc-tangent of f (radians).
     */
    public static function atan(float $f): float
    {
        return atan($f);
    }

    /**
     * Returns the angle in radians whose tangent is y/x.
     */
    public static function atan2(float $y, float $x): float
    {
        return atan2($y, $x);
    }

    /**
     * Returns the cosine of angle f (radians).
     */
    public static function cos(float $f): float
    {
        return cos($f);
    }

    /**
     * Returns the sine of angle f (radians).
     */
    public static function sin(float $f): float
    {
        return sin($f);
    }

    /**
     * Returns the tangent of angle f (radians).
     */
    public static function tan(float $f): float
    {
        return tan($f);
    }

    // ──────────────────────────────────────────────
    //  Clamping & range
    // ──────────────────────────────────────────────

    /**
     * Clamps value between min and max and returns the clamped value.
     */
    public static function clamp(float $value, float $min, float $max): float
    {
        return min(max($value, $min), $max);
    }

    /**
     * Clamps value between 0 and 1.
     */
    public static function clamp01(float $value): float
    {
        return self::clamp($value, 0.0, 1.0);
    }

    /**
     * Returns the minimum of two or more values.
     *
     * Accepts exactly two floats or an arbitrary number of floats.
     */
    public static function min(float $a, float ...$rest): float
    {
        $result = $a;
        foreach ($rest as $v) {
            if ($v < $result) {
                $result = $v;
            }
        }
        return $result;
    }

    /**
     * Returns the maximum of two or more values.
     */
    public static function max(float $a, float ...$rest): float
    {
        $result = $a;
        foreach ($rest as $v) {
            if ($v > $result) {
                $result = $v;
            }
        }
        return $result;
    }

    /**
     * Loops the value t so that it is never larger than length and never
     * smaller than 0.
     *
     * Equivalent to Unity's Mathf.Repeat.
     */
    public static function repeat(float $t, float $length): float
    {
        if ($length === 0.0) {
            return 0.0;
        }

        return self::clamp($t - self::floor($t / $length) * $length, 0.0, $length);
    }

    /**
     * PingPongs the value t between 0 and length.
     *
     * The returned value moves back and forth as t increases: it follows
     * the triangle wave formula where the bottom is 0 and the peak is
     * length.
     */
    public static function pingPong(float $t, float $length): float
    {
        if ($length === 0.0) {
            return 0.0;
        }

        $t = self::repeat($t, $length * 2.0);

        return $length - self::abs($t - $length);
    }

    // ──────────────────────────────────────────────
    //  Wrapping (custom, not in Unity Mathf)
    // ──────────────────────────────────────────────

    /**
     * Wraps a value into the [min, max) range.
     *
     * This is a Lenga-specific helper that generalises {@see repeat()} to
     * an arbitrary range.
     *
     * Important: the upper bound is exclusive. The returned value is always
     * greater than or equal to $min and strictly less than $max when
     * $max > $min.
     *
     * For example, wrapping menu indices for a 3-item list should use:
     *
     * - `MathUtil::wrap($index, 0, 3)` for the range `0, 1, 2`
     * - not `MathUtil::wrap($index, 0, 2)`, because `2` would be treated as
     *   the exclusive upper bound and only `0, 1` would be reachable
     *
     * If you need an inclusive integer clamp instead of wraparound behavior,
     * use {@see clamp()}.
     */
    public static function wrap(float $value, float $min, float $max): float
    {
        $range = $max - $min;
        if ($range <= 0.0) {
            return $min;
        }

        $wrappedValue = fmod($value - $min, $range);
        if ($wrappedValue < 0.0) {
            $wrappedValue += $range;
        }

        return $wrappedValue + $min;
    }

    // ──────────────────────────────────────────────
    //  Interpolation
    // ──────────────────────────────────────────────

    /**
     * Linearly interpolates between a and b by t (clamped to [0, 1]).
     */
    public static function lerp(float $a, float $b, float $t): float
    {
        $t = self::clamp01($t);

        return $a + ($b - $a) * $t;
    }

    /**
     * Linearly interpolates between a and b by t with no clamping.
     */
    public static function lerpUnclamped(float $a, float $b, float $t): float
    {
        return $a + ($b - $a) * $t;
    }

    /**
     * Same as {@see lerp()} but interpolates correctly when values wrap
     * around 360 degrees.
     *
     * @param float $a     Start angle in degrees.
     * @param float $b     End angle in degrees.
     * @param float $t     Interpolation factor (clamped to [0, 1]).
     * @return float       Interpolated angle in degrees.
     */
    public static function lerpAngle(float $a, float $b, float $t): float
    {
        $delta = self::repeat($b - $a, 360.0);
        if ($delta > 180.0) {
            $delta -= 360.0;
        }

        return $a + $delta * self::clamp01($t);
    }

    /**
     * Determines the interpolation factor of value between a and b
     * (clamped to [0, 1]).
     */
    public static function inverseLerp(float $a, float $b, float $t): float
    {
        if ($a === $b) {
            return 0.0;
        }

        return self::clamp01(($t - $a) / ($b - $a));
    }

    /**
     * Interpolates between from and to with smoothing at the limits.
     *
     * Uses the classic smoothstep polynomial 3t² − 2t³.
     */
    public static function smoothStep(float $from, float $to, float $t): float
    {
        $t = self::clamp01($t);
        $t = $t * $t * (3.0 - 2.0 * $t);

        return $to * $t + $from * (1.0 - $t);
    }

    /**
     * Gradually moves the current value towards a target value, over a
     * specified time and at a specified velocity.
     *
     * The function is damped so it never overshoots. The most common use
     * is for smoothing a follow-camera.
     *
     * @param float      $current         The current position.
     * @param float      $target          The target position.
     * @param float      &$currentVelocity Reference to current velocity (modified each call).
     * @param float      $smoothTime      Approximate time to reach the target (seconds).
     * @param float      $deltaTime       Time since last call (seconds).
     * @param float      $maxSpeed        Maximum speed (default: INF).
     * @return float                       New position.
     */
    public static function smoothDamp(
        float $current,
        float $target,
        float &$currentVelocity,
        float $smoothTime,
        float $deltaTime,
        float $maxSpeed = self::INFINITY,
    ): float {
        // Based on Game Programming Gems 4, ch. 1.10.
        $smoothTime = self::max(0.0001, $smoothTime);
        $omega      = 2.0 / $smoothTime;

        $x      = $omega * $deltaTime;
        $expVal = 1.0 / (1.0 + $x + 0.48 * $x * $x + 0.235 * $x * $x * $x);

        $change       = $current - $target;
        $originalTo   = $target;

        // Clamp maximum speed.
        $maxChange = $maxSpeed * $smoothTime;
        $change    = self::clamp($change, -$maxChange, $maxChange);
        $target    = $current - $change;

        $temp = ($currentVelocity + $omega * $change) * $deltaTime;

        $currentVelocity = ($currentVelocity - $omega * $temp) * $expVal;

        $output = $target + ($change + $temp) * $expVal;

        // Prevent overshooting.
        if (($originalTo - $current > 0.0) === ($output > $originalTo)) {
            $output          = $originalTo;
            $currentVelocity = ($output - $originalTo) / $deltaTime;
        }

        return $output;
    }

    /**
     * Gradually changes an angle given in degrees towards a desired goal
     * angle over time.
     *
     * @param float  $current         Current angle in degrees.
     * @param float  $target          Target angle in degrees.
     * @param float  &$currentVelocity Reference to current angular velocity.
     * @param float  $smoothTime      Approximate time to reach the target.
     * @param float  $deltaTime       Time since last call.
     * @param float  $maxSpeed        Maximum angular speed (degrees/sec).
     * @return float                   New angle in degrees.
     */
    public static function smoothDampAngle(
        float $current,
        float $target,
        float &$currentVelocity,
        float $smoothTime,
        float $deltaTime,
        float $maxSpeed = self::INFINITY,
    ): float {
        $target = $current + self::deltaAngle($current, $target);

        return self::smoothDamp(
            $current,
            $target,
            $currentVelocity,
            $smoothTime,
            $deltaTime,
            $maxSpeed,
        );
    }

    /**
     * Moves a value current towards target.
     *
     * This is essentially the same as lerp but the function ensures the
     * speed never exceeds maxDelta. Negative maxDelta pushes the value
     * away from target.
     */
    public static function moveTowards(float $current, float $target, float $maxDelta): float
    {
        if (self::abs($target - $current) <= $maxDelta) {
            return $target;
        }

        return $current + self::sign($target - $current) * $maxDelta;
    }

    /**
     * Same as {@see moveTowards()} but handles angles wrapping around
     * 360 degrees.
     *
     * @param float $current  Current angle in degrees.
     * @param float $target   Target angle in degrees.
     * @param float $maxDelta Maximum change in degrees per call.
     * @return float           New angle in degrees.
     */
    public static function moveTowardsAngle(float $current, float $target, float $maxDelta): float
    {
        $delta = self::deltaAngle($current, $target);

        if (-$maxDelta < $delta && $delta < $maxDelta) {
            return $target;
        }

        $target = $current + $delta;

        return self::moveTowards($current, $target, $maxDelta);
    }

    // ──────────────────────────────────────────────
    //  Utility
    // ──────────────────────────────────────────────

    /**
     * Compares two floating-point values and returns true if they are
     * similar.
     *
     * Uses the same tolerance algorithm as Unity: the values are
     * considered equal when the absolute difference is less than
     * max(1E-06 * max(|a|,|b|), Epsilon * 8).
     */
    public static function approximately(float $a, float $b): bool
    {
        return self::abs($b - $a) < self::max(
            1E-06 * self::max(self::abs($a), self::abs($b)),
            self::EPSILON * 8.0,
        );
    }

    /**
     * Calculates the shortest difference between two angles in degrees.
     *
     * The result is always in the range (−180, 180].
     */
    public static function deltaAngle(float $current, float $target): float
    {
        $delta = self::repeat($target - $current, 360.0);
        if ($delta > 180.0) {
            $delta -= 360.0;
        }

        return $delta;
    }

    /**
     * Returns the closest power of two to the given value.
     */
    public static function closestPowerOfTwo(int $value): int
    {
        if ($value <= 0) {
            return 1;
        }

        $next = self::nextPowerOfTwo($value);
        $prev = $next >> 1;

        // On a tie, round up to the higher power (matches Unity behaviour).
        return ($value - $prev < $next - $value) ? $prev : $next;
    }

    /**
     * Returns true if the value is a power of two.
     */
    public static function isPowerOfTwo(int $value): bool
    {
        return $value > 0 && ($value & ($value - 1)) === 0;
    }

    /**
     * Returns the next power of two that is equal to or greater than the
     * given value.
     */
    public static function nextPowerOfTwo(int $value): int
    {
        if ($value <= 0) {
            return 1;
        }

        $value--;
        $value |= $value >> 1;
        $value |= $value >> 2;
        $value |= $value >> 4;
        $value |= $value >> 8;
        $value |= $value >> 16;
        $value++;

        return $value;
    }

    /**
     * Generates 2D Perlin noise for the given coordinates.
     *
     * Returns a value in the range [0, 1]. The implementation uses a
     * classic permutation-table approach so that output is deterministic
     * and reproducible across runs.
     */
    public static function perlinNoise(float $x, float $y): float
    {
        // Integer grid coordinates.
        $xi = self::floorToInt($x) & 255;
        $yi = self::floorToInt($y) & 255;

        // Fractional part.
        $xf = $x - self::floor($x);
        $yf = $y - self::floor($y);

        // Fade curves.
        $u = self::perlinFade($xf);
        $v = self::perlinFade($yf);

        $p = self::perlinPermutation();

        $aa = $p[$p[$xi] + $yi];
        $ab = $p[$p[$xi] + $yi + 1];
        $ba = $p[$p[$xi + 1] + $yi];
        $bb = $p[$p[$xi + 1] + $yi + 1];

        $x1 = self::lerpUnclamped(
            self::perlinGrad($aa, $xf, $yf),
            self::perlinGrad($ba, $xf - 1.0, $yf),
            $u,
        );
        $x2 = self::lerpUnclamped(
            self::perlinGrad($ab, $xf, $yf - 1.0),
            self::perlinGrad($bb, $xf - 1.0, $yf - 1.0),
            $u,
        );

        // Map from [-1, 1] to [0, 1].
        return (self::lerpUnclamped($x1, $x2, $v) + 1.0) / 2.0;
    }

    /**
     * Generates 1D pseudo-random pattern of float values across a 2D
     * plane.
     *
     * Convenience alias — samples Perlin noise along the x-axis at y = 0.
     */
    public static function perlinNoise1D(float $x): float
    {
        return self::perlinNoise($x, 0.0);
    }

    /**
     * Encode a 32-bit float into its IEEE 754 half-precision (16-bit)
     * representation returned as an int.
     */
    public static function floatToHalf(float $f): int
    {
        $bits = unpack('N', pack('G', $f))[1];

        $sign     = ($bits >> 16) & 0x8000;
        $exponent = (($bits >> 23) & 0xFF) - 127 + 15;
        $mantissa = $bits & 0x7FFFFF;

        if ($exponent <= 0) {
            // Subnormal or zero.
            if ($exponent < -10) {
                return $sign;
            }
            $mantissa = ($mantissa | 0x800000) >> (1 - $exponent);
            return $sign | ($mantissa >> 13);
        }

        if ($exponent >= 31) {
            // Overflow → infinity or NaN.
            return $sign | 0x7C00 | (($mantissa !== 0) ? ($mantissa >> 13) | 1 : 0);
        }

        return $sign | ($exponent << 10) | ($mantissa >> 13);
    }

    /**
     * Convert a half-precision float (16-bit int) to a 32-bit float.
     */
    public static function halfToFloat(int $half): float
    {
        $sign     = ($half & 0x8000) << 16;
        $exponent = ($half >> 10) & 0x1F;
        $mantissa = $half & 0x03FF;

        if ($exponent === 0) {
            if ($mantissa === 0) {
                $bits = $sign;
            } else {
                // Subnormal → normalise.
                $exponent = 1;
                while (($mantissa & 0x0400) === 0) {
                    $mantissa <<= 1;
                    $exponent--;
                }
                $mantissa &= 0x03FF;
                $bits = $sign | ((127 - 15 + $exponent) << 23) | ($mantissa << 13);
            }
        } elseif ($exponent === 31) {
            $bits = $sign | 0x7F800000 | ($mantissa << 13);
        } else {
            $bits = $sign | (($exponent - 15 + 127) << 23) | ($mantissa << 13);
        }

        return unpack('G', pack('N', $bits))[1];
    }

    /**
     * Converts a value from gamma (sRGB) to linear colour space.
     */
    public static function gammaToLinearSpace(float $value): float
    {
        if ($value <= 0.04045) {
            return $value / 12.92;
        }

        return self::pow(($value + 0.055) / 1.055, 2.4);
    }

    /**
     * Converts a value from linear to gamma (sRGB) colour space.
     */
    public static function linearToGammaSpace(float $value): float
    {
        if ($value <= 0.0031308) {
            return $value * 12.92;
        }

        return 1.055 * self::pow($value, 1.0 / 2.4) - 0.055;
    }

    /**
     * Converts a colour temperature in Kelvin to an approximate RGB
     * colour (each channel in [0, 1]).
     *
     * Uses Tanner Helland's approximation algorithm which is a widely
     * adopted approach for real-time colour temperature conversion.
     *
     * @return array{r: float, g: float, b: float}
     */
    public static function correlatedColorTemperatureToRGB(float $kelvin): array
    {
        $temp = self::clamp($kelvin, 1000.0, 40000.0) / 100.0;

        // Red channel.
        if ($temp <= 66.0) {
            $r = 1.0;
        } else {
            $r = $temp - 60.0;
            $r = 329.698727446 * self::pow($r, -0.1332047592) / 255.0;
        }

        // Green channel.
        if ($temp <= 66.0) {
            $g = 99.4708025861 * self::log($temp) - 161.1195681661;
        } else {
            $g = $temp - 60.0;
            $g = 288.1221695283 * self::pow($g, -0.0755148492);
        }
        $g /= 255.0;

        // Blue channel.
        if ($temp >= 66.0) {
            $b = 1.0;
        } elseif ($temp <= 19.0) {
            $b = 0.0;
        } else {
            $b = $temp - 10.0;
            $b = (138.5177312231 * self::log($b) - 305.0447927307) / 255.0;
        }

        return [
            'r' => self::clamp01($r),
            'g' => self::clamp01($g),
            'b' => self::clamp01($b),
        ];
    }

    // ──────────────────────────────────────────────
    //  Perlin noise internals
    // ──────────────────────────────────────────────

    /**
     * Fade curve for Perlin noise: 6t^5 − 15t^4 + 10t^3.
     */
    private static function perlinFade(float $t): float
    {
        return $t * $t * $t * ($t * ($t * 6.0 - 15.0) + 10.0);
    }

    /**
     * Gradient function for 2D Perlin noise.
     */
    private static function perlinGrad(int $hash, float $x, float $y): float
    {
        return match ($hash & 3) {
            0 =>  $x + $y,
            1 => -$x + $y,
            2 =>  $x - $y,
            3 => -$x - $y,
        };
    }

    /**
     * Returns the classic 512-entry permutation table for Perlin noise.
     *
     * The table is Ken Perlin's original doubled permutation so that
     * lookups never need bounds checking.
     *
     * @return int[]
     */
    private static function perlinPermutation(): array
    {
        /** @var int[]|null Cached after first call. */
        static $perm = null;

        if ($perm !== null) {
            return $perm;
        }

        $p = [
            151,160,137,91,90,15,131,13,201,95,96,53,194,233,7,225,
            140,36,103,30,69,142,8,99,37,240,21,10,23,190,6,148,
            247,120,234,75,0,26,197,62,94,252,219,203,117,35,11,32,
            57,177,33,88,237,149,56,87,174,20,125,136,171,168,68,175,
            74,165,71,134,139,48,27,166,77,146,158,231,83,111,229,122,
            60,211,133,230,220,105,92,41,55,46,245,40,244,102,143,54,
            65,25,63,161,1,216,80,73,209,76,132,187,208,89,18,169,
            200,196,135,130,116,188,159,86,164,100,109,198,173,186,3,64,
            52,217,226,250,124,123,5,202,38,147,118,126,255,82,85,212,
            207,206,59,227,47,16,58,17,182,189,28,42,223,183,170,213,
            119,248,152,2,44,154,163,70,221,153,101,155,167,43,172,9,
            129,22,39,253,19,98,108,110,79,113,224,232,178,185,112,104,
            218,246,97,228,251,34,242,193,238,210,144,12,191,179,162,241,
            81,51,145,235,249,14,239,107,49,192,214,31,181,199,106,157,
            184,84,204,176,115,121,50,45,127,4,150,254,138,236,205,93,
            222,114,67,29,24,72,243,141,128,195,78,66,215,61,156,180,
        ];

        $perm = [];
        for ($i = 0; $i < 512; $i++) {
            $perm[$i] = $p[$i & 255];
        }

        return $perm;
    }
}
