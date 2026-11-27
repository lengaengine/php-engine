<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Sets how much friction and bounce a 2D collider has when it touches another collider.
 *
 * Assign one to a 2D collider's `material` property, for example
 * {@see BoxCollider2D::$material} or {@see CircleCollider2D::$material}.
 * When two colliders touch, their values are combined with the combine
 * modes: `Average`, `Minimum`, `Maximum` or `Multiply`.
 *
 * ```php
 * $collider = $this->gameObject->getComponent(CircleCollider2D::class);
 * $collider->material = new PhysicsMaterial2D(friction: 0.2, bounciness: 0.9);
 * ```
 *
 * Reading a collider's `material` gives a copy; assign a material to change
 * the collider. A material with an {@see PhysicsMaterial2D::$assetPath}
 * makes the collider use that asset and ignore the material's other values,
 * so set `assetPath` to `''` on a copy whose values you changed.
 */
final class PhysicsMaterial2D
{
    /**
     * Creates a 2D physics material.
     *
     * @param float $friction How strongly surfaces resist sliding against each other. `0.0` is frictionless.
     * @param float $bounciness How much speed is kept after a bounce. `0.0` does not bounce; `1.0` bounces back at full speed.
     * @param string $frictionCombine How this friction combines with the other collider's: `Average`, `Minimum`, `Maximum` or `Multiply`.
     * @param string $bounceCombine How this bounciness combines with the other collider's: `Average`, `Minimum`, `Maximum` or `Multiply`.
     * @param string $assetPath The project path of the physics material asset this material came from, or `''` for a material made in code. A collider assigned a material with an asset path uses that asset.
     */
    public function __construct(
        public float $friction = 0.0,
        public float $bounciness = 0.0,
        public string $frictionCombine = 'Average',
        public string $bounceCombine = 'Average',
        public string $assetPath = '',
    ) {
    }

    /**
     * Creates a material from the material data a 2D collider reports.
     *
     * @internal Used by the 2D colliders to build their `material` value.
     *
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
