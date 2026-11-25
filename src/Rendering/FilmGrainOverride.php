<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

/** Overrides film grain in the scene. A null field keeps the existing setting. */
final readonly class FilmGrainOverride
{
    /**
     * @param bool|null $enabled Whether film grain is enabled.
     * @param float|null $intensity The grain strength.
     * @param float|null $response How strongly scene brightness affects the grain.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $intensity = null,
        public ?float $response = null,
    ) {
    }
}
