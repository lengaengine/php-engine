<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class PhysicsMaterial2D
{
    public function __construct(
        public float $friction = 0.0,
        public float $bounciness = 0.0,
        public string $frictionCombine = 'Average',
        public string $bounceCombine = 'Average',
        public string $assetPath = '',
    ) {
    }

    /**
     * @param array{
     *     friction?: float|int,
     *     bounciness?: float|int,
     *     frictionCombine?: string,
     *     bounceCombine?: string
     * } $state
     */
    public static function fromArray(array $state, string $assetPath = ''): self
    {
        return new self(
            friction: (float) ($state['friction'] ?? 0.0),
            bounciness: (float) ($state['bounciness'] ?? 0.0),
            frictionCombine: (string) ($state['frictionCombine'] ?? 'Average'),
            bounceCombine: (string) ($state['bounceCombine'] ?? 'Average'),
            assetPath: $assetPath,
        );
    }
}
