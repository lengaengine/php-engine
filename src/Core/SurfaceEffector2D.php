<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * 2D effector that pushes bodies along its surface, like a conveyor belt.
 *
 * Add it to a GameObject with a 2D collider. Each physics step, every
 * {@see Rigidbody2D} whose colliders overlap that collider is pushed toward a target
 * velocity of {@see SurfaceEffector2D::$speed} along the effector's X axis. The push is
 * the difference between the target velocity and the body's current velocity,
 * multiplied by {@see SurfaceEffector2D::$forceScale}, so bodies settle at the belt
 * speed. Use it for conveyor belts, moving walkways and escalators.
 */
final class SurfaceEffector2D extends Component
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
        parent::__construct($gameObject, $componentId, 'SurfaceEffector2D');
    }

    /**
     * Whether the push direction ignores the GameObject's rotation.
     *
     * When `false`, the push follows the effector's local X axis, so a rotated belt
     * pushes along its slope. When `true`, the push always follows the world X axis.
     * Defaults to `false`.
     */
    public bool $useGlobalDirection {
        get {
            return (bool) ($this->getState()['useGlobalDirection'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('surface_effector2d_set_use_global_direction', $this->componentId, $value);
        }
    }

    /**
     * Target speed along the surface, in world units per second.
     *
     * Positive values push toward the effector's +X direction and negative values push
     * the other way. Defaults to `5.0`.
     */
    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 5.0);
        }

        set(float $value) {
            NativeEngine::call('surface_effector2d_set_speed', $this->componentId, $value);
        }
    }

    /**
     * How strongly the effector pulls bodies toward the target speed.
     *
     * The push is multiplied by this value. `0` turns the push off, and higher values
     * make bodies reach the belt speed faster. Negative values are stored as `0`.
     * Defaults to `1.0`.
     */
    public float $forceScale {
        get {
            return (float) ($this->getState()['forceScale'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('surface_effector2d_set_force_scale', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     useGlobalDirection?: bool,
     *     speed?: float|int,
     *     forceScale?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     useGlobalDirection?: bool,
         *     speed?: float|int,
         *     forceScale?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('surface_effector2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
