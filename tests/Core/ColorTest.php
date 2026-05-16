<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use InvalidArgumentException;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Internal\ColorBridge;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testConstructorUsesNormalizedChannels(): void
    {
        $color = new Color(1.0, 0.5, 0.25, 0.125);

        self::assertSame(1.0, $color->r);
        self::assertSame(0.5, $color->g);
        self::assertSame(0.25, $color->b);
        self::assertSame(0.125, $color->a);
    }

    public function testConstructorClampsNormalizedChannels(): void
    {
        $color = new Color(1.5, -0.25, 0.75, 2.0);

        self::assertSame(1.0, $color->r);
        self::assertSame(0.0, $color->g);
        self::assertSame(0.75, $color->b);
        self::assertSame(1.0, $color->a);
    }

    public function testFactoryColorsUseExpectedChannels(): void
    {
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255], Color::black()->toRGBA());
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 255], Color::blue()->toRGBA());
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0], Color::clear()->toRGBA());
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 255, 'a' => 255], Color::cyan()->toRGBA());
        self::assertSame(['r' => 128, 'g' => 128, 'b' => 128, 'a' => 255], Color::gray()->toRGBA());
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0, 'a' => 255], Color::green()->toRGBA());
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 255, 'a' => 255], Color::magenta()->toRGBA());
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255], Color::red()->toRGBA());
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255], Color::white()->toRGBA());
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 0, 'a' => 255], Color::yellow()->toRGBA());
    }

    public function testLerpInterpolatesChannelsAndClampsRatio(): void
    {
        self::assertSame(
            ['r' => 128, 'g' => 0, 'b' => 128, 'a' => 255],
            Color::lerp(Color::red(), Color::blue(), 0.5)->toRGBA(),
        );
        self::assertSame(
            ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255],
            Color::lerp(Color::red(), Color::blue(), -1.0)->toRGBA(),
        );
        self::assertSame(
            ['r' => 0, 'g' => 0, 'b' => 255, 'a' => 255],
            Color::lerp(Color::red(), Color::blue(), 2.0)->toRGBA(),
        );
    }

    public function testRgbToHsvConvertsNormalizedChannels(): void
    {
        self::assertSame(['h' => 0, 's' => 100, 'v' => 100], Color::rgbToHsv(1.0, 0.0, 0.0));
        self::assertSame(['h' => 120, 's' => 100, 'v' => 100], Color::rgbToHsv(0.0, 1.0, 0.0));
        self::assertSame(['h' => 240, 's' => 100, 'v' => 100], Color::rgbToHsv(0.0, 0.0, 1.0));
        self::assertSame(['h' => 180, 's' => 100, 'v' => 100], Color::rgbToHsv(0.0, 1.0, 1.0));
        self::assertSame(['h' => 0, 's' => 0, 'v' => 50], Color::rgbToHsv(0.5, 0.5, 0.5));
    }

    public function testRgbToHsvClampsNormalizedInputs(): void
    {
        self::assertSame(['h' => 0, 's' => 100, 'v' => 100], Color::rgbToHsv(1.5, -0.25, 0.0));
    }

    public function testHsvToRgbConvertsPercentChannels(): void
    {
        self::assertSame(['r' => 255, 'g' => 0, 'b' => 0], Color::hsvToRgb(0.0, 100.0, 100.0));
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::hsvToRgb(120.0, 100.0, 100.0));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::hsvToRgb(240.0, 100.0, 100.0));
        self::assertSame(['r' => 128, 'g' => 128, 'b' => 128], Color::hsvToRgb(0.0, 0.0, 50.0));
    }

    public function testHsvToRgbWrapsHueAndClampsPercentChannels(): void
    {
        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0], Color::hsvToRgb(480.0, 150.0, 110.0));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 255], Color::hsvToRgb(-120.0, 100.0, 100.0));
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0], Color::hsvToRgb(0.0, 100.0, -10.0));
    }

    public function testFromRGBAConvertsByteChannelsToNormalizedChannels(): void
    {
        $color = Color::fromRGBA(255, 128, 64, 32);

        self::assertSame(1.0, $color->r);
        self::assertEqualsWithDelta(128 / 255, $color->g, 0.00001);
        self::assertEqualsWithDelta(64 / 255, $color->b, 0.00001);
        self::assertEqualsWithDelta(32 / 255, $color->a, 0.00001);
    }

    public function testFromRGBAClampsByteChannels(): void
    {
        $color = Color::fromRGBA(300, -10, 42, 999);

        self::assertSame(['r' => 255, 'g' => 0, 'b' => 42, 'a' => 255], $color->toRGBA());
    }

    public function testFromRGBAArraySupportsAssociativeAndIndexedChannels(): void
    {
        self::assertSame(
            ['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40],
            Color::fromRGBAArray(['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40])->toRGBA(),
        );
        self::assertSame(
            ['r' => 50, 'g' => 60, 'b' => 70, 'a' => 80],
            Color::fromRGBAArray([50, 60, 70, 80])->toRGBA(),
        );
    }

    public function testToNormalizedRGBAUsesNormalizedChannels(): void
    {
        $color = new Color(0.1, 0.2, 0.3, 0.4);

        self::assertSame(['r' => 0.1, 'g' => 0.2, 'b' => 0.3, 'a' => 0.4], $color->toNormalizedRGBA());
    }

    public function testSerializationRoundTripsRGBAChannels(): void
    {
        $serialized = serialize(Color::fromRGBA(12, 34, 56, 78));

        $color = unserialize($serialized, ['allowed_classes' => [Color::class]]);

        self::assertInstanceOf(Color::class, $color);
        self::assertSame(['r' => 12, 'g' => 34, 'b' => 56, 'a' => 78], $color->toRGBA());
    }

    public function testStringConversionIsReadable(): void
    {
        self::assertSame('rgba(255, 128, 0, 64)', (string) Color::fromRGBA(255, 128, 0, 64));
    }

    public function testColorBridgeNormalizesPublicColorInputs(): void
    {
        self::assertSame(
            ['r' => 255, 'g' => 128, 'b' => 0, 'a' => 64],
            ColorBridge::toNative(Color::fromRGBA(255, 128, 0, 64)),
        );
        self::assertSame(
            ['r' => 1, 'g' => 2, 'b' => 3, 'a' => 4],
            ColorBridge::toNative(['r' => 1, 'g' => 2, 'b' => 3, 'a' => 4]),
        );
        self::assertSame(
            ['r' => 5, 'g' => 6, 'b' => 7, 'a' => 255],
            ColorBridge::toNative(5, 6, 7),
        );
    }

    public function testColorBridgeFallsBackWhenNativeStateIsMissing(): void
    {
        self::assertSame(
            ['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40],
            ColorBridge::fromState(null, Color::fromRGBA(10, 20, 30, 40))->toRGBA(),
        );
        self::assertSame(
            ['r' => 10, 'g' => 99, 'b' => 30, 'a' => 40],
            ColorBridge::fromState(['g' => 99], Color::fromRGBA(10, 20, 30, 40))->toRGBA(),
        );
    }

    public function testColorBridgeRequiresCompleteByteChannelArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ColorBridge::toNative(255);
    }
}
