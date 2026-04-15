<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\MathUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the MathUtil class.
 *
 * Covers every static property and method to validate parity with
 * Unity's Mathf behaviour.
 */
final class MathUtilTest extends TestCase
{
    // ──────────────────────────────────────────
    //  Constants
    // ──────────────────────────────────────────

    public function testDeg2Rad(): void
    {
        self::assertEqualsWithDelta(M_PI / 180.0, MathUtil::DEG_2_RAD, 1e-15);
    }

    public function testRad2Deg(): void
    {
        self::assertEqualsWithDelta(180.0 / M_PI, MathUtil::RAD_2_DEG, 1e-12);
    }

    public function testPi(): void
    {
        self::assertSame(M_PI, MathUtil::PI);
    }

    public function testEpsilon(): void
    {
        self::assertSame(PHP_FLOAT_EPSILON, MathUtil::EPSILON);
    }

    public function testInfinity(): void
    {
        self::assertSame(INF, MathUtil::INFINITY);
    }

    public function testNegativeInfinity(): void
    {
        self::assertSame(-INF, MathUtil::NEGATIVE_INFINITY);
    }

    // ──────────────────────────────────────────
    //  Basic math
    // ──────────────────────────────────────────

    public function testAbs(): void
    {
        self::assertSame(5.0, MathUtil::abs(-5.0));
        self::assertSame(5.0, MathUtil::abs(5.0));
        self::assertSame(0.0, MathUtil::abs(0.0));
    }

    public function testCeil(): void
    {
        self::assertSame(3.0, MathUtil::ceil(2.3));
        self::assertSame(-2.0, MathUtil::ceil(-2.3));
        self::assertSame(1.0, MathUtil::ceil(0.1));
    }

    public function testCeilToInt(): void
    {
        self::assertSame(3, MathUtil::ceilToInt(2.3));
        self::assertSame(-2, MathUtil::ceilToInt(-2.3));
    }

    public function testFloor(): void
    {
        self::assertSame(2.0, MathUtil::floor(2.9));
        self::assertSame(-3.0, MathUtil::floor(-2.1));
    }

    public function testFloorToInt(): void
    {
        self::assertSame(2, MathUtil::floorToInt(2.9));
        self::assertSame(-3, MathUtil::floorToInt(-2.1));
    }

    public function testRound(): void
    {
        self::assertSame(3.0, MathUtil::round(2.7));
        self::assertSame(2.0, MathUtil::round(2.3));
    }

    public function testRoundToInt(): void
    {
        self::assertSame(3, MathUtil::roundToInt(2.7));
        self::assertSame(2, MathUtil::roundToInt(2.3));
    }

    public function testSign(): void
    {
        self::assertSame(1.0, MathUtil::sign(42.0));
        self::assertSame(-1.0, MathUtil::sign(-0.5));
        self::assertSame(0.0, MathUtil::sign(0.0));
    }

    public function testSqrt(): void
    {
        self::assertEqualsWithDelta(3.0, MathUtil::sqrt(9.0), 1e-15);
        self::assertSame(0.0, MathUtil::sqrt(0.0));
    }

