<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/** Distorts the edges of the rendered scene while leaving the HUD untouched. A null field keeps the existing setting. */
final readonly class PeripheralWarpOverride
{
    /**
     * @param bool|null $enabled Whether peripheral warp is enabled.
     * @param float|null $intensity The blend between the original and warped scene.
     * @param float|null $safeRadius Radius of the undistorted center.
     * @param float|null $smoothness Width of the transition beyond the safe radius.
     * @param float|null $rippleAmplitude Displacement from the ripple.
     * @param float|null $rippleFrequency Frequency of the ripple.
     * @param float|null $phase Ripple phase in radians.
     * @param float|null $chromaticSeparation Color-channel separation in the warped region.
     * @param Color|null $outerTint Tint near the edge of the frame.
     * @param Color|null $innerTint Tint near the inner edge of the warped region.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $intensity = null,
        public ?float $safeRadius = null,
        public ?float $smoothness = null,
        public ?float $rippleAmplitude = null,
        public ?float $rippleFrequency = null,
        public ?float $phase = null,
        public ?float $chromaticSeparation = null,
        public ?Color $outerTint = null,
        public ?Color $innerTint = null,
    ) {
    }
}
