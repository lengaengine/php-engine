<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_string;

/**
 * Defines the friction and bounce response used by a 3D physics collider.
 */
final class PhysicsMaterial3D
{
    public function __construct(
        public float $dynamicFriction = 0.6,
        public float $staticFriction = 0.6,
        public float $bounciness = 0.0,
        public string $frictionCombine = 'Average',
        public string $bounceCombine = 'Average',
        public string $assetPath = '',
    ) {}

    /**
     * Creates a material instance from native collider state.
     *
     * @param array{
     *     dynamicFriction?: float|int,
     *     staticFriction?: float|int,
     *     friction?: float|int,
     *     bounciness?: float|int,
     *     frictionCombine?: string,
     *     bounceCombine?: string
     * } $state
     */
    public static function fromArray(array $state, string $assetPath = ''): self
    {
        $dynamicFriction = (float) ($state['dynamicFriction'] ?? $state['friction'] ?? 0.6);
        $staticFriction = (float) ($state['staticFriction'] ?? $dynamicFriction);

        return new self(
            dynamicFriction: $dynamicFriction,
            staticFriction: $staticFriction,
            bounciness: (float) ($state['bounciness'] ?? 0.0),
            frictionCombine: is_string($state['frictionCombine'] ?? null) ? $state['frictionCombine'] : 'Average',
            bounceCombine: is_string($state['bounceCombine'] ?? null) ? $state['bounceCombine'] : 'Average',
            assetPath: $assetPath,
        );
    }
}
