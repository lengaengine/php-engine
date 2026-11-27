<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Makes the GameObject's 2D collider act as a fluid that floats, slows and carries 2D rigidbodies.
 *
 * Add it next to a {@see BoxCollider2D} to make water, lava or another
 * pool. Each fixed step, every {@see Rigidbody2D} whose colliders reach
 * below the fluid surface is pushed up in proportion to how much of its
 * height is submerged and to {@see BuoyancyEffector2D::$density}. The fluid
 * also slows the body with {@see BuoyancyEffector2D::$linearDrag} and can
 * carry it with a flow. Make the collider a trigger so bodies can enter
 * the fluid.
 */
final class BuoyancyEffector2D extends Component
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'BuoyancyEffector2D');
    }

    /**
     * How strongly the fluid pushes submerged bodies up.
     *
     * At `1` a fully submerged body is pushed up as strongly as the default
     * 2D gravity of 980 pulls it down, whatever its mass. Higher values
     * float bodies higher. Values below zero are treated as zero. Defaults
     * to `1`.
     */
    public float $density {
        get {
            return (float) ($this->getState()['density'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('buoyancy_effector2d_set_density', $this->componentId, $value);
        }
    }

    /**
     * How strongly the fluid slows submerged bodies, scaled by how much of each body is submerged.
     *
     * Values below zero are treated as zero. Defaults to `1`.
     */
    public float $linearDrag {
        get {
            return (float) ($this->getState()['linearDrag'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('buoyancy_effector2d_set_linear_drag', $this->componentId, $value);
        }
    }

    /**
     * Direction of the flow that carries submerged bodies, in degrees.
     *
     * `0` flows along positive X (right) and `90` along positive Y, down the
     * screen. The angle turns with the GameObject's Z rotation. Defaults to `0`.
     */
    public float $flowAngle {
        get {
            return (float) ($this->getState()['flowAngle'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('buoyancy_effector2d_set_flow_angle', $this->componentId, $value);
        }
    }

    /**
     * Strength of the flow, scaled by how much of each body is submerged.
     *
     * Zero, the default, turns the flow off. Negative values flow the
     * opposite way.
     */
    public float $flowMagnitude {
        get {
            return (float) ($this->getState()['flowMagnitude'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('buoyancy_effector2d_set_flow_magnitude', $this->componentId, $value);
        }
    }

    /**
     * Depth of the fluid surface below the top edge of the GameObject's collider, in world units.
     *
     * At `0` the whole collider is fluid. Larger values lower the surface;
     * the surface never goes below the bottom of the collider. Defaults to `0`.
     */
    public float $surfaceLevel {
        get {
            return (float) ($this->getState()['surfaceLevel'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('buoyancy_effector2d_set_surface_level', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     density?: float|int,
     *     linearDrag?: float|int,
     *     flowAngle?: float|int,
     *     flowMagnitude?: float|int,
     *     surfaceLevel?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     density?: float|int,
         *     linearDrag?: float|int,
         *     flowAngle?: float|int,
         *     flowMagnitude?: float|int,
         *     surfaceLevel?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('buoyancy_effector2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
