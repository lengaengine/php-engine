<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Enumerations\TonemappingMode;

/** Selects a tonemapping mode for the active scene. A null field keeps the existing setting. */
final readonly class TonemappingOverride
{
    /**
     * @param bool|null $enabled Whether tonemapping is enabled.
     * @param TonemappingMode|null $mode The curve used to map scene colors.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?TonemappingMode $mode = null,
    ) {
    }
}
