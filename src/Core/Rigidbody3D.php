<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\ForceMode;
use Lenga\Engine\Enumerations\RigidbodyInterpolation;
use function is_array;

/**
 * Lets 3D physics move a GameObject with velocity, gravity, forces and collisions.
 *
 * Add a Rigidbody3D together with a 3D collider such as
 * {@see BoxCollider3D} or {@see SphereCollider3D}. The Rigidbody3D decides
 * how the object moves; the collider gives it a shape to collide with. Get
 * the component from a script with `$this->gameObject->getComponent(Rigidbody3D::class)`.
 *
 * 3D scenes use a Y-up coordinate system, and the default gravity pulls
 * toward negative Y. Change it with {@see Physics3D::setGravity()}. Change
 * velocity and apply forces in `fixedUpdate()`, which runs once per physics
 * step.
 */
final class Rigidbody3D extends Component
{
    /**
     * Wraps an existing Rigidbody3D component.
     *
     * The engine creates Rigidbody3D wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'Rigidbody3D');
    }

    /**
     * How physics treats this body: `Dynamic`, `Kinematic` or `Static`.
     *
     * A `Dynamic` body responds to gravity, forces and collisions. A
     * `Kinematic` body moves only when a script moves it, for example with
     * {@see Rigidbody3D::movePosition()}. A `Static` body does not move.
     * Defaults to `Dynamic`.
     */
    public string $bodyType {
        get {
            return (string) ($this->getState()['bodyType'] ?? 'Dynamic');
        }

        set(string $value) {
            NativeEngine::call('rigidbody3d_set_body_type', $this->componentId, $value);
        }
    }

    /**
     * The body's velocity in world space, in world units per second.
     *
     * Read it, change a component, and assign it back to steer the body.
     * Reads as a zero vector when the engine reports no velocity.
     */
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

    /**
     * How fast the body spins around each world axis.
     *
     * Each component is the rotation speed around that world axis. Reads as a
     * zero vector when the engine reports no angular velocity.
     */
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

