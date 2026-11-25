<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/** Adjusts the scene's exposure and colors. A null field keeps the existing setting. */
final readonly class ColorAdjustmentsOverride
{
    /**
     * @param bool|null $enabled Whether color adjustments are enabled.
     * @param float|null $postExposure Exposure adjustment in stops.
     * @param float|null $contrast Contrast adjustment.
     * @param float|null $saturation Saturation adjustment.
     * @param Color|null $colorFilter Color applied to the scene.
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
