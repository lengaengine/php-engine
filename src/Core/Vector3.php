<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;
use function abs;
use function acos;
use function cos;
use function is_array;
use function max;
use function min;
use function sin;
use function sqrt;

/**
 * 3D vector for positions, directions, velocities, Euler angles and scales.
 *
 * Lenga uses Vector3 for {@see Transform} positions, rotations in degrees and scales, in
 * both 2D and 3D scenes. It has two kinds of operations:
 *
 * - Instance methods such as {@see Vector3::add()} and {@see Vector3::scale()} change the
 *   vector itself and return it, so calls can be chained.
 * - Static methods such as {@see Vector3::sum()} and {@see Vector3::distance()} leave their
 *   inputs alone and return a new value.
 *
 * Components can also be read and written like an array, with `0`, `1` and `2` or `'x'`,
 * `'y'` and `'z'`.
 *
 * Properties such as {@see Transform::$position} return a copy. Change the copy and assign
 * it back:
 *
 * ```php
 * $position = $this->transform->position;
 * $position->add(new Vector3(0.0, $this->jumpHeight, 0.0));
 * $this->transform->position = $position;
 * ```
 */
final class Vector3 implements ArrayAccess
{
    private const float EPSILON = 0.000001;
    private const float RAD2DEG = 57.29577951308232;

    /**
     * X component.
     */
    public float $x {
        get {
            return $this->x;
        }
        set {
            $this->x = $value;
        }
    }

    /**
     * Y component.
     */
    public float $y {
        get {
            return $this->y;
        }
        set {
            $this->y = $value;
        }
    }

    /**
     * Z component.
     */
    public float $z {
        get {
            return $this->z;
        }
        set {
            $this->z = $value;
        }
    }

