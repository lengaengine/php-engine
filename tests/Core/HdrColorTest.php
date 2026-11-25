<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\HdrColor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HdrColorTest extends TestCase
{
    private const float DELTA = 1e-6;

    public function testDefaultsMatchTheEngine(): void
    {
        $value = new HdrColor();

        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255], $value->color->toRGBA());
        self::assertSame(0.0, $value->intensity);
        self::assertSame(0.0, HdrColor::NEUTRAL_INTENSITY);
    }

    /**
     * Expected values are the sRGB decode of (255, 128, 10) times 2^intensity,
     * computed outside PHP. 128 lies on the curve and 10 on the straight segment.
     *
     * @return array<string, array{float, float, float, float, float}>
     */
    public static function intensities(): array
    {
        return [
            'minus two stops' => [-2.0, 0.25, 0.25, 0.053965125028474815, 0.0007588174588720937],
            'neutral' => [0.0, 1.0, 1.0, 0.21586050011389926, 0.003035269835488375],
            'one stop' => [1.0, 2.0, 2.0, 0.4317210002277985, 0.00607053967097675],
            'two and a half stops' => [2.5, 5.656854249492381, 5.656854249492381, 1.2210913873668616, 0.017170079067238452],
        ];
    }

    #[DataProvider('intensities')]
    public function testLinearValueIsTheDecodedColourTimesTheIntensityScale(
        float $intensity,
        float $scale,
        float $red,
        float $green,
        float $blue,
    ): void {
        $value = new HdrColor(Color::fromRGBA(255, 128, 10, 64), $intensity);

        $linear = $value->linearValue();

        self::assertEqualsWithDelta($scale, $value->intensityScale(), self::DELTA);
        self::assertEqualsWithDelta($red, $linear->x, self::DELTA);
        self::assertEqualsWithDelta($green, $linear->y, self::DELTA);
        self::assertEqualsWithDelta($blue, $linear->z, self::DELTA);
    }

    public function testAssetValueRoundTrips(): void
    {
        $value = new HdrColor(Color::fromRGBA(255, 128, 10, 64), -1.5);

        $stored = $value->toAssetValue();
        $read = HdrColor::fromAssetValue($stored);

        self::assertSame(['color' => '#FF800A40', 'intensity' => -1.5], $stored);
        self::assertSame($value->color->toRGBA(), $read->color->toRGBA());
        self::assertSame(-1.5, $read->intensity);
    }

    public function testPlainHexReadsAsTheNeutralIntensity(): void
    {
        $read = HdrColor::fromAssetValue('#336699', new HdrColor(Color::black(), 3.0));

        self::assertSame(['r' => 51, 'g' => 102, 'b' => 153, 'a' => 255], $read->color->toRGBA());
        self::assertSame(HdrColor::NEUTRAL_INTENSITY, $read->intensity);
    }

    public function testMissingOrMistypedPartsKeepTheFallback(): void
    {
        $fallback = new HdrColor(Color::red(), 2.0);

        $missingIntensity = HdrColor::fromAssetValue(['color' => '#00FF00'], $fallback);
        $mistypedColor = HdrColor::fromAssetValue(['color' => 12, 'intensity' => 1], $fallback);

        self::assertSame(['r' => 0, 'g' => 255, 'b' => 0, 'a' => 255], $missingIntensity->color->toRGBA());
        self::assertSame(2.0, $missingIntensity->intensity);
        self::assertSame(Color::red()->toRGBA(), $mistypedColor->color->toRGBA());
        self::assertSame(1.0, $mistypedColor->intensity);
        self::assertSame($fallback, HdrColor::fromAssetValue(null, $fallback));
    }
}
