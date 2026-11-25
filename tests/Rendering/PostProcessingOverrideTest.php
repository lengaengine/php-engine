<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Rendering;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\TonemappingMode;
use Lenga\Engine\Exceptions\LengaRuntimeUnavailableException;
use Lenga\Engine\Rendering\BloomOverride;
use Lenga\Engine\Rendering\ChromaticAberrationOverride;
use Lenga\Engine\Rendering\ColorAdjustmentsOverride;
use Lenga\Engine\Rendering\FilmGrainOverride;
use Lenga\Engine\Rendering\PeripheralWarpOverride;
use Lenga\Engine\Rendering\PostProcessing;
use Lenga\Engine\Rendering\PostProcessingOverride;
use Lenga\Engine\Rendering\TonemappingOverride;
use Lenga\Engine\Rendering\VignetteOverride;
use PHPUnit\Framework\TestCase;

final class PostProcessingOverrideTest extends TestCase
{
    public function testEncodesOnlyAuthoredFieldsAndPreservesZeroAndFalse(): void
    {
        $tint = Color::fromRGBA(26, 80, 140, 192);
        $layer = new PostProcessingOverride(
            weight: 0.75,
            priority: 3,
            tonemapping: new TonemappingOverride(enabled: false, mode: TonemappingMode::Aces),
            colorAdjustments: new ColorAdjustmentsOverride(postExposure: 0.0, colorFilter: $tint),
            bloom: new BloomOverride(intensity: 0.5),
            vignette: new VignetteOverride(center: new Vector2(0.125, 0.75), rounded: false),
            filmGrain: new FilmGrainOverride(response: 0.0),
            chromaticAberration: new ChromaticAberrationOverride(),
            peripheralWarp: new PeripheralWarpOverride(enabled: true, phase: 0.0, outerTint: $tint),
        );

        self::assertSame([
            'weight' => 0.75,
            'priority' => 3,
            'tonemapping' => ['enabled' => false, 'mode' => 'ACES'],
            'colorAdjustments' => [
                'postExposure' => 0.0,
                'colorFilter' => ['r' => 26, 'g' => 80, 'b' => 140, 'a' => 192],
            ],
            'bloom' => ['intensity' => 0.5],
            'vignette' => ['center' => ['x' => 0.125, 'y' => 0.75], 'rounded' => false],
            'filmGrain' => ['response' => 0.0],
            'peripheralWarp' => [
                'enabled' => true,
                'phase' => 0.0,
                'outerTint' => ['r' => 26, 'g' => 80, 'b' => 140, 'a' => 192],
            ],
        ], $layer->toNativeArray());
    }

    public function testSetPostProcessingOverrideRequiresTheRuntime(): void
    {
        $this->expectException(LengaRuntimeUnavailableException::class);

        PostProcessing::setOverride('timed-effect', new PostProcessingOverride());
    }
}
