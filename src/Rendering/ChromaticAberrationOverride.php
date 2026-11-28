<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

/**
 * Overrides color-channel separation near the edges of the scene. A null field keeps the existing setting.
 *
 * Pass it as the `chromaticAberration` module of a {@see PostProcessingOverride}.
 */
final readonly class ChromaticAberrationOverride
{
    /**
     * Creates a chromatic aberration override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether chromatic aberration is enabled. `false` turns the effect off only when the layer weight is 1; at a lower weight it fades the intensity instead.
     * @param float|null $intensity The amount of channel separation, from 0 to 1.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $intensity = null,
    ) {
    }
}