    /**
     * Whether gravity pulls on this body.
     *
     * Gravity only affects an enabled `Dynamic` body. Defaults to `true`.
     */
    public bool $useGravity {
        get {
            return (bool) ($this->getState()['useGravity'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('rigidbody3d_set_use_gravity', $this->componentId, $value);
        }
    }

    /**
     * Multiplier applied to the world gravity for this body.
     *
     * Use `0.5` for half gravity or `2.0` for double gravity. Defaults to `1.0`.
     */
    public float $gravityScale {
        get {
            return (float) ($this->getState()['gravityScale'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_gravity_scale', $this->componentId, $value);
        }
    }

    /**
     * How strongly the body's velocity slows down over time.
     *
     * `0.0` means the body keeps its speed until something else changes it.
     * Higher values slow it down faster. Defaults to `0.0`.
     */
    public float $linearDrag {
        get {
            return (float) ($this->getState()['linearDrag'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_linear_drag', $this->componentId, $value);
        }
    }

    /**
     * How strongly the body's spin slows down over time.
     *
     * Higher values stop a spinning body faster. Defaults to `0.05`.
     */
    public float $angularDrag {
        get {
            return (float) ($this->getState()['angularDrag'] ?? 0.05);
        }

        set(float $value) {
            NativeEngine::call('rigidbody3d_set_angular_drag', $this->componentId, $value);
        }
    }

    /**
     * Whether physics is prevented from rotating this body around any axis.
     *
     * Turn this on for characters that should stay upright. To lock only some
     * axes, use {@see Rigidbody3D::$freezeRotationX},
     * {@see Rigidbody3D::$freezeRotationY} and
     * {@see Rigidbody3D::$freezeRotationZ}. Defaults to `false`.
     */
    public bool $freezeRotation {
        get {
            return (bool) ($this->getState()['freezeRotation'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('rigidbody3d_set_freeze_rotation', $this->componentId, $value);
        }
    }

    /**
     * Whether physics is prevented from rotating this body around the world X axis.
     *
     * Defaults to `false`.
     */
    public bool $freezeRotationX {
        get {
            return (bool) ($this->getState()['freezeRotationX'] ?? false);
        }

        set(bool $value) {
            $this->setFreezeRotationAxes(
                $value,
                $this->freezeRotationY,
                $this->freezeRotationZ,
            );
        }
    }

    /**
     * Whether physics is prevented from rotating this body around the world Y axis.
     *
     * Lock X and Z but leave Y free to let a character turn while staying
     * upright. Defaults to `false`.
     */
    public bool $freezeRotationY {
        get {
            return (bool) ($this->getState()['freezeRotationY'] ?? false);
        }

        set(bool $value) {
            $this->setFreezeRotationAxes(
                $this->freezeRotationX,
                $value,
                $this->freezeRotationZ,
            );
        }
    }

    /**
     * Whether physics is prevented from rotating this body around the world Z axis.
     *
     * Defaults to `false`.
     */
    public bool $freezeRotationZ {
        get {
            return (bool) ($this->getState()['freezeRotationZ'] ?? false);
        }

        set(bool $value) {
            $this->setFreezeRotationAxes(
                $this->freezeRotationX,
                $this->freezeRotationY,
                $value,
            );
        }
    }

    /**
     * How collisions are detected for this body: `Discrete` or `Continuous`.
     *
     * `Discrete` checks for overlaps once per physics step. Use `Continuous`
     * for fast bodies that could otherwise pass through thin colliders between
     * steps. Defaults to `Discrete`.
     */
    public string $collisionDetection {
        get {
            return (string) ($this->getState()['collisionDetection'] ?? 'Discrete');
        }

        set(string $value) {
            NativeEngine::call('rigidbody3d_set_collision_detection', $this->componentId, $value);
        }
    }

    /**
     * How the body's Transform is smoothed between physics steps.
     *
     * Physics runs at a fixed rate that can differ from the frame rate. Use
     * {@see RigidbodyInterpolation::Interpolate} to remove visible stutter on
     * a moving body such as the player. Defaults to
     * {@see RigidbodyInterpolation::None}.
     */
    public RigidbodyInterpolation $interpolation {
        get {
            return RigidbodyInterpolation::tryFrom((string) ($this->getState()['interpolation'] ?? 'None'))
                ?? RigidbodyInterpolation::None;
        }

        set(RigidbodyInterpolation $value) {
            NativeEngine::call('rigidbody3d_set_interpolation', $this->componentId, $value->value);
        }
    }

    /**
     * Moves this body to a world position during the next physics step.
     *
     * Call it from `fixedUpdate()` to move a `Kinematic` body smoothly. The
     * move uses the body's {@see Rigidbody3D::$interpolation} setting. Setting
     * {@see Transform::$position} directly teleports the object instead.
     *
     * @param Vector3 $position The target position in world space.
     */
    public function movePosition(Vector3 $position): void
    {
        NativeEngine::call(
            'rigidbody3d_move_position',
            $this->componentId,
            $position->x,
            $position->y,
            $position->z,
        );
    }

    /**
     * Rotates this body to a world orientation during the next physics step.
     *
     * Call it from `fixedUpdate()` to rotate a `Kinematic` body smoothly. The
     * rotation uses the body's {@see Rigidbody3D::$interpolation} setting.
     * Setting {@see Transform::$rotation} directly snaps the object instead.
     *
     * @param Quaternion $rotation The target orientation in world space.
     */
    public function moveRotation(Quaternion $rotation): void
    {
        NativeEngine::call(
            'rigidbody3d_move_rotation',
            $this->componentId,
            $rotation->x,
            $rotation->y,
            $rotation->z,
            $rotation->w,
        );
    }

    /**
     * Pushes this body with a force, an acceleration, an impulse or a direct velocity change.
     *
     * {@see ForceMode::Force} and {@see ForceMode::Acceleration} are applied
     * over the next physics step; call them every `fixedUpdate()` for a push
     * that lasts. {@see ForceMode::Impulse} and {@see ForceMode::VelocityChange}
     * change the velocity at once; use them for a single kick such as a jump
     * or a launch pad.
     *
     * @param Vector3 $force The direction and strength of the push, in world space.
     * @param ForceMode $mode How the force is applied.
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
     * Locks or unlocks rotation around each world axis at once.
     *
     * Use it in place of setting {@see Rigidbody3D::$freezeRotationX},
     * {@see Rigidbody3D::$freezeRotationY} and
     * {@see Rigidbody3D::$freezeRotationZ} one by one.
     *
     * @param bool $x Whether rotation around the world X axis is locked.
     * @param bool $y Whether rotation around the world Y axis is locked.
     * @param bool $z Whether rotation around the world Z axis is locked.
     */
    public function setFreezeRotationAxes(bool $x, bool $y, bool $z): void
    {
        NativeEngine::call('rigidbody3d_set_freeze_rotation_axes', $this->componentId, $x, $y, $z);
    }

    /**
     * Checks whether any collider on this body's GameObject is touching a collider that matches the filters.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
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
     * Lists the contacts between colliders on this body's GameObject and colliders that match the filters.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders are included.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Collision3D> One entry per contact; empty when the body touches nothing.
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
     *     freezeRotationX?: bool,
     *     freezeRotationY?: bool,
     *     freezeRotationZ?: bool,
     *     collisionDetection?: string,
     *     interpolation?: string,
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
         *     freezeRotationX?: bool,
         *     freezeRotationY?: bool,
         *     freezeRotationZ?: bool,
         *     collisionDetection?: string,
         *     enabled?: bool
         * }|false $state
         */
        $state = NativeEngine::call('rigidbody3d_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
