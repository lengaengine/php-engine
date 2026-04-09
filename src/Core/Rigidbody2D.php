<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\ForceMode2D;

/**
 * 2D rigid body physics component bound to the native engine runtime.
 *
 * Exposes body configuration and velocity through property hooks.
 */
final class Rigidbody2D extends Component
{
    /**
     * @param GameObject $gameObject
     * @param int $componentId
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'Rigidbody2D');
    }

    /**
     * Rigid body mode used by the physics solver (for example: Dynamic, Kinematic, Static).
     */
    public string $bodyType {
        get {
            return (string) ($this->getState()['bodyType'] ?? 'Dynamic');
        }

        set(string $value) {
            \lenga_internal_rigidbody2d_set_body_type($this->componentId, $value);
        }
    }

    /** Enables or disables gravity influence for this body. */
    public bool $useGravity {
        get {
            return (bool) ($this->getState()['useGravity'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_rigidbody2d_set_use_gravity($this->componentId, $value);
        }
    }

    /** Multiplier applied to world gravity for this body. */
    public float $gravityScale {
        get {
            return (float) ($this->getState()['gravityScale'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_rigidbody2d_set_gravity_scale($this->componentId, $value);
        }
    }

    /** Linear drag coefficient used to damp translational velocity. */
    public float $linearDrag {
        get {
            return (float) ($this->getState()['linearDrag'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_rigidbody2d_set_linear_drag($this->componentId, $value);
        }
    }

    /** Prevents the physics simulation from rotating this body. */
    public bool $freezeRotation {
        get {
            return (bool) ($this->getState()['freezeRotation'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_rigidbody2d_set_freeze_rotation($this->componentId, $value);
        }
    }

    /** Collision detection mode used by the 2D backend (Discrete by default, Continuous when supported). */
    public string $collisionDetection {
        get {
            return (string) ($this->getState()['collisionDetection'] ?? 'Discrete');
        }

        set(string $value) {
            \lenga_internal_rigidbody2d_set_collision_detection($this->componentId, $value);
        }
    }

    /** World-space linear velocity. */
    public Vector3 $velocity {
        get {
            /** @var array{x?: float|int, y?: float|int, z?: float|int}|false $state */
            $state = \lenga_internal_rigidbody2d_get_velocity($this->componentId);
            if (!\is_array($state)) {
                return new Vector3(0.0, 0.0, 0.0);
            }

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            \lenga_internal_rigidbody2d_set_velocity($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Checks whether this body is currently touching any collider that matches the query.
     *
     * @param bool $includeTriggers Whether trigger colliders are included.
     * @param int|null $layerMask Layer mask filter; defaults to all layers.
     * @return bool
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return \lenga_internal_rigidbody2d_is_touching(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * Returns true when this body is resting on a supporting surface.
     *
     * Support is resolved against the current world gravity direction, which
     * keeps the query aligned with the native physics setup instead of a fixed axis.
     *
     * @param float $minSupportDot Higher values reject steeper slopes. Valid range is -1..1.
     * @param bool $includeTriggers Whether trigger colliders are included.
     * @param int|null $layerMask Layer mask filter; defaults to all layers.
     */
    public function isGrounded(
        float $minSupportDot = 0.5,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): bool {
        return \lenga_internal_rigidbody2d_is_grounded(
            $this->componentId,
            $minSupportDot,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * Returns all current contacts that match the query.
     *
     * @param bool $includeTriggers Whether trigger colliders are included.
     * @param int|null $layerMask Layer mask filter; defaults to all layers.
     * @return list<Collision2D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = \lenga_internal_rigidbody2d_get_contacts(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $contacts[] = Collision2D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision2D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * Reads the current native rigid body state snapshot.
     *
     * @return array{
     *     bodyType?: string,
     *     useGravity?: bool,
     *     gravityScale?: float,
     *     linearDrag?: float,
     *     freezeRotation?: bool,
     *     collisionDetection?: string,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     bodyType?: string,
         *     useGravity?: bool,
         *     gravityScale?: float,
         *     linearDrag?: float,
         *     freezeRotation?: bool,
         *     collisionDetection?: string,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_rigidbody2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }

    /**
     * Applies a force to this body.
     *
     * Note: Lenga currently treats `force` as an acceleration-like value (mass is not modeled),
     * accumulated and applied during the next physics step.
     */
    public function addForce(Vector2 $force, ForceMode2D $mode = ForceMode2D::Force): void
    {
        \lenga_internal_rigidbody2d_add_force(
            $this->componentId,
            $force->x,
            $force->y,
            $mode->value
        );
    }
}
