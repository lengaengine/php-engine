<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Pushes 2D rigidbodies in one direction while they overlap the GameObject's collider.
 *
 * Add it next to a {@see BoxCollider2D} or {@see CircleCollider2D} to make a
 * wind zone, a current or a conveyor-like push. Each fixed step, every
 * {@see Rigidbody2D} whose colliders overlap that collider is accelerated
 * by {@see AreaEffector2D::$forceMagnitude} along
 * {@see AreaEffector2D::$forceAngle}, reduced by
 * {@see AreaEffector2D::$drag} times the body's velocity. Make the collider
 * a trigger when bodies should pass through the area.
 */
final class AreaEffector2D extends Component
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
        parent::__construct($gameObject, $componentId, 'AreaEffector2D');
    }

    /**
     * Whether {@see AreaEffector2D::$forceAngle} is measured in world space.
     *
     * When false, the angle turns with the GameObject's Z rotation. When
     * true, rotating the GameObject does not change the direction. Defaults
     * to `false`.
     */
    public bool $useGlobalAngle {
        get {
            return (bool) ($this->getState()['useGlobalAngle'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('area_effector2d_set_use_global_angle', $this->componentId, $value);
        }
    }

    /**
     * Direction of the push, in degrees.
     *
     * `0` pushes along positive X (right) and `90` along positive Y, which is
     * down the screen in a 2D scene. Defaults to `0`.
     */
    public float $forceAngle {
        get {
            return (float) ($this->getState()['forceAngle'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('area_effector2d_set_force_angle', $this->componentId, $value);
        }
    }

    /**
     * Strength of the push, as an acceleration in world units per second squared.
     *
     * Every body receives the same acceleration, whatever its mass. Negative
     * values push the opposite way. Defaults to `10`.
     */
    public float $forceMagnitude {
        get {
            return (float) ($this->getState()['forceMagnitude'] ?? 10.0);
        }

        set(float $value) {
            NativeEngine::call('area_effector2d_set_force_magnitude', $this->componentId, $value);
        }
    }

    /**
     * How strongly the area slows bodies inside it.
     *
     * Each body is slowed in proportion to its velocity times this value.
     * Values below zero are treated as zero. Defaults to `0`.
     */
    public float $drag {
        get {
            return (float) ($this->getState()['drag'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('area_effector2d_set_drag', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     useGlobalAngle?: bool,
     *     forceAngle?: float|int,
     *     forceMagnitude?: float|int,
     *     drag?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     useGlobalAngle?: bool,
         *     forceAngle?: float|int,
         *     forceMagnitude?: float|int,
         *     drag?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('area_effector2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
