<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;

/**
 * 2D vector for positions, directions, velocities and sizes in 2D gameplay code.
 *
 * Lenga uses Vector2 for 2D forces, touch positions and sprite settings. It has
 * two kinds of operations:
 *
 * - Instance methods such as {@see Vector2::add()} and {@see Vector2::scale()} change the
 *   vector itself and return it, so calls can be chained.
 * - Static methods such as {@see Vector2::sum()} and {@see Vector2::distance()} leave their
 *   inputs alone and return a new value.
 *
 * Components can also be read and written like an array, with `0` or `'x'` and `1` or
 * `'y'`. Convert to 3D with {@see Vector2::toVector3()}, for example to assign a
 * {@see Transform::$position}.
 *
 * ```php
 * $position = Vector2::fromVector3($this->transform->position);
 * $toPlayer = Vector2::difference($playerPosition, $position);
 *
 * if ($toPlayer->sqrMagnitude < $this->chaseRange * $this->chaseRange) {
 *     $step = $toPlayer->normalized->scale($this->speed * Time::deltaTime());
 *     $this->transform->translate2D($step, false);
 * }
 * ```
 */
final class Vector2 implements ArrayAccess
{
    /**
     * X component.
     */
    public float $x {
        get {
            return $this->data->x;
        }
        set {
            $this->data->x = $value;
        }
    }
    /**
     * Y component.
     */
    public float $y {
        get {
            return $this->data->y;
        }
        set {
            $this->data->y = $value;
        }
    }

    /**
     * Squared length of the vector. Read-only.
     *
     * Cheaper than {@see Vector2::$magnitude} because it skips the square root. Use it to
     * compare lengths, for example `$offset->sqrMagnitude < $range * $range`.
     */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y;
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
     * Returns `(0, 0)` when the vector has zero length. The vector itself does not change.
     * Use {@see Vector2::normalize()} to change it in place.
     */
    public Vector2 $normalized {
        get {
            if ($this->sqrMagnitude === 0.0) {
                return new self();
            }

            $magnitude = $this->magnitude;

            return new self(
                $this->x / $magnitude,
                $this->y / $magnitude,
            );
        }
    }

    private Vector3 $data;

    /**
     * Creates a vector from its components.
     *
     * @param float $x X component. Defaults to `0`.
     * @param float $y Y component. Defaults to `0`.
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
    )
    {
        $this->data = new Vector3($x, $y);
    }

    /**
     * Returns a new unit vector pointing down, `(0, -1)`.
     */
    public static function down(): self
    {
        return new self(0, -1);
    }

    /**
     * Returns a new unit vector pointing up, `(0, 1)`.
     */
    public static function up(): self
    {
        return new self(0, 1);
    }

    /**
     * Returns a new unit vector pointing left, `(-1, 0)`.
     */
    public static function left(): self
    {
        return new self(-1, 0);
    }

    /**
     * Returns a new unit vector pointing right, `(1, 0)`.
     */
    public static function right(): self
    {
        return new self(1, 0);
    }

    /**
     * Returns a new vector with both components set to 1.
     */
    public static function one(): self
    {
        return new self(1, 1);
    }

    /**
     * Returns a new vector with both components set to 0.
     */
    public static function zero(): self
    {
        return new self(0, 0);
    }

    /**
     * Returns a new vector with both components set to negative infinity.
     *
     * Useful as a starting value when searching for the largest value with {@see Vector2::max()}.
     */
    public static function negativeInfinity(): self
    {
        return new self(-INF, -INF);
    }

    /**
     * Returns a new vector with both components set to positive infinity.
     *
     * Useful as a starting value when searching for the smallest value with {@see Vector2::min()}.
     */
    public static function positiveInfinity(): self
    {
        return new self(INF, INF);
    }

