<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\ForceMode2D;
use Lenga\Engine\Enumerations\RigidbodyInterpolation;

/**
 * Lets 2D physics move a GameObject with velocity, gravity, forces and collisions.
 *
 * Add a Rigidbody2D together with a 2D collider such as {@see BoxCollider2D}
 * or {@see CircleCollider2D}. The Rigidbody2D decides how the object moves;
 * the collider gives it a shape to collide with. Get the component from a
 * script with `$this->gameObject->getComponent(Rigidbody2D::class)`.
 *
 * 2D scenes use screen-oriented coordinates: positive X points right and
 * positive Y points down, so the default gravity pulls toward positive Y and
 * an upward jump uses a negative Y velocity or impulse. Change the gravity
 * with {@see Physics2D::setGravity()}.
 *
 * Change velocity and apply forces in `fixedUpdate()`, which runs once per
 * physics step.
 *
 * @example examples/Core/Rigidbody2D.php Run and jump with a Rigidbody2D
 */
final class Rigidbody2D extends Component
{
    /**
     * Wraps an existing Rigidbody2D component.
     *
     * The engine creates Rigidbody2D wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'Rigidbody2D');
    }

    /**
     * How physics treats this body: `Dynamic`, `Kinematic` or `Static`.
     *
     * A `Dynamic` body responds to gravity, forces and collisions. A
     * `Kinematic` body moves only when a script moves it, for example with
     * {@see Rigidbody2D::movePosition()}. A `Static` body does not move.
     * Defaults to `Dynamic`.
     */
    public string $bodyType {
        get {
            return (string) ($this->getState()['bodyType'] ?? 'Dynamic');
        }

        set(string $value) {
            NativeEngine::call('rigidbody2d_set_body_type', $this->componentId, $value);
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
            NativeEngine::call('rigidbody2d_set_use_gravity', $this->componentId, $value);
        }
    }

    /**
     * Multiplier applied to the world gravity for this body.
     *
     * Use `0.5` for half gravity or `2.0` for double gravity, for example to
     * make a character fall faster than it rises. Defaults to `1.0`.
     */
    public float $gravityScale {
        get {
            return (float) ($this->getState()['gravityScale'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('rigidbody2d_set_gravity_scale', $this->componentId, $value);
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
            NativeEngine::call('rigidbody2d_set_linear_drag', $this->componentId, $value);
        }
    }

    /**
     * Whether physics is prevented from rotating this body.
     *
     * Turn this on for characters that should stay upright when they hit
     * walls or slopes. Defaults to `false`.
     */
    public bool $freezeRotation {
        get {
            return (bool) ($this->getState()['freezeRotation'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('rigidbody2d_set_freeze_rotation', $this->componentId, $value);
        }
    }

    /**
     * How collisions are detected for this body: `Discrete` or `Continuous`.
     *
     * `Discrete` checks for overlaps once per physics step. `Continuous`
     * also catches collisions a fast body would pass through between steps,
     * where the platform supports it. Defaults to `Discrete`.
     */
    public string $collisionDetection {
        get {
            return (string) ($this->getState()['collisionDetection'] ?? 'Discrete');
        }

        set(string $value) {
            NativeEngine::call('rigidbody2d_set_collision_detection', $this->componentId, $value);
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
            NativeEngine::call('rigidbody2d_set_interpolation', $this->componentId, $value->value);
        }
    }

    /**
     * Moves this body to a 2D world position during the next physics step.
     *
     * Call it from `fixedUpdate()` to move a `Kinematic` body smoothly. The
     * move uses the body's {@see Rigidbody2D::$interpolation} setting. Setting
     * {@see Transform::$position} directly teleports the object instead.
     *
     * @param Vector2 $position The target position in world space.
     */
    public function movePosition(Vector2 $position): void
    {
        NativeEngine::call(
            'rigidbody2d_move_position',
            $this->componentId,
            $position->x,
            $position->y,
        );
    }

    /**
     * Rotates this body to a 2D angle during the next physics step.
     *
     * Call it from `fixedUpdate()` to rotate a `Kinematic` body smoothly. The
     * rotation uses the body's {@see Rigidbody2D::$interpolation} setting.
     * Setting {@see Transform::$rotation} directly snaps the object instead.
     *
     * @param float $angleDegrees The target rotation around the Z axis, in degrees.
     */
    public function moveRotation(float $angleDegrees): void
    {
        NativeEngine::call(
            'rigidbody2d_move_rotation',
            $this->componentId,
            $angleDegrees,
        );
    }

    /**
     * The body's velocity in world space, in world units per second.
     *
     * Read it, change a component, and assign it back to steer the body:
     * `$velocity = $body->velocity->clone(); $velocity->x = 120.0; $body->velocity = $velocity;`.
     * 2D physics uses the X and Y components. Positive Y moves down. Reads
     * as a zero vector when the engine reports no velocity.
     */
    public Vector3 $velocity {
        get {
            /** @var array{x?: float|int, y?: float|int, z?: float|int}|false $state */
            $state = NativeEngine::call('rigidbody2d_get_velocity', $this->componentId);
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
            NativeEngine::call('rigidbody2d_set_velocity', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Checks whether this body is touching any collider that matches the filters.
     *
     * Any contact counts, including walls and ceilings. Use
     * {@see Rigidbody2D::isGrounded()} to check for a surface that supports
     * the body.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     */
    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return NativeEngine::call('rigidbody2d_is_touching',
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * Checks whether this body is resting on a surface that supports it.
     *
     * A contact supports the body when it pushes against the current gravity
     * direction, so the check still works after {@see Physics2D::setGravity()}
     * changes gravity. Use it to decide whether the player may jump.
     *
     * @param float $minSupportDot How directly a surface must face against gravity, from -1 to 1. Higher values reject steeper slopes.
     * @param bool $includeTriggers Whether contacts with trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     */
    public function isGrounded(
        float $minSupportDot = 0.5,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): bool {
        return NativeEngine::call('rigidbody2d_is_grounded',
            $this->componentId,
            $minSupportDot,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * Lists the contacts this body has with colliders that match the filters.
     *
     * Set `$includeTriggers` to `false` to see only solid contacts.
     *
     * @param bool $includeTriggers Whether contacts with trigger colliders are included.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Collision2D> One entry per contact; empty when the body touches nothing.
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = NativeEngine::call('rigidbody2d_get_contacts',
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
         *     freezeRotation?: bool,
         *     collisionDetection?: string,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('rigidbody2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }

    /**
     * Pushes this body with a force or an instant impulse.
     *
     * Forces added during a step are combined and applied in the next physics
     * step. Rigidbody2D has no mass setting, so a force changes every body's
     * velocity by the same amount. Use {@see ForceMode2D::Force} for a push
     * that lasts, called every `fixedUpdate()`, and {@see ForceMode2D::Impulse}
     * for a single kick such as a jump.
     *
     * @param Vector2 $force The direction and strength of the push, in world space. Negative Y pushes up.
     * @param ForceMode2D $mode Whether to apply a continuous force or an instant impulse.
     */
    public function addForce(Vector2 $force, ForceMode2D $mode = ForceMode2D::Force): void
    {
        NativeEngine::call('rigidbody2d_add_force',
            $this->componentId,
            $force->x,
            $force->y,
            $mode->value
        );
    }
}
