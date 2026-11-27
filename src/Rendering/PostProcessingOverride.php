<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\TonemappingMode;

/**
 * One temporary layer of scene post-processing. Null modules and fields leave
 * the authored scene settings or lower-priority layers intact.
 *
 * Apply it with {@see PostProcessing::setOverride()}. Layers apply in order of
 * `priority`, lowest first; layers with the same priority apply in the order
 * they were first added. Most numeric settings blend from the current value
 * toward the layer's value by `weight`. Tonemapping settings, `rounded` and
 * `phase` switch as soon as the layer's weight is above 0.
 *
 * ```php
 * PostProcessing::setOverride('damage-flash', new PostProcessingOverride(
 *     weight: 0.6,
 *     vignette: new VignetteOverride(enabled: true, color: Color::red(), intensity: 0.45),
 * ));
 * ```
 */
final readonly class PostProcessingOverride
{
    /**
     * Creates a post-processing layer. Pass only the modules you want to change.
     *
     * @param float $weight Layer influence from 0 (no effect) to 1 (full override). Values outside that range are clamped.
     * @param int $priority Higher values apply after lower ones.
     * @param TonemappingOverride|null $tonemapping Display color mapping.
     * @param ColorAdjustmentsOverride|null $colorAdjustments Exposure and color adjustments.
     * @param BloomOverride|null $bloom Glow around bright pixels.
     * @param VignetteOverride|null $vignette Shading around the edges of the viewport.
     * @param FilmGrainOverride|null $filmGrain Grain added to the scene.
     * @param ChromaticAberrationOverride|null $chromaticAberration Color-channel separation.
     * @param PeripheralWarpOverride|null $peripheralWarp Distortion around the frame edges.
     */
    public function __construct(
        public float $weight = 1.0,
        public int $priority = 0,
        public ?TonemappingOverride $tonemapping = null,
        public ?ColorAdjustmentsOverride $colorAdjustments = null,
        public ?BloomOverride $bloom = null,
        public ?VignetteOverride $vignette = null,
        public ?FilmGrainOverride $filmGrain = null,
        public ?ChromaticAberrationOverride $chromaticAberration = null,
        public ?PeripheralWarpOverride $peripheralWarp = null,
    ) {
    }

    /**
     * @internal
     * @return array<string, float|int|array<string, bool|float|string|array<string, int|float>>>
     */
    public function toNativeArray(): array
    {
        $payload = ['weight' => $this->weight, 'priority' => $this->priority];
        foreach ([
            'tonemapping' => $this->tonemapping,
            'colorAdjustments' => $this->colorAdjustments,
            'bloom' => $this->bloom,
            'vignette' => $this->vignette,
            'filmGrain' => $this->filmGrain,
            'chromaticAberration' => $this->chromaticAberration,
            'peripheralWarp' => $this->peripheralWarp,
        ] as $name => $module) {
            if ($module === null) {
                continue;
            }
            $fields = [];
            foreach (get_object_vars($module) as $key => $value) {
                if ($value === null) {
                    continue;
                }
                $fields[$key] = match (true) {
                    $value instanceof Color => $value->toRGBA(),
                    $value instanceof Vector2 => ['x' => $value->x, 'y' => $value->y],
                    $value instanceof TonemappingMode => $value->value,
                    default => $value,
                };
            }
            if ($fields !== []) {
                $payload[$name] = $fields;
            }
        }

        return $payload;
    }
}