    /**
     * Returns whether an array offset names a component, for `isset($vector[...])`.
     *
     * @param mixed $offset `0` or `'x'` for X, `1` or `'y'` for Y.
     * @return bool `true` for a valid component offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeOffset($offset);

        return $offset === 0 || $offset === 1;
    }

    /**
     * Reads a component with array syntax, as in `$vector[0]` or `$vector['y']`.
     *
     * @param mixed $offset `0` or `'x'` for X, `1` or `'y'` for Y.
     * @return mixed The component value.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetGet(mixed $offset): mixed
    {
        $offset = $this->normalizeOffset($offset);

        return match ($offset) {
            0 => $this->x,
            1 => $this->y,
            default => throw new OutOfBoundsException("Offset $offset does not exist."),
        };
    }

    /**
     * Writes a component with array syntax, as in `$vector['x'] = 2.0`.
     *
     * @param mixed $offset `0` or `'x'` for X, `1` or `'y'` for Y.
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
            $this->data->x = $value;
        }

        if ($offset === 1) {
            $this->data->y = $value;
        }
    }

    /**
     * Handles `unset($vector[...])`. A vector always has both components, so this cannot remove one.
     *
     * Unsetting a valid component throws an `Error`, because the components cannot be
     * unset. Set the component to `0.0` instead.
     *
     * @param mixed $offset `0` or `'x'` for X, `1` or `'y'` for Y.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetUnset(mixed $offset): void
    {
        $offset = $this->normalizeOffset($offset);

        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Offset $offset does not exist.");
        }

        $property = $offset === 0 ? 'x' : 'y';

        unset($this->data->{$property});
    }

    private function normalizeOffset(mixed $offset): mixed
    {
        return match ($offset) {
            'x' => 0,
            'y' => 1,
            default => $offset,
        };
    }

    // -------------------------------------------------------------------------
    // In-place operations (mutate this instance; return self for chaining)
    // -------------------------------------------------------------------------

    /**
     * Adds another vector to this vector in place.
     *
     * @param Vector2 $other Vector to add.
     * @return self This vector, for chaining.
     */
    public function add(Vector2 $other): self
    {
        $this->data->x += $other->x;
        $this->data->y += $other->y;

        return $this;
    }

    /**
     * Subtracts another vector from this vector in place.
     *
     * @param Vector2 $other Vector to subtract.
     * @return self This vector, for chaining.
     */
    public function subtract(Vector2 $other): self
    {
        $this->data->x -= $other->x;
        $this->data->y -= $other->y;

        return $this;
    }

    /**
     * Multiplies this vector component by component with another vector, in place.
     *
     * @param Vector2 $other Vector whose X and Y multiply this vector's X and Y.
     * @return self This vector, for chaining.
     */
    public function multiply(Vector2 $other): self
    {
        $this->data->x *= $other->x;
        $this->data->y *= $other->y;

        return $this;
    }

    /**
     * Divides this vector component by component by another vector, in place.
     *
     * @param Vector2 $other Vector whose X and Y divide this vector's X and Y.
     * @return self This vector, for chaining.
     * @throws \DivisionByZeroError When a component of `$other` is `0`.
     */
    public function divide(Vector2 $other): self
    {
        $this->data->x /= $other->x;
        $this->data->y /= $other->y;

        return $this;
    }

    /**
     * Multiplies both components by a number, in place.
     *
     * @param float $scalar Number to multiply by.
     * @return self This vector, for chaining.
     */
    public function scale(float $scalar): self
    {
        $this->data->x *= $scalar;
        $this->data->y *= $scalar;

        return $this;
    }

    /**
     * Changes this vector to a length of 1, keeping its direction.
     *
     * A vector with zero length stays unchanged. Use {@see Vector2::$normalized} to get a
     * normalized copy instead.
     *
     * @return self This vector, for chaining.
     */
    public function normalize(): self
    {
        $m = $this->magnitude;
        if ($m === 0.0) {
            return $this;
        }

        $this->data->x /= $m;
        $this->data->y /= $m;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Instance convenience methods
    // -------------------------------------------------------------------------

    /**
     * Sets both components of this vector.
     *
     * @param float $x New X component.
     * @param float $y New Y component.
     */
    public function set(float $x, float $y): void
    {
        $this->data->x = $x;
        $this->data->y = $y;
    }

    /**
     * Returns whether both components exactly equal those of another vector.
     *
     * The comparison is exact, so results of calculations that differ by rounding are not
     * equal. Compare {@see Vector2::distance()} against a small tolerance for those.
     *
     * @param Vector2 $other Vector to compare with.
     */
    public function equals(Vector2 $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }

    /**
     * Returns the vector as text in the form `(x, y)`, for logging.
     */
    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }

