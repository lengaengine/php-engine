<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/**
 * Overrides the glow produced by bright scene pixels. A null field keeps the existing setting.
 *
 * Pass it as the `bloom` module of a {@see PostProcessingOverride}.
 */
final readonly class BloomOverride
{
    /**
     * Creates a bloom override. Set only the fields you want to change.
     *
     * @param bool|null $enabled Whether bloom is enabled. `false` turns bloom off only when the layer weight is 1; at a lower weight it fades the bloom intensity instead.
     * @param float|null $threshold The brightness above which pixels glow. Values below 0 are treated as 0.
     * @param float|null $intensity The strength of the bloom. Values below 0 are treated as 0.
     * @param float|null $scatter How far the glow spreads, from 0 to 1.
     * @param Color|null $tint Color of the bloom.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?float $threshold = null,
        public ?float $intensity = null,
        public ?float $scatter = null,
        public ?Color $tint = null,
    ) {
    }
}
