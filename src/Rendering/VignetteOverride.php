<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\Vector2;

/**
 * Overrides the vignette around the edges of the scene. A null field keeps the existing setting.
 *
 * Pass it as the `vignette` module of a {@see PostProcessingOverride}.
 */
final readonly class VignetteOverride
{
    /**
     * Creates a vignette override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether the vignette is enabled. `false` turns the vignette off only when the layer weight is 1; at a lower weight it fades the intensity instead.
     * @param Color|null $color Color of the vignette.
     * @param Vector2|null $center Center in normalized viewport coordinates.
     * @param float|null $intensity The vignette strength, from 0 to 1.
     * @param float|null $smoothness The softness of its transition, from 0.01 to 1.
     * @param bool|null $rounded Whether the vignette uses a rounded shape.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?Color $color = null,
        public ?Vector2 $center = null,
        public ?float $intensity = null,
        public ?float $smoothness = null,
        public ?bool $rounded = null,
    ) {
    }
}
