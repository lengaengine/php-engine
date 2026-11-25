<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

/** Overrides color-channel separation near the edges of the scene. A null field keeps the existing setting. */
final readonly class ChromaticAberrationOverride
{
    /**
     * @param bool|null $enabled Whether chromatic aberration is enabled.
     * @param float|null $intensity The amount of channel separation.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $intensity = null,
    ) {
    }
}