    /**
     * Returns a new Vector3 with this vector's X and Y.
     *
     * @param float $z Z component of the result. Defaults to `0`.
     */
    public function toVector3(float $z = 0.0): Vector3
    {
        return new Vector3($this->x, $this->y, $z);
    }
    /**
     * Returns a new Vector4 with this vector's X and Y.
     *
     * @param float $z Z component of the result. Defaults to `0`.
     * @param float $w W component of the result. Defaults to `0`.
     */
    public function toVector4(float $z = 0.0, float $w = 0.0): Vector4
    {
        return new Vector4($this->x, $this->y, $z, $w);
    }


    // -------------------------------------------------------------------------
    // Static pure helpers (return new vectors; no mutation)
    // -------------------------------------------------------------------------

    /**
     * Returns the sum of two vectors as a new vector.
     *
     * @param Vector2 $a First vector.
     * @param Vector2 $b Second vector.
     */
    public static function sum(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x + $b->x, $a->y + $b->y);
    }

    /**
     * Returns `$a` minus `$b` as a new vector.
     *
     * The result points from `$b` to `$a`.
     *
     * @param Vector2 $a Vector to subtract from.
     * @param Vector2 $b Vector to subtract.
     */
    public static function difference(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x - $b->x, $a->y - $b->y);
    }

    /**
     * Returns a new vector equal to a vector multiplied by a number.
     *
     * @param Vector2 $v Vector to scale.
     * @param float $scalar Number to multiply by.
     */
    public static function scaleNew(Vector2 $v, float $scalar): Vector2
    {
        return new self($v->x * $scalar, $v->y * $scalar);
    }

    /**
     * Returns a new vector made by multiplying two vectors component by component.
     *
     * @param Vector2 $a First vector.
     * @param Vector2 $b Second vector.
     */
    public static function product(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x * $b->x, $a->y * $b->y);
    }

    /**
     * Returns the dot product of two vectors.
     *
     * For unit vectors the result is `1` when they point the same way, `0` when they are at
     * right angles and `-1` when they point in opposite directions.
     *
     * @param Vector2 $a First vector.
     * @param Vector2 $b Second vector.
     */
    public static function dot(Vector2 $a, Vector2 $b): float
    {
        return $a->x * $b->x + $a->y * $b->y;
    }

    /**
     * Returns the angle between two vectors in degrees, from 0 to 180.
     *
     * Returns `0` when either vector has zero length.
     *
     * @param Vector2 $from Direction the angle is measured from.
     * @param Vector2 $to Direction the angle is measured to.
     */
    public static function angle(Vector2 $from, Vector2 $to): float
    {
        $denominator = $from->magnitude * $to->magnitude;
        if ($denominator === 0.0) {
            return 0.0;
        }

        $dot = \max(-1.0, \min(1.0, self::dot($from, $to) / $denominator));

        return \rad2deg(\acos($dot));
    }

    /**
     * Returns the angle between two vectors in degrees, from -180 to 180.
     *
     * The result is positive when turning from `$from` to `$to` is counter-clockwise and
     * negative when it is clockwise.
     *
     * @param Vector2 $from Direction the angle is measured from.
     * @param Vector2 $to Direction the angle is measured to.
     */
    public static function signedAngle(Vector2 $from, Vector2 $to): float
    {
        $unsigned = self::angle($from, $to);
        $cross = $from->x * $to->y - $from->y * $to->x;

        return $cross < 0.0 ? -$unsigned : $unsigned;
    }

    /**
     * Returns the distance between two points.
     *
     * @param Vector2 $a First point.
     * @param Vector2 $b Second point.
     */
    public static function distance(Vector2 $a, Vector2 $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;

        return \sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * Returns the point a fraction of the way from `$a` to `$b`.
     *
     * `$t` is clamped to the range 0 to 1, so the result never goes past either end.
     *
     * @param Vector2 $a Value at `$t = 0`.
     * @param Vector2 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`, from 0 to 1.
     * @return Vector2 The interpolated vector.
     */
    public static function lerp(Vector2 $a, Vector2 $b, float $t): Vector2
    {
        $t = \max(0.0, \min(1.0, $t));

        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
        );
    }

    /**
     * Returns the point a fraction of the way from `$a` to `$b`, without clamping `$t`.
     *
     * Values of `$t` below 0 or above 1 extend past `$a` or `$b`.
     *
     * @param Vector2 $a Value at `$t = 0`.
     * @param Vector2 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`.
     * @return Vector2 The interpolated vector.
     */
    public static function lerpUnclamped(Vector2 $a, Vector2 $b, float $t): Vector2
    {
        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
        );
    }

    /**
     * Returns a new vector made from the larger X and the larger Y of two vectors.
     *
     * @param Vector2 $a First vector.
     * @param Vector2 $b Second vector.
     */
    public static function max(Vector2 $a, Vector2 $b): Vector2
    {
        return new self(\max($a->x, $b->x), \max($a->y, $b->y));
    }

    /**
     * Returns a new vector made from the smaller X and the smaller Y of two vectors.
     *
     * @param Vector2 $a First vector.
     * @param Vector2 $b Second vector.
     */
    public static function min(Vector2 $a, Vector2 $b): Vector2
    {
        return new self(\min($a->x, $b->x), \min($a->y, $b->y));
    }

    /**
     * Returns a copy of a vector shortened to a maximum length.
     *
     * Vectors already within the limit are returned unchanged. Use it to cap speeds.
     *
     * @param Vector2 $vector Vector to limit.
     * @param float $maxLength Largest length allowed. Use a value of 0 or more; a negative
     *     value flips the direction of a vector that is longer than it.
     * @return Vector2 A new vector with the same direction and a length of at most `$maxLength`.
     */
    public static function clampMagnitude(Vector2 $vector, float $maxLength): Vector2
    {
        if ($vector->sqrMagnitude > $maxLength * $maxLength) {
            return new self(
                $vector->x / $vector->magnitude * $maxLength,
                $vector->y / $vector->magnitude * $maxLength,
            );
        }

        return new self($vector->x, $vector->y);
    }

    /**
     * Returns a point moved from `$current` toward `$target` by at most `$maxDelta`.
     *
     * The result never passes the target. Call it every frame with
     * `$speed * Time::deltaTime()` to move at a steady speed.
     *
     * @param Vector2 $current Starting point.
     * @param Vector2 $target Point to move toward.
     * @param float $maxDelta Largest distance to move. A negative value moves away from the target.
     * @return Vector2 The new point.
     */
    public static function moveTowards(Vector2 $current, Vector2 $target, float $maxDelta): Vector2
    {
        $dx = $target->x - $current->x;
        $dy = $target->y - $current->y;
        $sqrDist = $dx * $dx + $dy * $dy;

        if ($sqrDist === 0.0 || ($maxDelta >= 0.0 && $sqrDist <= $maxDelta * $maxDelta)) {
            return new self($target->x, $target->y);
        }

        $dist = \sqrt($sqrDist);

        return new self(
            $current->x + $dx / $dist * $maxDelta,
            $current->y + $dy / $dist * $maxDelta,
        );
    }

    /**
     * Returns a new vector at right angles to a direction, turned 90 degrees counter-clockwise.
     *
     * For `(x, y)` the result is `(-y, x)`.
     *
     * @param Vector2 $inDirection Direction to turn.
     */
    public static function perpendicular(Vector2 $inDirection): Vector2
    {
        return new self(-$inDirection->y, $inDirection->x);
    }

    /**
     * Reflects a vector off the surface defined by a normal.
     *
     * Use it to bounce a ball or projectile off a wall.
     *
     * @param Vector2 $inDirection The incoming direction vector.
     * @param Vector2 $inNormal The surface normal. It should have a length of 1.
     * @return Vector2 The reflected direction.
     */
    public static function reflect(Vector2 $inDirection, Vector2 $inNormal): Vector2
    {
        $dot = self::dot($inDirection, $inNormal);

        return new self(
            $inDirection->x - 2.0 * $dot * $inNormal->x,
            $inDirection->y - 2.0 * $dot * $inNormal->y,
        );
    }

    /**
     * Moves a value toward a target smoothly, slowing down as it gets close.
     *
     * Use it for camera follow and other motion that should ease in. Call it every frame and
     * keep the same `$currentVelocity` variable between calls.
     *
     * ```php
     * $this->position = Vector2::smoothDamp(
     *     $this->position,
     *     $target,
     *     $this->velocity,
     *     0.3,
     *     deltaTime: Time::deltaTime(),
     * );
     * ```
     *
     * @param Vector2 $current Current position.
     * @param Vector2 $target Target position.
     * @param Vector2 &$currentVelocity Current velocity, modified by this method on each call.
     * @param float $smoothTime Approximate time in seconds to reach the target. Values below
     *     `0.0001` are treated as `0.0001`.
     * @param float $maxSpeed Highest speed allowed, in units per second. Defaults to no limit.
     * @param float $deltaTime Time in seconds since the last call. Pass {@see Time::deltaTime()}.
     *     With the default of `0` the method returns `$current` unchanged.
     * @return Vector2 The new position.
     */
    public static function smoothDamp(
        Vector2 $current,
        Vector2 $target,
        Vector2 &$currentVelocity,
        float $smoothTime,
        float $maxSpeed = PHP_FLOAT_MAX,
        float $deltaTime = 0.0,
    ): Vector2 {
        if ($deltaTime <= 0.0) {
            return new self($current->x, $current->y);
        }

        $smoothTime = \max(0.0001, $smoothTime);
        $omega = 2.0 / $smoothTime;
        $x = $omega * $deltaTime;
        $exp = 1.0 / (1.0 + $x + 0.48 * $x * $x + 0.235 * $x * $x * $x);

        $changeX = $current->x - $target->x;
        $changeY = $current->y - $target->y;
        $origTargetX = $target->x;
        $origTargetY = $target->y;

        $maxChange = $maxSpeed * $smoothTime;
        $maxChangeSq = $maxChange * $maxChange;
        $sqrMag = $changeX * $changeX + $changeY * $changeY;

        if ($sqrMag > $maxChangeSq) {
            $mag = \sqrt($sqrMag);
            $changeX = $changeX / $mag * $maxChange;
            $changeY = $changeY / $mag * $maxChange;
        }

        $targetX = $current->x - $changeX;
        $targetY = $current->y - $changeY;

        $tempX = ($currentVelocity->x + $omega * $changeX) * $deltaTime;
        $tempY = ($currentVelocity->y + $omega * $changeY) * $deltaTime;

        $currentVelocity = new self(
            ($currentVelocity->x - $omega * $tempX) * $exp,
            ($currentVelocity->y - $omega * $tempY) * $exp,
        );

        $outputX = $targetX + ($changeX + $tempX) * $exp;
        $outputY = $targetY + ($changeY + $tempY) * $exp;

        // Prevent overshooting
        if (($origTargetX - $current->x) * ($outputX - $origTargetX)
            + ($origTargetY - $current->y) * ($outputY - $origTargetY) > 0.0
        ) {
            $outputX = $origTargetX;
            $outputY = $origTargetY;
            $currentVelocity = new self(0.0, 0.0);
        }

        return new self($outputX, $outputY);
    }

    /**
     * Returns a new Vector2 with the X and Y of a Vector3, dropping Z.
     *
     * @param Vector3 $v Vector to convert.
     */
    public static function fromVector3(Vector3 $v): self
    {
        return new self($v->x, $v->y);
    }

    /**
     * Returns a new Vector2 with the X and Y of a Vector4, dropping Z and W.
     *
     * @param Vector4 $v Vector to convert.
     */
    public static function fromVector4(Vector4 $v): self
    {
        return new self($v->x, $v->y);
    }
}