    /**
     * Squared length of the vector. Read-only.
     *
     * Cheaper than {@see Vector3::$magnitude} because it skips the square root. Use it to
     * compare lengths, for example `$offset->sqrMagnitude < $range * $range`.
     */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z;
        }
    }

    /**
     * Length of the vector. Read-only.
     */
    public float $magnitude {
        get {
            return sqrt($this->sqrMagnitude);
        }
    }

    /**
     * Copy of this vector with a length of 1, pointing the same way. Read-only.
     *
     * Returns `(0, 0, 0)` when the vector is shorter than about `0.001`. The vector itself does
     * not change. Use {@see Vector3::normalize()} to change it in place.
     */
    public Vector3 $normalized {
        get {
            if ($this->sqrMagnitude <= self::EPSILON) {
                return new self();
            }

            $magnitude = $this->magnitude;

            return new self(
                $this->x / $magnitude,
                $this->y / $magnitude,
                $this->z / $magnitude,
            );
        }
    }

    /**
     * Creates a vector from its components.
     *
     * @param float $x X component. Defaults to `0`.
     * @param float $y Y component. Defaults to `0`.
     * @param float $z Z component. Defaults to `0`.
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
        float $z = 0.0,
    )
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Returns a new vector with all components set to 1.
     */
    public static function one(): self
    {
        return new self(1.0, 1.0, 1.0);
    }

    /**
     * Returns a new unit vector pointing down, `(0, -1, 0)`.
     */
    public static function down(): self
    {
        return new self(0.0, -1.0, 0.0);
    }

    /**
     * Returns a new unit vector pointing left, `(-1, 0, 0)`.
     */
    public static function left(): self
    {
        return new self(-1.0, 0.0, 0.0);
    }

    /**
     * Returns a new unit vector pointing along -Z, `(0, 0, -1)`.
     *
     * A {@see Transform} faces its local -Z axis, so this is the local direction of
     * {@see Transform::$forward}.
     */
    public static function back(): self
    {
        return new self(0.0, 0.0, -1.0);
    }

    /**
     * Returns a new vector with all components set to negative infinity.
     */
    public static function negativeInfinity(): self
    {
        return new self(-INF, -INF, -INF);
    }

    /**
     * Returns a new vector with all components set to positive infinity.
     */
    public static function positiveInfinity(): self
    {
        return new self(INF, INF, INF);
    }

    /**
     * Returns a new Vector3 with the X and Y of a Vector2.
     *
     * @param Vector2 $vector Vector to convert.
     * @param float $z Z component of the result. Defaults to `0`.
     */
    public static function fromVector2(Vector2 $vector, float $z = 0.0): self
    {
        return new self($vector->x, $vector->y, $z);
    }

    /**
     * Returns a new Vector3 with the X, Y and Z of a Vector4, dropping W.
     *
     * @param Vector4 $vector Vector to convert.
     */
    public static function fromVector4(Vector4 $vector): self
    {
        return new self($vector->x, $vector->y, $vector->z);
    }

    /**
     * Returns the sum of two vectors as a new vector.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function sum(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x + $b->x,
            $a->y + $b->y,
            $a->z + $b->z,
        );
    }

    /**
     * Returns `$a` minus `$b` as a new vector.
     *
     * The result points from `$b` to `$a`, so `Vector3::difference($target, $position)` is
     * the offset from an object to its target.
     *
     * @param Vector3 $a Vector to subtract from.
     * @param Vector3 $b Vector to subtract.
     */
    public static function difference(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x - $b->x,
            $a->y - $b->y,
            $a->z - $b->z,
        );
    }

    // ---------------------------------------------------------------------
    // In-place operations (mutate this instance; return self for chaining)
    // ---------------------------------------------------------------------

    /**
     * Returns a new vector made by multiplying two vectors component by component.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function product(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x * $b->x,
            $a->y * $b->y,
            $a->z * $b->z,
        );
    }

    /**
     * Returns a new vector made by dividing `$a` by `$b` component by component.
     *
     * @param Vector3 $a Vector to divide.
     * @param Vector3 $b Vector to divide by.
     * @throws \DivisionByZeroError When a component of `$b` is `0`.
     */
    public static function quotient(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x / $b->x,
            $a->y / $b->y,
            $a->z / $b->z,
        );
    }

    /**
     * Returns a new vector equal to a vector multiplied by a number.
     *
     * @param Vector3 $vector Vector to scale.
     * @param float $scalar Number to multiply by.
     */
    public static function scaleNew(Vector3 $vector, float $scalar): self
    {
        return new self(
            $vector->x * $scalar,
            $vector->y * $scalar,
            $vector->z * $scalar,
        );
    }

    /**
     * Returns the angle between two vectors in degrees, from -180 to 180, measured around an axis.
     *
     * The sign tells which way the turn goes around `$axis`: positive when the turn from
     * `$from` to `$to` is counter-clockwise looking down the axis toward the origin.
     *
     * @param Vector3 $from Direction the angle is measured from.
     * @param Vector3 $to Direction the angle is measured to.
     * @param Vector3 $axis Axis that decides the sign, such as {@see Vector3::up()} for a turn
     *     left or right.
     */
    public static function signedAngle(Vector3 $from, Vector3 $to, Vector3 $axis): float
    {
        $unsigned = self::angle($from, $to);
        $cross = self::cross($from, $to);

        return self::dot($axis, $cross) < 0.0 ? -$unsigned : $unsigned;
    }

    /**
     * Returns the angle between two vectors in degrees, from 0 to 180.
     *
     * Returns `0` when either vector has zero length.
     *
     * @param Vector3 $from Direction the angle is measured from.
     * @param Vector3 $to Direction the angle is measured to.
     */
    public static function angle(Vector3 $from, Vector3 $to): float
    {
        $denominator = sqrt($from->sqrMagnitude * $to->sqrMagnitude);
        if ($denominator <= self::EPSILON) {
            return 0.0;
        }

        $dot = self::clamp(self::dot($from, $to) / $denominator, -1.0, 1.0);

        return acos($dot) * self::RAD2DEG;
    }

    private static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    // ---------------------------------------------------------------------
    // Instance convenience methods
    // ---------------------------------------------------------------------

    /**
     * Returns the dot product of two vectors.
     *
     * For unit vectors the result is `1` when they point the same way, `0` when they are at
     * right angles and `-1` when they point in opposite directions.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function dot(Vector3 $a, Vector3 $b): float
    {
        return $a->x * $b->x
            + $a->y * $b->y
            + $a->z * $b->z;
    }

    /**
     * Returns the cross product of two vectors.
     *
     * The result is at right angles to both inputs, and its length is the area of the
     * parallelogram they span. Use it to find a surface normal or a sideways direction.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function cross(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->y * $b->z - $a->z * $b->y,
            $a->z * $b->x - $a->x * $b->z,
            $a->x * $b->y - $a->y * $b->x,
        );
    }

    /**
     * Returns the distance between two points.
     *
     * @param Vector3 $a First point.
     * @param Vector3 $b Second point.
     */
    public static function distance(Vector3 $a, Vector3 $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Returns the point a fraction of the way from `$a` to `$b`.
     *
     * `$t` is clamped to the range 0 to 1, so the result never goes past either end. The
     * calculation runs in the Lenga runtime, so it works only inside the editor or a game.
     *
     * @param Vector3 $a Value at `$t = 0`.
     * @param Vector3 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`, from 0 to 1.
     * @return Vector3 The interpolated vector.
     * @throws \Lenga\Engine\Exceptions\LengaRuntimeUnavailableException When the Lenga runtime is not loaded.
     */
    public static function lerp(Vector3 $a, Vector3 $b, float $t): self
    {
        /** @var array{x: float, y: float, z: float}|false $result */
        $result = NativeEngine::call('vector3_lerp', $a->x, $a->y, $a->z, $b->x, $b->y, $b->z, $t);
        if (!is_array($result)) {
            return new self();
        }

        return self::fromArray($result);
    }

    /**
     * Creates a vector from an array with `x`, `y` and `z` keys.
     *
     * Missing keys become `0`.
     *
     * @param array{x?: float, y?: float, z?: float} $data Component values.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (float)($data['x'] ?? 0.0),
            (float)($data['y'] ?? 0.0),
            (float)($data['z'] ?? 0.0),
        );
    }

    /**
     * Spherically interpolates between two vectors, treating them as directions.
     *
     * The direction turns along the shortest arc and the length changes linearly, so it suits
     * turning one direction into another. `$t` is clamped to the range 0 to 1. The calculation
     * runs in the Lenga runtime, so it works only inside the editor or a game.
     *
     * @param Vector3 $a Value at `$t = 0`.
     * @param Vector3 $b Value at `$t = 1`.
     * @param float $t Fraction of the turn from `$a` to `$b`, from 0 to 1.
     * @return Vector3 The interpolated vector.
     * @throws \Lenga\Engine\Exceptions\LengaRuntimeUnavailableException When the Lenga runtime is not loaded.
     */
    public static function slerp(Vector3 $a, Vector3 $b, float $t): self
    {
        /** @var array{x: float, y: float, z: float}|false $result */
        $result = NativeEngine::call('vector3_slerp', $a->x, $a->y, $a->z, $b->x, $b->y, $b->z, $t);
        if (!is_array($result)) {
            return new self();
        }

        return self::fromArray($result);
    }

    /**
     * Spherically interpolates between two vectors without clamping `$t`.
     *
     * Works like {@see Vector3::slerp()}. Values of `$t` outside 0 to 1 keep turning past `$a`
     * or `$b`. When either vector has zero length, it interpolates linearly instead.
     *
     * @param Vector3 $a Value at `$t = 0`.
     * @param Vector3 $b Value at `$t = 1`.
     * @param float $t Fraction of the turn from `$a` to `$b`.
     * @return Vector3 The interpolated vector.
     */
    public static function slerpUnclamped(Vector3 $a, Vector3 $b, float $t): self
    {
        return self::slerpUnclampedInternal($a, $b, $t);
    }

    private static function slerpUnclampedInternal(Vector3 $a, Vector3 $b, float $t): self
    {
        if ($t === 0.0) {
            return new self($a->x, $a->y, $a->z);
        }

        if ($t === 1.0) {
            return new self($b->x, $b->y, $b->z);
        }

        $magnitudeA = $a->magnitude;
        $magnitudeB = $b->magnitude;
        if ($magnitudeA <= self::EPSILON || $magnitudeB <= self::EPSILON) {
            return self::lerpUnclamped($a, $b, $t);
        }

        $normalizedA = new self($a->x / $magnitudeA, $a->y / $magnitudeA, $a->z / $magnitudeA);
        $normalizedB = new self($b->x / $magnitudeB, $b->y / $magnitudeB, $b->z / $magnitudeB);
        $dot = self::clamp(self::dot($normalizedA, $normalizedB), -1.0, 1.0);

        if ($dot > 0.9995) {
            $direction = self::lerpUnclamped($normalizedA, $normalizedB, $t)->normalized;
        } elseif ($dot < -0.9995) {
            $orthogonal = self::cross(
                $normalizedA,
                abs($normalizedA->x) < 0.99 ? self::right() : self::up(),
            )->normalized;

            if ($orthogonal->sqrMagnitude <= self::EPSILON) {
                $orthogonal = self::cross($normalizedA, self::forward())->normalized;
            }

            $angle = M_PI * $t;
            $direction = new self(
                $normalizedA->x * cos($angle) + $orthogonal->x * sin($angle),
                $normalizedA->y * cos($angle) + $orthogonal->y * sin($angle),
                $normalizedA->z * cos($angle) + $orthogonal->z * sin($angle),
            );
        } else {
            $theta = acos($dot);
            $sinTheta = sin($theta);
            if (abs($sinTheta) <= self::EPSILON) {
                $direction = self::lerpUnclamped($normalizedA, $normalizedB, $t)->normalized;
            } else {
                $fromWeight = sin((1.0 - $t) * $theta) / $sinTheta;
                $toWeight = sin($t * $theta) / $sinTheta;
                $direction = new self(
                    $normalizedA->x * $fromWeight + $normalizedB->x * $toWeight,
                    $normalizedA->y * $fromWeight + $normalizedB->y * $toWeight,
                    $normalizedA->z * $fromWeight + $normalizedB->z * $toWeight,
                );
                $direction = $direction->normalized;
            }
        }

        $magnitude = $magnitudeA + (($magnitudeB - $magnitudeA) * $t);

        return new self(
            $direction->x * $magnitude,
            $direction->y * $magnitude,
            $direction->z * $magnitude,
        );
    }

    /**
     * Returns the point a fraction of the way from `$a` to `$b`, without clamping `$t`.
     *
     * Values of `$t` below 0 or above 1 extend past `$a` or `$b`.
     *
     * @param Vector3 $a Value at `$t = 0`.
     * @param Vector3 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`.
     * @return Vector3 The interpolated vector.
     */
    public static function lerpUnclamped(Vector3 $a, Vector3 $b, float $t): self
    {
        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
            $a->z + ($b->z - $a->z) * $t,
        );
    }

    /**
     * Returns a new unit vector pointing right, `(1, 0, 0)`.
     */
    public static function right(): self
    {
        return new self(1.0, 0.0, 0.0);
    }

    // ---------------------------------------------------------------------
    // Static pure helpers (return new vectors; no mutation)
    // ---------------------------------------------------------------------

    /**
     * Returns a new unit vector pointing up, `(0, 1, 0)`.
     */
    public static function up(): self
    {
        return new self(0.0, 1.0, 0.0);
    }

    /**
     * Returns a new unit vector pointing along +Z, `(0, 0, 1)`.
     *
     * A {@see Transform} faces its local -Z axis, so {@see Transform::$forward} points along
     * {@see Vector3::back()} rotated by the Transform's rotation.
     */
    public static function forward(): self
    {
        return new self(0.0, 0.0, 1.0);
    }

    /**
     * Returns a new vector made from the largest X, Y and Z of two vectors.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function max(Vector3 $a, Vector3 $b): self
    {
        return new self(
            max($a->x, $b->x),
            max($a->y, $b->y),
            max($a->z, $b->z),
        );
    }

    /**
     * Returns a new vector made from the smallest X, Y and Z of two vectors.
     *
     * @param Vector3 $a First vector.
     * @param Vector3 $b Second vector.
     */
    public static function min(Vector3 $a, Vector3 $b): self
    {
        return new self(
            min($a->x, $b->x),
            min($a->y, $b->y),
            min($a->z, $b->z),
        );
    }

    /**
     * Returns a copy of a vector with its magnitude clamped to maxLength.
     *
     * Vectors already within the limit are returned unchanged. Use it to cap speeds.
     *
     * @param Vector3 $vector The vector to clamp.
     * @param float $maxLength The maximum magnitude of the vector. Negative values are treated as `0`.
     *
     * @return Vector3 A new vector with the same direction as the original, but clamped to maxLength.
     */
    public static function clampMagnitude(Vector3 $vector, float $maxLength): self
    {
        $maxLength = max(0.0, $maxLength);
        if ($vector->sqrMagnitude > $maxLength * $maxLength) {
            $normalized = $vector->normalized;
            return new self(
                $normalized->x * $maxLength,
                $normalized->y * $maxLength,
                $normalized->z * $maxLength,
            );
        }

        return new self($vector->x, $vector->y, $vector->z);
    }

    /**
     * Returns a point moved from `$current` toward `$target` by at most `$maxDelta`.
     *
     * The result never passes the target. Call it every frame with
     * `$speed * Time::deltaTime()` to move at a steady speed.
     *
     * @param Vector3 $current Starting point.
     * @param Vector3 $target Point to move toward.
     * @param float $maxDelta Largest distance to move. A negative value moves away from the target.
     * @return Vector3 The new point.
     */
    public static function moveTowards(Vector3 $current, Vector3 $target, float $maxDelta): self
    {
        $dx = $target->x - $current->x;
        $dy = $target->y - $current->y;
        $dz = $target->z - $current->z;
        $sqrDist = $dx * $dx + $dy * $dy + $dz * $dz;

        if ($sqrDist <= self::EPSILON ||
            ($maxDelta >= 0.0 && $sqrDist <= $maxDelta * $maxDelta)
        ) {
            return new self($target->x, $target->y, $target->z);
        }

        $dist = sqrt($sqrDist);

        return new self(
            $current->x + $dx / $dist * $maxDelta,
            $current->y + $dy / $dist * $maxDelta,
            $current->z + $dz / $dist * $maxDelta,
        );
    }

    /**
     * Reflects a vector off the plane defined by a normal vector.
     *
     * Use it to bounce a ball or projectile off a surface.
     *
     * @param Vector3 $inDirection The incoming direction.
     * @param Vector3 $inNormal The surface normal. It should have a length of 1.
     * @return Vector3 The reflected direction.
     */
    public static function reflect(Vector3 $inDirection, Vector3 $inNormal): self
    {
        $dot = self::dot($inDirection, $inNormal);

        return new self(
            $inDirection->x - 2.0 * $dot * $inNormal->x,
            $inDirection->y - 2.0 * $dot * $inNormal->y,
            $inDirection->z - 2.0 * $dot * $inNormal->z,
        );
    }

    /**
     * Turns one vector toward another by a limited angle, and moves its length toward the other's length.
     *
     * Use it to turn a direction gradually, for example a homing missile's heading. Call it
     * every frame with steps multiplied by {@see Time::deltaTime()}. The result never turns
     * past the target direction.
     *
     * @param Vector3 $current Vector to turn.
     * @param Vector3 $target Vector to turn toward.
     * @param float $maxRadiansDelta Largest angle to turn, in radians. A negative value turns
     *     toward the opposite of `$target`.
     * @param float $maxMagnitudeDelta Largest change in length.
     * @return Vector3 The turned vector.
     */
    public static function rotateTowards(
        Vector3 $current,
        Vector3 $target,
        float   $maxRadiansDelta,
        float   $maxMagnitudeDelta,
    ): self
    {
        $currentMagnitude = $current->magnitude;
        $targetMagnitude = $target->magnitude;
        $nextMagnitude = self::moveTowardsScalar($currentMagnitude, $targetMagnitude, $maxMagnitudeDelta);

        if ($currentMagnitude <= self::EPSILON) {
            if ($targetMagnitude <= self::EPSILON) {
                return self::zero();
            }

            $targetDirection = $target->normalized;
            return new self(
                $targetDirection->x * $nextMagnitude,
                $targetDirection->y * $nextMagnitude,
                $targetDirection->z * $nextMagnitude,
            );
        }

        $currentDirection = new self(
            $current->x / $currentMagnitude,
            $current->y / $currentMagnitude,
            $current->z / $currentMagnitude,
        );

        if ($targetMagnitude <= self::EPSILON) {
            $targetDirection = $currentDirection;
        } elseif ($maxRadiansDelta < 0.0) {
            $targetDirection = new self(
                -$target->x / $targetMagnitude,
                -$target->y / $targetMagnitude,
                -$target->z / $targetMagnitude,
            );
        } else {
            $targetDirection = new self(
                $target->x / $targetMagnitude,
                $target->y / $targetMagnitude,
                $target->z / $targetMagnitude,
            );
        }

        $angleRadians = acos(self::clamp(self::dot($currentDirection, $targetDirection), -1.0, 1.0));
        if ($angleRadians <= self::EPSILON) {
            return new self(
                $targetDirection->x * $nextMagnitude,
                $targetDirection->y * $nextMagnitude,
                $targetDirection->z * $nextMagnitude,
            );
        }

        $step = min(1.0, abs($maxRadiansDelta) / $angleRadians);
        $rotatedDirection = self::slerpUnclampedInternal($currentDirection, $targetDirection, $step)->normalized;

        return new self(
            $rotatedDirection->x * $nextMagnitude,
            $rotatedDirection->y * $nextMagnitude,
            $rotatedDirection->z * $nextMagnitude,
        );
    }

    private static function moveTowardsScalar(float $current, float $target, float $maxDelta): float
    {
        $delta = $target - $current;
        if ($maxDelta >= 0.0 && abs($delta) <= $maxDelta) {
            return $target;
        }

        $direction = $delta >= 0.0 ? 1.0 : -1.0;
        $signedStep = $direction * abs($maxDelta) * ($maxDelta >= 0.0 ? 1.0 : -1.0);

        return $current + $signedStep;
    }

    /**
     * Returns a new vector with all components set to 0.
     */
    public static function zero(): self
    {
        return new self(0.0, 0.0, 0.0);
    }

    /**
     * Moves a value toward a target smoothly, slowing down as it gets close.
     *
     * Use it for camera follow and other motion that should ease in. Call it every frame and
     * keep the same `$currentVelocity` variable between calls.
     *
     * ```php
     * $this->transform->position = Vector3::smoothDamp(
     *     $this->transform->position,
     *     $targetPosition,
     *     $this->followVelocity,
     *     0.25,
     * );
     * ```
     *
     * @param Vector3 $current Current position.
     * @param Vector3 $target Target position.
     * @param Vector3 &$currentVelocity Current velocity. Modified in place on each call.
     * @param float $smoothTime Approximate time in seconds to reach the target.
     * @param float $maxSpeed Highest speed allowed, in units per second. Defaults to no limit.
     * @param float|null $deltaTime Time in seconds since the last call. Defaults to
     *     {@see Time::deltaTime()}. When it is `0` or less, `$current` is returned unchanged.
     * @return Vector3 The new position.
     * @throws \Lenga\Engine\Exceptions\LengaRuntimeUnavailableException When the Lenga runtime is not loaded.
     */
    public static function smoothDamp(
        Vector3 $current,
        Vector3 $target,
        Vector3 &$currentVelocity,
        float   $smoothTime,
        float   $maxSpeed = PHP_FLOAT_MAX,
        ?float  $deltaTime = null,
    ): self
    {
        $resolvedDeltaTime = $deltaTime ?? Time::deltaTime();
        if ($resolvedDeltaTime <= 0.0) {
            return new self($current->x, $current->y, $current->z);
        }

        /** @var array{
         *     result?: array{x?: float, y?: float, z?: float},
         *     currentVelocity?: array{x?: float, y?: float, z?: float}
         * }|false $result
         */
        $result = NativeEngine::call('vector3_smooth_damp',
            $current->x,
            $current->y,
            $current->z,
            $target->x,
            $target->y,
            $target->z,
            $currentVelocity->x,
            $currentVelocity->y,
            $currentVelocity->z,
            $smoothTime,
            $maxSpeed,
            $resolvedDeltaTime,
        );

        if (!is_array($result)) {
            return new self($current->x, $current->y, $current->z);
        }

        if (is_array($result['currentVelocity'] ?? null)) {
            $currentVelocity = self::fromArray($result['currentVelocity']);
        }

        if (is_array($result['result'] ?? null)) {
            return self::fromArray($result['result']);
        }

        return new self($current->x, $current->y, $current->z);
    }

    /**
     * Makes normal and tangent orthogonal and normalized using Gram-Schmidt.
     *
     * `$normal` is normalized. `$tangent` is then made perpendicular to it and normalized.
     * If the tangent is parallel to the normal, an arbitrary perpendicular tangent is chosen.
     * Use it to build a set of axes from a surface normal.
     *
     * @param Vector3 &$normal Normal to normalize. Replaced with the result.
     * @param Vector3 &$tangent Tangent to straighten. Replaced with the result.
     */
    public static function orthoNormalize(Vector3 &$normal, Vector3 &$tangent): void
    {
        $normal = $normal->normalized;
        $tangent = self::projectOnPlane($tangent, $normal);
        if ($tangent->sqrMagnitude <= self::EPSILON) {
            $tangent = self::buildOrthogonalVector($normal);
        }

        $tangent = $tangent->normalized;
    }

    /**
     * Projects a vector onto a plane defined by the given normal.
     *
     * The result is the part of `$vector` that lies along the plane. Use it to keep movement
     * on the ground, for example a camera's forward direction without its up or down tilt.
     *
     * @param Vector3 $vector Vector to project.
     * @param Vector3 $planeNormal Normal of the plane. It does not need to be normalized.
     * @return Vector3 The projected vector.
     */
    public static function projectOnPlane(Vector3 $vector, Vector3 $planeNormal): self
    {
        $projection = self::project($vector, $planeNormal);

        return new self(
            $vector->x - $projection->x,
            $vector->y - $projection->y,
            $vector->z - $projection->z,
        );
    }

    /**
     * Projects a vector onto another vector.
     *
     * The result is the part of `$vector` that points along `$onNormal`.
     *
     * @param Vector3 $vector Vector to project.
     * @param Vector3 $onNormal Direction to project onto. It does not need to be normalized.
     * @return Vector3 The projected vector, or `(0, 0, 0)` when `$onNormal` has zero length.
     */
    public static function project(Vector3 $vector, Vector3 $onNormal): self
    {
        $denominator = $onNormal->sqrMagnitude;
        if ($denominator <= self::EPSILON) {
            return self::zero();
        }

        $scale = self::dot($vector, $onNormal) / $denominator;

        return new self(
            $onNormal->x * $scale,
            $onNormal->y * $scale,
            $onNormal->z * $scale,
        );
    }

    private static function buildOrthogonalVector(Vector3 $vector): self
    {
        if ($vector->sqrMagnitude <= self::EPSILON) {
            return self::right();
        }

        $orthogonal = self::cross(
            $vector,
            abs($vector->z) < 0.999 ? self::forward() : self::up(),
        );

        if ($orthogonal->sqrMagnitude <= self::EPSILON) {
            $orthogonal = self::cross($vector, self::right());
        }

        return $orthogonal->normalized;
    }

    /**
     * Adds another vector to this vector in place.
     *
     * @param Vector3 $other Vector to add.
     * @return self This vector, for chaining.
     */
    public function add(Vector3 $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        $this->z += $other->z;

        return $this;
    }

    /**
     * Subtracts another vector from this vector in place.
     *
     * @param Vector3 $other Vector to subtract.
     * @return self This vector, for chaining.
     */
    public function subtract(Vector3 $other): self
    {
        $this->x -= $other->x;
        $this->y -= $other->y;
        $this->z -= $other->z;

        return $this;
    }

    /**
     * Multiplies this vector component by component with another vector, in place.
     *
     * @param Vector3 $other Vector whose components multiply this vector's components.
     * @return self This vector, for chaining.
     */
    public function multiply(Vector3 $other): self
    {
        $this->x *= $other->x;
        $this->y *= $other->y;
        $this->z *= $other->z;

        return $this;
    }

    /**
     * Divides this vector component by component by another vector, in place.
     *
     * @param Vector3 $other Vector whose components divide this vector's components.
     * @return self This vector, for chaining.
     * @throws \DivisionByZeroError When a component of `$other` is `0`.
     */
    public function divide(Vector3 $other): self
    {
        $this->x /= $other->x;
        $this->y /= $other->y;
        $this->z /= $other->z;

        return $this;
    }

    /**
     * Multiplies all components by a number, in place.
     *
     * @param float $scalar Number to multiply by.
     * @return self This vector, for chaining.
     */
    public function scale(float $scalar): self
    {
        $this->x *= $scalar;
        $this->y *= $scalar;
        $this->z *= $scalar;

        return $this;
    }

    /**
     * Changes this vector to a length of 1, keeping its direction.
     *
     * A vector shorter than about `0.000001` stays unchanged. Use {@see Vector3::$normalized}
     * to get a normalized copy instead.
     *
     * @return self This vector, for chaining.
     */
    public function normalize(): self
    {
        $magnitude = $this->magnitude;
        if ($magnitude <= self::EPSILON) {
            return $this;
        }

        $this->x /= $magnitude;
        $this->y /= $magnitude;
        $this->z /= $magnitude;

        return $this;
    }

    /**
     * Sets the x, y, and z components of this vector.
     *
     * @param float $x New X component.
     * @param float $y New Y component.
     * @param float $z New Z component.
     */
    public function set(float $x, float $y, float $z): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Returns a copy of this vector.
     *
     * Change the copy without affecting the original.
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Returns whether all components exactly equal those of another vector.
     *
     * The comparison is exact, so results of calculations that differ by rounding are not
     * equal. Compare {@see Vector3::distance()} against a small tolerance for those.
     *
     * @param Vector3 $other Vector to compare with.
     */
    public function equals(Vector3 $other): bool
    {
        return $this->x === $other->x
            && $this->y === $other->y
            && $this->z === $other->z;
    }

    /**
     * Returns the vector as text in the form `(x, y, z)`, for logging.
     */
    public function __toString(): string
    {
        return "($this->x, $this->y, $this->z)";
    }

    /**
     * Returns a new Vector2 with this vector's X and Y, dropping Z.
     */
    public function toVector2(): Vector2
    {
        return new Vector2($this->x, $this->y);
    }

    /**
     * Returns a new Vector4 with this vector's X, Y and Z.
     *
     * @param float $w W component of the result. Defaults to `0`.
     */
    public function toVector4(float $w = 0.0): Vector4
    {
        return new Vector4($this->x, $this->y, $this->z, $w);
    }

    /**
     * Returns the components as an array with `x`, `y` and `z` keys.
     *
     * @return array{x: float, y: float, z: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    /**
     * Reads a component with array syntax, as in `$vector[0]` or `$vector['z']`.
     *
     * @param mixed $offset `0` or `'x'`, `1` or `'y'`, `2` or `'z'`.
     * @return float The component value.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetGet(mixed $offset): float
    {
        $offset = $this->normalizeOffset($offset);

        return match ($offset) {
            0 => $this->x,
            1 => $this->y,
            2 => $this->z,
            default => throw new OutOfBoundsException("Offset $offset does not exist."),
        };
    }

    private function normalizeOffset(mixed $offset): mixed
    {
        return match ($offset) {
            'x' => 0,
            'y' => 1,
            'z' => 2,
            default => $offset,
        };
    }

    /**
     * Writes a component with array syntax, as in `$vector['y'] = 2.0`.
     *
     * @param mixed $offset `0` or `'x'`, `1` or `'y'`, `2` or `'z'`.
     * @param mixed $value New component value.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset = $this->normalizeOffset($offset);

        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Offset $offset does not exist.");
        }

        if ($offset === 0) {
            $this->x = $value;
        }

        if ($offset === 1) {
            $this->y = $value;
        }

        if ($offset === 2) {
            $this->z = $value;
        }
    }

    /**
     * Returns whether an array offset names a component, for `isset($vector[...])`.
     *
     * @param mixed $offset `0` or `'x'`, `1` or `'y'`, `2` or `'z'`.
     * @return bool `true` for a valid component offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeOffset($offset);

        return $offset === 0 || $offset === 1 || $offset === 2;
    }

    /**
     * Handles `unset($vector[...])`. A vector always has all three components, so this cannot remove one.
     *
     * Unsetting a valid component throws an `Error`, because the components cannot be
     * unset. Set the component to `0.0` instead.
     *
     * @param mixed $offset `0` or `'x'`, `1` or `'y'`, `2` or `'z'`.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetUnset(mixed $offset): void
    {
        $offset = $this->normalizeOffset($offset);

        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Offset $offset does not exist.");
        }

        $property = match ($offset) {
            0 => 'x',
            1 => 'y',
            default => 'z',
        };

        unset($this->{$property});
    }
}
