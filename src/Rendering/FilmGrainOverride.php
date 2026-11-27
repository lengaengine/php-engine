<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

/**
 * Overrides film grain in the scene. A null field keeps the existing setting.
 *
 * Pass it as the `filmGrain` module of a {@see PostProcessingOverride}.
 */
final readonly class FilmGrainOverride
{
    /**
     * Creates a film grain override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether film grain is enabled. `false` turns the grain off only when the layer weight is 1; at a lower weight it fades the intensity instead.
     * @param float|null $intensity The grain strength, from 0 to 1.
     * @param float|null $response How strongly scene brightness affects the grain, from 0 to 1.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $intensity = null,
        public ?float $response = null,
    ) {
    }
}