    public function testPow(): void
    {
        self::assertEqualsWithDelta(8.0, MathUtil::pow(2.0, 3.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::pow(5.0, 0.0), 1e-15);
    }

    public function testExp(): void
    {
        self::assertEqualsWithDelta(M_E, MathUtil::exp(1.0), 1e-12);
        self::assertEqualsWithDelta(1.0, MathUtil::exp(0.0), 1e-15);
    }

    public function testLogNatural(): void
    {
        self::assertEqualsWithDelta(1.0, MathUtil::log(M_E), 1e-15);
    }

    public function testLogBase(): void
    {
        self::assertEqualsWithDelta(3.0, MathUtil::log(8.0, 2.0), 1e-12);
    }

    public function testLog10(): void
    {
        self::assertEqualsWithDelta(2.0, MathUtil::log10(100.0), 1e-15);
    }

    // ──────────────────────────────────────────
    //  Trigonometry
    // ──────────────────────────────────────────

    public function testCos(): void
    {
        self::assertEqualsWithDelta(1.0, MathUtil::cos(0.0), 1e-15);
        self::assertEqualsWithDelta(-1.0, MathUtil::cos(M_PI), 1e-15);
    }

    public function testSin(): void
    {
        self::assertEqualsWithDelta(0.0, MathUtil::sin(0.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::sin(M_PI / 2), 1e-15);
    }

    public function testTan(): void
    {
        self::assertEqualsWithDelta(0.0, MathUtil::tan(0.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::tan(M_PI / 4), 1e-12);
    }

    public function testAcos(): void
    {
        self::assertEqualsWithDelta(0.0, MathUtil::acos(1.0), 1e-15);
        self::assertEqualsWithDelta(M_PI, MathUtil::acos(-1.0), 1e-15);
    }

    public function testAsin(): void
    {
        self::assertEqualsWithDelta(M_PI / 2, MathUtil::asin(1.0), 1e-15);
    }

    public function testAtan(): void
    {
        self::assertEqualsWithDelta(M_PI / 4, MathUtil::atan(1.0), 1e-15);
    }

    public function testAtan2(): void
    {
        self::assertEqualsWithDelta(M_PI / 4, MathUtil::atan2(1.0, 1.0), 1e-15);
        self::assertEqualsWithDelta(M_PI / 2, MathUtil::atan2(1.0, 0.0), 1e-15);
    }

    // ──────────────────────────────────────────
    //  Clamping & range
    // ──────────────────────────────────────────

    public function testClamp(): void
    {
        self::assertSame(5.0, MathUtil::clamp(10.0, 0.0, 5.0));
        self::assertSame(0.0, MathUtil::clamp(-3.0, 0.0, 5.0));
        self::assertSame(3.0, MathUtil::clamp(3.0, 0.0, 5.0));
    }

    public function testClamp01(): void
    {
        self::assertSame(0.0, MathUtil::clamp01(-0.5));
        self::assertSame(1.0, MathUtil::clamp01(1.5));
        self::assertSame(0.5, MathUtil::clamp01(0.5));
    }

    public function testMinTwoValues(): void
    {
        self::assertSame(1.0, MathUtil::min(1.0, 2.0));
        self::assertSame(-5.0, MathUtil::min(3.0, -5.0));
    }

    public function testMinMultipleValues(): void
    {
        self::assertSame(-2.0, MathUtil::min(5.0, 3.0, -2.0, 10.0));
    }

    public function testMaxTwoValues(): void
    {
        self::assertSame(2.0, MathUtil::max(1.0, 2.0));
    }

    public function testMaxMultipleValues(): void
    {
        self::assertSame(10.0, MathUtil::max(5.0, 3.0, -2.0, 10.0));
    }

    public function testRepeat(): void
    {
        // 5.5 mod 3 => 2.5
        self::assertEqualsWithDelta(2.5, MathUtil::repeat(5.5, 3.0), 1e-12);
        // Negative input wraps.
        self::assertEqualsWithDelta(2.0, MathUtil::repeat(-1.0, 3.0), 1e-12);
        // Zero length.
        self::assertSame(0.0, MathUtil::repeat(5.0, 0.0));
    }

    public function testPingPong(): void
    {
        // t=0 → 0, t=length → length, t=2*length → 0.
        self::assertEqualsWithDelta(0.0, MathUtil::pingPong(0.0, 3.0), 1e-12);
        self::assertEqualsWithDelta(3.0, MathUtil::pingPong(3.0, 3.0), 1e-12);
        self::assertEqualsWithDelta(0.0, MathUtil::pingPong(6.0, 3.0), 1e-12);
        // Halfway back.
        self::assertEqualsWithDelta(1.5, MathUtil::pingPong(4.5, 3.0), 1e-12);
    }

    public function testWrap(): void
    {
        self::assertEqualsWithDelta(2.0, MathUtil::wrap(5.0, 0.0, 3.0), 1e-12);
        self::assertEqualsWithDelta(0.0, MathUtil::wrap(0.0, 0.0, 3.0), 1e-12);
        self::assertEqualsWithDelta(0.0, MathUtil::wrap(3.0, 0.0, 3.0), 1e-12);
        self::assertEqualsWithDelta(2.0, MathUtil::wrap(-1.0, 0.0, 3.0), 1e-12);
    }

    // ──────────────────────────────────────────
    //  Interpolation
    // ──────────────────────────────────────────

    public function testLerp(): void
    {
        self::assertEqualsWithDelta(5.0, MathUtil::lerp(0.0, 10.0, 0.5), 1e-15);
        // Clamped.
        self::assertEqualsWithDelta(10.0, MathUtil::lerp(0.0, 10.0, 2.0), 1e-15);
        self::assertEqualsWithDelta(0.0, MathUtil::lerp(0.0, 10.0, -1.0), 1e-15);
    }

    public function testLerpUnclamped(): void
    {
        self::assertEqualsWithDelta(20.0, MathUtil::lerpUnclamped(0.0, 10.0, 2.0), 1e-12);
        self::assertEqualsWithDelta(-10.0, MathUtil::lerpUnclamped(0.0, 10.0, -1.0), 1e-12);
    }

    public function testLerpAngle(): void
    {
        // 350 → 10 should go +20 (via 0), not −340.
        // The delta is +20, so midpoint is 350 + 10 = 360 (equivalent to 0°).
        self::assertEqualsWithDelta(360.0, MathUtil::lerpAngle(350.0, 10.0, 0.5), 1e-10);
        // Full interpolation.
        self::assertEqualsWithDelta(370.0, MathUtil::lerpAngle(350.0, 10.0, 1.0), 1e-10);
        // No interpolation.
        self::assertEqualsWithDelta(350.0, MathUtil::lerpAngle(350.0, 10.0, 0.0), 1e-10);
    }

    public function testInverseLerp(): void
    {
        self::assertEqualsWithDelta(0.5, MathUtil::inverseLerp(0.0, 10.0, 5.0), 1e-15);
        self::assertEqualsWithDelta(0.0, MathUtil::inverseLerp(0.0, 10.0, -5.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::inverseLerp(0.0, 10.0, 20.0), 1e-15);
    }

    public function testInverseLerpEqualBounds(): void
    {
        self::assertSame(0.0, MathUtil::inverseLerp(5.0, 5.0, 5.0));
    }

    public function testSmoothStep(): void
    {
        // At boundaries.
        self::assertEqualsWithDelta(0.0, MathUtil::smoothStep(0.0, 10.0, 0.0), 1e-15);
        self::assertEqualsWithDelta(10.0, MathUtil::smoothStep(0.0, 10.0, 1.0), 1e-15);
        // Mid-point (smoothstep of 0.5 = 0.5).
        self::assertEqualsWithDelta(5.0, MathUtil::smoothStep(0.0, 10.0, 0.5), 1e-12);
    }

    public function testMoveTowards(): void
    {
        self::assertEqualsWithDelta(3.0, MathUtil::moveTowards(1.0, 10.0, 2.0), 1e-15);
        // Already close enough → snap to target.
        self::assertEqualsWithDelta(10.0, MathUtil::moveTowards(9.5, 10.0, 2.0), 1e-15);
    }

    public function testMoveTowardsAngle(): void
    {
        // 350 → 10 should step by +5 through 0, yielding 355.
        self::assertEqualsWithDelta(355.0, MathUtil::moveTowardsAngle(350.0, 10.0, 5.0), 1e-10);
    }

    public function testSmoothDamp(): void
    {
        $velocity = 0.0;
        $current  = 0.0;

        // Simulate 100 frames at 60 fps.
        for ($i = 0; $i < 100; $i++) {
            $current = MathUtil::smoothDamp($current, 10.0, $velocity, 0.3, 1.0 / 60.0);
        }

        // Should be very close to the target after ~1.7 seconds.
        self::assertEqualsWithDelta(10.0, $current, 0.01);
    }

    public function testSmoothDampAngle(): void
    {
        $velocity = 0.0;
        $current  = 350.0;

        for ($i = 0; $i < 100; $i++) {
            $current = MathUtil::smoothDampAngle($current, 10.0, $velocity, 0.3, 1.0 / 60.0);
        }

        // The raw result converges to 370 (350 + deltaAngle(350,10) = 370),
        // which is equivalent to 10° but not normalised.
        $normalised = MathUtil::repeat($current, 360.0);
        self::assertEqualsWithDelta(10.0, $normalised, 0.1);
    }

    // ──────────────────────────────────────────
    //  Utility
    // ──────────────────────────────────────────

    public function testApproximately(): void
    {
        self::assertTrue(MathUtil::approximately(1.0, 1.0));
        self::assertTrue(MathUtil::approximately(1.0, 1.0 + 1e-7));
        self::assertFalse(MathUtil::approximately(1.0, 2.0));
        // Near zero.
        self::assertTrue(MathUtil::approximately(0.0, 0.0));
    }

    public function testDeltaAngle(): void
    {
        self::assertEqualsWithDelta(20.0, MathUtil::deltaAngle(350.0, 10.0), 1e-10);
        self::assertEqualsWithDelta(-20.0, MathUtil::deltaAngle(10.0, 350.0), 1e-10);
        self::assertEqualsWithDelta(0.0, MathUtil::deltaAngle(90.0, 90.0), 1e-15);
    }

    public function testIsPowerOfTwo(): void
    {
        self::assertTrue(MathUtil::isPowerOfTwo(1));
        self::assertTrue(MathUtil::isPowerOfTwo(2));
        self::assertTrue(MathUtil::isPowerOfTwo(1024));
        self::assertFalse(MathUtil::isPowerOfTwo(0));
        self::assertFalse(MathUtil::isPowerOfTwo(3));
        self::assertFalse(MathUtil::isPowerOfTwo(-4));
    }

    public function testNextPowerOfTwo(): void
    {
        self::assertSame(1, MathUtil::nextPowerOfTwo(0));
        self::assertSame(1, MathUtil::nextPowerOfTwo(1));
        self::assertSame(4, MathUtil::nextPowerOfTwo(3));
        self::assertSame(8, MathUtil::nextPowerOfTwo(5));
        self::assertSame(1024, MathUtil::nextPowerOfTwo(1000));
    }

    public function testClosestPowerOfTwo(): void
    {
        self::assertSame(4, MathUtil::closestPowerOfTwo(3));
        self::assertSame(4, MathUtil::closestPowerOfTwo(5));
        self::assertSame(8, MathUtil::closestPowerOfTwo(6));
        self::assertSame(1024, MathUtil::closestPowerOfTwo(1000));
    }

    public function testPerlinNoiseIsDeterministic(): void
    {
        $a = MathUtil::perlinNoise(3.14, 2.72);
        $b = MathUtil::perlinNoise(3.14, 2.72);
        self::assertSame($a, $b);
    }

    public function testPerlinNoiseRange(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $v = MathUtil::perlinNoise($i * 0.1, $i * 0.07);
            self::assertGreaterThanOrEqual(0.0, $v);
            self::assertLessThanOrEqual(1.0, $v);
        }
    }

    public function testPerlinNoise1D(): void
    {
        self::assertSame(
            MathUtil::perlinNoise(1.5, 0.0),
            MathUtil::perlinNoise1D(1.5),
        );
    }

    public function testFloatToHalfAndBack(): void
    {
        $testValues = [0.0, 1.0, -1.0, 0.5, 65504.0, -65504.0];

        foreach ($testValues as $value) {
            $half    = MathUtil::floatToHalf($value);
            $roundTrip = MathUtil::halfToFloat($half);
            self::assertEqualsWithDelta($value, $roundTrip, 0.001, "Round-trip failed for $value");
        }
    }

    public function testFloatToHalfInfinity(): void
    {
        $half = MathUtil::floatToHalf(INF);
        self::assertSame(INF, MathUtil::halfToFloat($half));
    }

    public function testGammaToLinearSpace(): void
    {
        // sRGB 0 → linear 0; sRGB 1 → linear 1.
        self::assertEqualsWithDelta(0.0, MathUtil::gammaToLinearSpace(0.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::gammaToLinearSpace(1.0), 1e-6);
        // Mid-range: sRGB 0.5 ≈ linear 0.214.
        self::assertEqualsWithDelta(0.214, MathUtil::gammaToLinearSpace(0.5), 0.01);
    }

    public function testLinearToGammaSpace(): void
    {
        self::assertEqualsWithDelta(0.0, MathUtil::linearToGammaSpace(0.0), 1e-15);
        self::assertEqualsWithDelta(1.0, MathUtil::linearToGammaSpace(1.0), 1e-6);
    }

    public function testGammaLinearRoundTrip(): void
    {
        $original = 0.73;
        $linear   = MathUtil::gammaToLinearSpace($original);
        $gamma    = MathUtil::linearToGammaSpace($linear);

        self::assertEqualsWithDelta($original, $gamma, 1e-6);
    }

    public function testCorrelatedColorTemperatureToRGB(): void
    {
        // 6500 K (D65 daylight) should be very close to white.
        $rgb = MathUtil::correlatedColorTemperatureToRGB(6500.0);

        self::assertGreaterThan(0.9, $rgb['r']);
        self::assertGreaterThan(0.9, $rgb['g']);
        self::assertGreaterThan(0.9, $rgb['b']);

        // Very warm (2000 K) → strong red, weak blue.
        $warm = MathUtil::correlatedColorTemperatureToRGB(2000.0);
        self::assertGreaterThan($warm['b'], $warm['r']);
    }

    public function testCorrelatedColorTemperatureChannelsInRange(): void
    {
        foreach ([1000.0, 3000.0, 6500.0, 10000.0, 40000.0] as $k) {
            $rgb = MathUtil::correlatedColorTemperatureToRGB($k);
            self::assertGreaterThanOrEqual(0.0, $rgb['r']);
            self::assertLessThanOrEqual(1.0, $rgb['r']);
            self::assertGreaterThanOrEqual(0.0, $rgb['g']);
            self::assertLessThanOrEqual(1.0, $rgb['g']);
            self::assertGreaterThanOrEqual(0.0, $rgb['b']);
            self::assertLessThanOrEqual(1.0, $rgb['b']);
        }
    }
}
