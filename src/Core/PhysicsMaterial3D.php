<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_string;

/**
 * Sets how much friction and bounce a 3D collider has when it touches another collider.
 *
 * Use it for surfaces that need their own feel, such as ice, rubber or a
 * bumper. Assign one to a 3D collider's `material` property, for example
 * {@see SphereCollider3D::$material}. When two colliders touch, their values
 * are combined with the combine modes: `Average`, `Minimum`, `Maximum` or
 * `Multiply`.
 *
 * ```php
 * $collider = $this->gameObject->getComponent(SphereCollider3D::class);
 * $collider->material = new PhysicsMaterial3D(
 *     dynamicFriction: 0.2,
 *     staticFriction: 0.2,
 *     bounciness: 0.85,
 * );
 * ```
 *
 * For materials shared across a project, create a Physics Material 3D asset
 * in the editor and assign it to the collider's Material field.
 *
 * Reading a collider's `material` gives a copy; assign a material to change
 * the collider. A material with an {@see PhysicsMaterial3D::$assetPath}
 * makes the collider use that asset and ignore the material's other values,
 * so set `assetPath` to `''` on a copy whose values you changed.
 */
final class PhysicsMaterial3D
{
    /**
     * Creates a 3D physics material.
     *
     * @param float $dynamicFriction Friction while surfaces are already sliding across each other. `0.0` is frictionless.
     * @param float $staticFriction Friction that holds a resting body in place until it starts to slide.
     * @param float $bounciness How much speed is kept after a bounce. `0.0` does not bounce; `1.0` bounces back at full speed.
     * @param string $frictionCombine How this friction combines with the other collider's: `Average`, `Minimum`, `Maximum` or `Multiply`.
     * @param string $bounceCombine How this bounciness combines with the other collider's: `Average`, `Minimum`, `Maximum` or `Multiply`.
     * @param string $assetPath The project path of the physics material asset this material came from, or `''` for a material made in code. A collider assigned a material with an asset path uses that asset.
     */
    public function __construct(
        public float $dynamicFriction = 0.6,
        public float $staticFriction = 0.6,
        public float $bounciness = 0.0,
        public string $frictionCombine = 'Average',
        public string $bounceCombine = 'Average',
        public string $assetPath = '',
    ) {}

    /**
     * Creates a material from the material data a 3D collider reports.
     *
     * @internal Used by the 3D colliders to build their `material` value.
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
