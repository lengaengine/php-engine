<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Pushes 2D bodies away from, or pulls them toward, the centre of its collider.
 *
 * Add it to a GameObject with a 2D collider. Each physics step, every
 * {@see Rigidbody2D} whose colliders overlap that collider receives a force
 * along the line from the collider's centre to the body's centre. Use it for
 * magnets, black holes, explosions and repulsion fields.
 *
 * The force weakens with distance: it is
 * `forceMagnitude / (distance * distanceScale)`, divided by
 * `distance * distanceScale` again when {@see PointEffector2D::$inverseSquared}
 * is on. A body exactly at the centre receives no force.
 */
final class PointEffector2D extends Component
{
    /**
     * Wraps an existing PointEffector2D component.
     *
     * The engine creates PointEffector2D wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PointEffector2D');
    }

    /**
     * The strength of the force before it weakens with distance.
     *
     * Positive values push bodies away from the centre; negative values pull
     * them toward it. Defaults to `10.0`.
     */
    public float $forceMagnitude {
        get {
            return (float) ($this->getState()['forceMagnitude'] ?? 10.0);
        }

        set(float $value) {
            NativeEngine::call('point_effector2d_set_force_magnitude', $this->componentId, $value);
        }
    }

    /**
     * Multiplier applied to the distance before the force is weakened.
     *
     * Values below `1.0` make the force reach further; values above `1.0`
     * make it fade faster. The engine keeps the value above zero. Defaults to
     * `1.0`.
     */
    public float $distanceScale {
        get {
            return (float) ($this->getState()['distanceScale'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('point_effector2d_set_distance_scale', $this->componentId, $value);
        }
    }

    /**
     * Whether the force weakens with the square of the distance.
     *
     * On, the force fades quickly, like gravity or magnetism. Off, it fades in
     * direct proportion to the distance. Defaults to `true`.
     */
    public bool $inverseSquared {
        get {
            return (bool) ($this->getState()['inverseSquared'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('point_effector2d_set_inverse_squared', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     forceMagnitude?: float|int,
     *     distanceScale?: float|int,
     *     inverseSquared?: bool,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     forceMagnitude?: float|int,
         *     distanceScale?: float|int,
         *     inverseSquared?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('point_effector2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
