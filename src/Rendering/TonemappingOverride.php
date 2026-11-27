<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Enumerations\TonemappingMode;

/**
 * Selects a tonemapping mode for the active scene. A null field keeps the existing setting.
 *
 * Pass it as the `tonemapping` module of a {@see PostProcessingOverride}. Tonemapping
 * settings switch as soon as their layer has a weight above 0; they do not blend.
 */
final readonly class TonemappingOverride
{
    /**
     * Creates a tonemapping override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether tonemapping is enabled.
     * @param TonemappingMode|null $mode The curve used to map scene colors to the display.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?TonemappingMode $mode = null,
    ) {
    }
}
