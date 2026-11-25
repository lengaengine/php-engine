<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\Color;

/** Overrides the glow produced by bright scene pixels. A null field keeps the existing setting. */
final readonly class BloomOverride
{
    /**
     * @param bool|null $enabled Whether bloom is enabled.
     * @param float|null $threshold The brightness threshold for bloom.
     * @param float|null $intensity The strength of the bloom.
     * @param float|null $scatter The bloom spread.
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
