<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

/**
 * Moves a 3D character under script control, stopping at and sliding along the colliders it meets.
 *
 * Use it for player characters, scripted enemies and other objects your
 * code steers, where a {@see Rigidbody3D} would let physics push them
 * around. The controller is an upright capsule described by
 * {@see CharacterController::$radius}, {@see CharacterController::$height}
 * and {@see CharacterController::$center}. Move it from `update()` with
 * {@see CharacterController::move()} or
 * {@see CharacterController::simpleMove()}; do not also give the GameObject
 * a dynamic Rigidbody3D.
 *
 * ```php
 * $direction = new Vector3(Input::getAxis('Horizontal'), 0.0, Input::getAxis('Vertical'));
 * $this->controller->simpleMove(Vector3::scaleNew($direction, $this->speed));
 *
 * if ($this->controller->isGrounded) {
 *     // Allow a jump.
 * }
 * ```
 */
final class CharacterController extends Component
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
        parent::__construct($gameObject, $componentId, 'CharacterController');
    }

    /**
     * Radius of the controller's capsule, in local units.
     *
     * The GameObject's larger horizontal scale (X or Z) is applied on top.
     * Defaults to `0.5`.
     */
    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('character_controller_set_radius', $this->componentId, $value);
        }
    }

    /**
     * Height of the controller's capsule from end to end, in local units.
     *
     * The GameObject's Y scale is applied on top. The capsule is never
     * shorter than its diameter. Defaults to `2`.
     */
    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 2.0);
        }

        set(float $value) {
            NativeEngine::call('character_controller_set_height', $this->componentId, $value);
        }
    }

    /**
     * Position of the capsule's centre relative to the GameObject's position, in local units.
     *
     * The GameObject's scale is applied on top. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $center {
        get {
            $state = $this->getState()['center'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('character_controller_set_center', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Gap the controller keeps between itself and the colliders it touches, in world units.
     *
     * A small gap stops the capsule from sticking to walls and floors. It is
     * limited to just under the capsule's radius. Defaults to `0.08`.
     */
    #[Min(0)]
    public float $skinWidth {
        get {
            return (float) ($this->getState()['skinWidth'] ?? 0.08);
        }

        set(float $value) {
            NativeEngine::call('character_controller_set_skin_width', $this->componentId, $value);
        }
    }

    /**
     * Shortest movement {@see CharacterController::move()} carries out, in world units.
     *
     * A call with a shorter motion leaves the controller where it is. Zero,
     * the default, carries out any movement.
     */
    #[Min(0)]
    public float $minMoveDistance {
        get {
            return (float) ($this->getState()['minMoveDistance'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('character_controller_set_min_move_distance', $this->componentId, $value);
        }
    }

    /**
     * Whether the controller is blocked by other colliders.
     *
     * When false, {@see CharacterController::move()} moves the GameObject by
     * the full motion, passing through everything, and
     * {@see CharacterController::$isGrounded} is false. Defaults to `true`.
     */
    public bool $detectCollisions {
        get {
            return (bool) ($this->getState()['detectCollisions'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('character_controller_set_detect_collisions', $this->componentId, $value);
        }
    }

    /**
     * Whether the controller was standing on a collider after its last move. Read-only.
     *
     * Updated by each call to {@see CharacterController::move()} or
     * {@see CharacterController::simpleMove()}. Trigger colliders do not
     * count. Defaults to `false`.
     */
    public bool $isGrounded {
        get {
            return (bool) ($this->getState()['isGrounded'] ?? false);
        }
    }

    /**
     * How fast the controller moved during its last move, in world units per second. Read-only.
     *
     * It is the distance the controller actually travelled, after collisions,
     * divided by {@see Time::deltaTime()}. Zero when the last move did not go
     * anywhere or the game is paused.
     */
    public Vector3 $velocity {
        get {
            $state = $this->getState()['velocity'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }
    }

    /**
     * Which sides of the controller were blocked during its last move. Read-only.
     *
     * The same bit flags {@see CharacterController::move()} returns. Test
     * them against {@see \Lenga\Engine\Enumerations\CollisionFlags} values.
     */
    public int $collisionFlags {
        get {
            return (int) ($this->getState()['collisionFlags'] ?? 0);
        }
    }

    /**
     * Moves the controller by a distance, stopping at and sliding along the colliders in the way.
     *
     * Pass the movement for this frame, usually a velocity multiplied by
     * {@see Time::deltaTime()}. The controller moves as far as it can and
     * slides along walls and floors on the blocked axes. It does not apply
     * gravity; add it to the motion yourself, or use
     * {@see CharacterController::simpleMove()}. Trigger colliders never block
     * it.
     *
     * The result combines {@see \Lenga\Engine\Enumerations\CollisionFlags}
     * bits: `Below` when downward motion was blocked, `Above` when upward
     * motion was blocked, and `Sides` when motion along X or Z was blocked.
     *
     * ```php
     * $flags = $this->controller->move($motion);
     * if (($flags & CollisionFlags::Below->value) !== 0) {
     *     $this->verticalSpeed = 0.0;
     * }
     * ```
     *
     * @param Vector3 $motion The movement to attempt, in world units.
     * @return int The {@see \Lenga\Engine\Enumerations\CollisionFlags} bits for this move, `0` when nothing blocked it.
     */
    public function move(Vector3 $motion): int
    {
        return (int) NativeEngine::call('character_controller_move',
            $this->componentId,
            $motion->x,
            $motion->y,
            $motion->z,
        );
    }

    /**
     * Moves the controller at a horizontal speed for this frame and applies gravity.
     *
     * Call it once per frame from `update()`. The controller moves by
     * `$speed` multiplied by {@see Time::deltaTime()} on X and Z, and falls
     * under a gravity of 9.81 units per second squared that resets when it
     * lands. The Y value of `$speed` is ignored, so use
     * {@see CharacterController::move()} for jumping and flying. While the
     * game is paused the controller does not move.
     *
     * @param Vector3 $speed Horizontal speed in world units per second. Y is ignored.
     * @return bool True when the controller is standing on a collider after the move.
     */
    public function simpleMove(Vector3 $speed): bool
    {
        return (bool) NativeEngine::call('character_controller_simple_move',
            $this->componentId,
            $speed->x,
            $speed->y,
            $speed->z,
        );
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     radius?: float|int,
     *     height?: float|int,
     *     center?: array{x?: float|int, y?: float|int, z?: float|int},
     *     skinWidth?: float|int,
     *     minMoveDistance?: float|int,
     *     detectCollisions?: bool,
     *     isGrounded?: bool,
     *     collisionFlags?: int,
     *     velocity?: array{x?: float|int, y?: float|int, z?: float|int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     radius?: float|int,
         *     height?: float|int,
         *     center?: array{x?: float|int, y?: float|int, z?: float|int},
         *     skinWidth?: float|int,
         *     minMoveDistance?: float|int,
         *     detectCollisions?: bool,
         *     isGrounded?: bool,
         *     collisionFlags?: int,
         *     velocity?: array{x?: float|int, y?: float|int, z?: float|int}
         * } $state
         */
        $state = NativeEngine::call('character_controller_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
