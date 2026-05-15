<?php
declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Color;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Color class.
 *
 * Covers construction, property access, value clamping, and factory methods.
 */
final class ColorTest extends TestCase
{
    // ──────────────────────────────────────────
    //  Constructor & Default Values
    // ──────────────────────────────────────────

    public function testConstructorWithDefaultValues(): void
    {
        $color = new Color();

        self::assertEquals(0, $color->r);
        self::assertEquals(0, $color->g);
        self::assertEquals(0, $color->b);
        self::assertEquals(1, $color->a);
    }

    public function testConstructorWithAllValues(): void
    {
        $color = new Color(1, 0.5, 0.25, 0.13);

        self::assertEquals(1.0, $color->r);
        self::assertEquals(0.5, $color->g);
        self::assertEquals(0.25, $color->b);
        self::assertEquals(0.13, $color->a);
    }

    public function testConstructorWithPartialValues(): void
    {
        $color = new Color(0.78, 0.39);

        self::assertEquals(0.78, $color->r);
        self::assertEquals(0.39, $color->g);
        self::assertEquals(0.0, $color->b);
        self::assertEquals(1, $color->a);
    }

    // ──────────────────────────────────────────
    //  Value Clamping (0-255)
    // ──────────────────────────────────────────

    public function testRedChannelClamping(): void
    {
        $color = new Color(1.18, 0, 0, 0);
        self::assertEquals(1.0, $color->r);

        $color = new Color(-0.2, 0, 0, 0);
        self::assertEquals(0, $color->r);
    }

    public function testGreenChannelClamping(): void
    {
        $color = new Color(0, 1.18, 0, 0);
        self::assertEquals(1.0, $color->g);

        $color = new Color(0, -0.2, 0, 0);
        self::assertEquals(0, $color->g);
    }

    public function testBlueChannelClamping(): void
    {
        $color = new Color(0, 0, 1.96, 0);
        self::assertEquals(1.0, $color->b);

        $color = new Color(0, 0, -0.39, 0);
        self::assertEquals(0, $color->b);
    }

    public function testAlphaChannelClamping(): void
    {
        $color = new Color(0, 0, 0, 3.92);
        self::assertEquals(1.0, $color->a);

        $color = new Color(0, 0, 0, -0.78);
        self::assertEquals(0, $color->a);
    }

    public function testAllChannelsClampedBoundaryValues(): void
    {
        // Test boundary values: just below 0, at 0, at 255, just above 255
        $color = new Color(-(1/255), (256/255), 0, 1.0);

        self::assertEquals(0.0, $color->r);
        self::assertEquals(1.0, $color->g);
        self::assertEquals(0.0, $color->b);
        self::assertEquals(1.0, $color->a);
    }

    // ──────────────────────────────────────────
    //  Property Read-Only Access
    // ──────────────────────────────────────────

    public function testRedPropertyReadOnly(): void
    {
        $color = new Color(0.5, 0, 0, 0);

        // Properties are backed by hooks - we can only access them
        self::assertEquals(0.5, $color->r);
    }

    public function testGreenPropertyReadOnly(): void
    {
        $color = new Color(0, 0.78, 0, 0);
        self::assertEquals(0.78, $color->g);
    }

    public function testBluePropertyReadOnly(): void
    {
        $color = new Color(0, 0, 0.59, 0);
        self::assertEquals(0.59, $color->b);
    }

    public function testAlphaPropertyReadOnly(): void
    {
        $color = new Color(0, 0, 0, 0.29);
        self::assertEquals(0.29, $color->a);
    }

    // ──────────────────────────────────────────
    //  Factory Methods
    // ──────────────────────────────────────────

    public function testBlackFactoryMethod(): void
    {
        $black = Color::black();

        self::assertEquals(0, $black->r);
        self::assertEquals(0, $black->g);
        self::assertEquals(0, $black->b);
        // Note: The current implementation has 2550 which clamps to 255
        self::assertEquals(1.0, $black->a);
    }

    // ──────────────────────────────────────────
    //  Combined Tests
    // ──────────────────────────────────────────

    public function testMultipleColorsIndependent(): void
    {
        $red = new Color(1.0, 0, 0, 1.0);
        $green = new Color(0, 1.0, 0, 1.0);
        $blue = new Color(0, 0, 1.0, 1.0);

        self::assertEquals(1.0, $red->r);
        self::assertEquals(0, $red->g);
        self::assertEquals(0, $red->b);

        self::assertEquals(0, $green->r);
        self::assertEquals(1.0, $green->g);
        self::assertEquals(0, $green->b);

        self::assertEquals(0, $blue->r);
        self::assertEquals(0, $blue->g);
        self::assertEquals(1.0, $blue->b);
    }

    public function testExtremeValuesAreClamped(): void
    {
        $color = new Color(
            PHP_INT_MAX ,
            PHP_INT_MIN,
            999999,
            -999999,
        );

        self::assertEquals(1.0, $color->r);
        self::assertEquals(0.0, $color->g);
        self::assertEquals(1.0, $color->b);
        self::assertEquals(0.0, $color->a);
    }
}