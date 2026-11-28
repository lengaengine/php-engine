<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/**
 * Adjusts the scene's exposure and colors. A null field keeps the existing setting.
 *
 * Pass it as the `colorAdjustments` module of a {@see PostProcessingOverride}.
 */
final readonly class ColorAdjustmentsOverride
{
    /**
     * Creates a color adjustments override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether color adjustments are enabled. `false` fades the current adjustments back to neutral by the layer weight.
     * @param float|null $postExposure Exposure adjustment in stops. 0 leaves the brightness unchanged and 1 doubles it.
     * @param float|null $contrast Contrast adjustment, from -100 to 100. 0 leaves the contrast unchanged.
     * @param float|null $saturation Saturation adjustment, from -100 to 100. 0 leaves the saturation unchanged and -100 is greyscale.
     * @param Color|null $colorFilter Color multiplied over the scene. White leaves the colors unchanged.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $postExposure = null,
        public ?float $contrast = null,
        public ?float $saturation = null,
        public ?Color $colorFilter = null,
    ) {
    }
}
