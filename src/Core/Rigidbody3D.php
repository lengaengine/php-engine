<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\ForceMode;
use function is_array;

/**
 * 3D rigid body physics component bound to the Lenga runtime.
 *
 * Use this component when a GameObject should be driven by the 3D physics simulation.
 */
final class Rigidbody3D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'Rigidbody3D');
    }

    /** Rigid body mode used by the 3D physics solver. */
    public string $bodyType {
        get {
            return (string) ($this->getState()['bodyType'] ?? 'Dynamic');
        }

        set(string $value) {
            NativeEngine::call('rigidbody3d_set_body_type', $this->componentId, $value);
        }
    }

    /** World-space linear velocity. */
    public Vector3 $velocity {
        get {
            /** @var array{x?: float|int, y?: float|int, z?: float|int}|false $state */
            $state = NativeEngine::call('rigidbody3d_get_velocity', $this->componentId);
            if (!is_array($state)) {
                return new Vector3();
            }

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('rigidbody3d_set_velocity', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /** World-space angular velocity. */
    public Vector3 $angularVelocity {
        get {
            /** @var array{x?: float|int, y?: float|int, z?: float|int}|false $state */
            $state = NativeEngine::call('rigidbody3d_get_angular_velocity', $this->componentId);
            if (!is_array($state)) {
                return new Vector3();
            }

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call(
                'rigidbody3d_set_angular_velocity',
                $this->componentId,
                $value->x,
                $value->y,
                $value->z,
            );
        }
    }

    /** Enables or disables gravity influence for this body. */
    public bool $useGravity {
        get {
            return (bool) ($this->getState()['useGravity'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('rigidbody3d_set_use_gravity', $this->componentId, $value);
        }
    }

    /** Multiplier applied to world gravity for this body. */
    public float $gravityScale {
        get {
            return (float) ($this->getState()['gravityScale'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_gravity_scale', $this->componentId, $value);
        }
    }

    /** Linear drag coefficient used to damp translational velocity. */
    public float $linearDrag {
        get {
            return (float) ($this->getState()['linearDrag'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_linear_drag', $this->componentId, $value);
        }
    }

    /** Angular drag coefficient used to damp rotational velocity. */
    public float $angularDrag {
        get {
            return (float) ($this->getState()['angularDrag'] ?? 0.05);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_angular_drag', $this->componentId, $value);
        }
    }

    /** Prevents the physics simulation from rotating this body. */
    public bool $freezeRotation {
        get {
            return (bool) ($this->getState()['freezeRotation'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('rigidbody3d_set_freeze_rotation', $this->componentId, $value);
        }
    }

    /** Collision detection mode used by the 3D backend. */
    public string $collisionDetection {
        get {
            return (string) ($this->getState()['collisionDetection'] ?? 'Discrete');
        }

        set(string $value) {
            NativeEngine::call('rigidbody3d_set_collision_detection', $this->componentId, $value);
        }
    }

    /**
     * Applies a force to this body.
     *
     * `Force` and `Acceleration` are applied over the next simulation step. `Impulse`
     * and `VelocityChange` apply an immediate velocity change.
     */
    public function addForce(Vector3 $force, ForceMode $mode = ForceMode::Force): void
    {
        NativeEngine::call(
            'rigidbody3d_add_force',
            $this->componentId,
            $force->x,
            $force->y,
            $force->z,
            $mode->value,
        );
    }

    /**
     * Checks whether this body is currently touching any collider that matches the query.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call(
            'rigidbody3d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * Returns all current contacts that match the query.
     *
     * @return list<Collision3D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call(
            'rigidbody3d_get_contacts',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );

        if (!is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (is_array($result)) {
                $contacts[] = Collision3D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision3D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * Reads the current rigid body state snapshot from the engine.
     *
     * @return array{
     *     bodyType?: string,
     *     useGravity?: bool,
     *     gravityScale?: float,
     *     linearDrag?: float,
     *     angularDrag?: float,
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
         *     angularDrag?: float,
         *     freezeRotation?: bool,
         *     collisionDetection?: string,
         *     enabled?: bool
         * }|false $state
         */
        $state = NativeEngine::call('rigidbody3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
