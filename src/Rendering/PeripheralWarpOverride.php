<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/**
 * Distorts the edges of the rendered scene while leaving the HUD untouched. A null field keeps the existing setting.
 *
 * Pass it as the `peripheralWarp` module of a {@see PostProcessingOverride}. Use it for
 * dizziness, speed or portal effects. To animate the ripple, set a new override with a
 * larger `phase` each frame under the same key.
 */
final readonly class PeripheralWarpOverride
{
    /**
     * Creates a peripheral warp override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether peripheral warp is enabled. `false` turns the effect off only when the layer weight is 1; at a lower weight it fades the intensity instead.
     * @param float|null $intensity The blend between the original and warped scene, from 0 to 1.
     * @param float|null $safeRadius Normalized radius of the undistorted center, from 0 to 1.
     * @param float|null $smoothness Width of the transition beyond the safe radius, from 0.01 to 1.
     * @param float|null $rippleAmplitude Displacement from the ripple, from 0 to 0.1.
     * @param float|null $rippleFrequency Frequency of the ripple, from 0 to 30.
     * @param float|null $phase Ripple phase in radians. Change it over time to animate the ripple.
     * @param float|null $chromaticSeparation Color-channel separation in the warped region, from 0 to 0.05.
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
