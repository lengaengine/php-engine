<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;

/**
 * 2D vector value type mirroring Unity's UnityEngine.Vector2.
 *
 * Backed internally by a Vector3 data store (z is always 0).
 * Supports array-style component access using offsets 0/'x' and 1/'y'.
 */
final class Vector2 implements ArrayAccess
{
    /** X-axis component. */
    public float $x {
        get {
            return $this->data->x;
        }
        set {
            $this->data->x = $value;
        }
    }
    /** Y-axis component. */
    public float $y {
        get {
            return $this->data->y;
        }
        set {
            $this->data->y = $value;
        }
    }

    /** Squared length of the vector (avoids sqrt when only relative lengths are needed). */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y;
        }
    }

    /** Euclidean length of the vector. */
    public float $magnitude {
        get {
            return sqrt($this->sqrMagnitude);
        }
    }

    /** Returns a normalized copy of this vector, or zero when the vector has zero length. */
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
     * @param float $x
     * @param float $y
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
    )
    {
        $this->data = new Vector3($x, $y);
    }

    /** Unit vector pointing down (0, -1). */
    public static function down(): self
    {
        return new self(0, -1);
    }

    /** Unit vector pointing up (0, 1). */
    public static function up(): self
    {
        return new self(0, 1);
    }

    /** Unit vector pointing left (-1, 0). */
    public static function left(): self
    {
        return new self(-1, 0);
    }

    /** Unit vector pointing right (1, 0). */
    public static function right(): self
    {
        return new self(1, 0);
    }

    /** Vector with all components set to 1. */
    public static function one(): self
    {
        return new self(1, 1);
    }

    /** Vector with all components set to 0. */
    public static function zero(): self
    {
        return new self(0, 0);
    }

    /** Shorthand for Vector2(-INF, -INF). */
    public static function negativeInfinity(): self
    {
        return new self(-INF, -INF);
    }

    /** Shorthand for Vector2(INF, INF). */
    public static function positiveInfinity(): self
    {
        return new self(INF, INF);
    }

    /**
     * Checks whether an array offset maps to a valid vector component.
     * Supports 0/'x' and 1/'y'.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeOffset($offset);

        return $offset === 0 || $offset === 1;
    }

    /**
     * Gets a vector component by index: 0|'x' => x, 1|'y' => y.
     *
     * @param mixed $offset
     * @return mixed
     * @throws OutOfBoundsException
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
     * Sets a vector component by index: 0|'x' => x, 1|'y' => y.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws OutOfBoundsException
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
     * Unsets a vector component by index.
     *
     * @param mixed $offset
     * @return void
     * @throws OutOfBoundsException
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

    /** Adds another vector to this vector in place. */
    public function add(Vector2 $other): self
    {
        $this->data->x += $other->x;
        $this->data->y += $other->y;

        return $this;
    }

    /** Subtracts another vector from this vector in place. */
    public function subtract(Vector2 $other): self
    {
        $this->data->x -= $other->x;
        $this->data->y -= $other->y;

        return $this;
    }

    /** Multiplies this vector component-wise by another vector in place. */
    public function multiply(Vector2 $other): self
    {
        $this->data->x *= $other->x;
        $this->data->y *= $other->y;

        return $this;
    }

    /** Divides this vector component-wise by another vector in place. */
    public function divide(Vector2 $other): self
    {
        $this->data->x /= $other->x;
        $this->data->y /= $other->y;

        return $this;
    }

    /** Scales this vector by a scalar in place. */
    public function scale(float $scalar): self
    {
        $this->data->x *= $scalar;
        $this->data->y *= $scalar;

        return $this;
    }

    /**
     * Normalizes this vector to have a magnitude of 1 in place.
     * If the vector is zero, it remains unchanged.
     *
     * @return self
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
     * Sets the x and y components of this vector.
     *
     * @param float $x
     * @param float $y
     * @return void
     */
    public function set(float $x, float $y): void
    {
        $this->data->x = $x;
        $this->data->y = $y;
    }

    /**
     * Returns true if this vector is exactly equal to the other vector.
     *
     * @param Vector2 $other
     * @return bool
     */
    public function equals(Vector2 $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }

    /**
     * Returns a formatted string representation of this vector.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }

    /**
     * Converts this Vector2 to a Vector3 with an optional z component.
     *
     * @param float $z
     * @return Vector3
     */
    public function toVector3(float $z = 0.0): Vector3
    {
        return new Vector3($this->x, $this->y, $z);
    }

    // -------------------------------------------------------------------------
    // Static pure helpers (return new vectors; no mutation)
    // -------------------------------------------------------------------------

    /**
     * Returns the sum of two vectors.
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return Vector2
     */
    public static function sum(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x + $b->x, $a->y + $b->y);
    }

    /**
     * Returns the difference of two vectors.
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return Vector2
     */
    public static function difference(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x - $b->x, $a->y - $b->y);
    }

    /**
     * Returns a new vector scaled by a scalar.
     *
     * @param Vector2 $v
     * @param float $scalar
     * @return Vector2
     */
    public static function scaleNew(Vector2 $v, float $scalar): Vector2
    {
        return new self($v->x * $scalar, $v->y * $scalar);
    }

    /**
     * Multiplies two vectors component-wise and returns the result.
     * Equivalent to Unity's Vector2.Scale(a, b).
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return Vector2
     */
    public static function product(Vector2 $a, Vector2 $b): Vector2
    {
        return new self($a->x * $b->x, $a->y * $b->y);
    }

    /**
     * Returns the dot product of two vectors.
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return float
     */
    public static function dot(Vector2 $a, Vector2 $b): float
    {
        return $a->x * $b->x + $a->y * $b->y;
    }

    /**
     * Returns the unsigned angle in degrees between two vectors.
     *
     * @param Vector2 $from
     * @param Vector2 $to
     * @return float
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
     * Returns the signed angle in degrees between two vectors.
     * Positive when rotating counter-clockwise from $from to $to.
     *
     * @param Vector2 $from
     * @param Vector2 $to
     * @return float
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
     * @param Vector2 $a
     * @param Vector2 $b
     * @return float
     */
    public static function distance(Vector2 $a, Vector2 $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;

        return \sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * Linearly interpolates between two vectors by t, clamped to [0, 1].
     *
     * @param Vector2 $a Start vector.
     * @param Vector2 $b End vector.
     * @param float $t Interpolation factor (0–1).
     * @return Vector2
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
     * Linearly interpolates between two vectors by t, without clamping.
     *
     * @param Vector2 $a Start vector.
     * @param Vector2 $b End vector.
     * @param float $t Interpolation factor.
     * @return Vector2
     */
    public static function lerpUnclamped(Vector2 $a, Vector2 $b, float $t): Vector2
    {
        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
        );
    }

    /**
     * Returns a vector made from the largest components of two vectors.
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return Vector2
     */
    public static function max(Vector2 $a, Vector2 $b): Vector2
    {
        return new self(\max($a->x, $b->x), \max($a->y, $b->y));
    }

    /**
     * Returns a vector made from the smallest components of two vectors.
     *
     * @param Vector2 $a
     * @param Vector2 $b
     * @return Vector2
     */
    public static function min(Vector2 $a, Vector2 $b): Vector2
    {
        return new self(\min($a->x, $b->x), \min($a->y, $b->y));
    }

    /**
     * Returns a copy of $vector with its magnitude clamped to $maxLength.
     *
     * @param Vector2 $vector
     * @param float $maxLength
     * @return Vector2
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
     * Moves $current towards $target by at most $maxDelta.
     * Will not overshoot the target.
     *
     * @param Vector2 $current
     * @param Vector2 $target
     * @param float $maxDelta Maximum distance to move.
     * @return Vector2
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
     * Returns the 2D vector perpendicular to the given direction,
     * rotated 90 degrees counter-clockwise: (-y, x).
     *
     * @param Vector2 $inDirection
     * @return Vector2
     */
    public static function perpendicular(Vector2 $inDirection): Vector2
    {
        return new self(-$inDirection->y, $inDirection->x);
    }

    /**
     * Reflects a vector off the surface defined by a normal.
     *
     * @param Vector2 $inDirection The incoming direction vector.
     * @param Vector2 $inNormal The surface normal (should be normalized).
     * @return Vector2
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
     * Gradually changes a vector towards a desired goal over time using a spring-damper.
     *
     * @param Vector2 $current Current position.
     * @param Vector2 $target Target position.
     * @param Vector2 &$currentVelocity Current velocity, modified by this method on each call.
     * @param float $smoothTime Approximate time to reach target.
     * @param float $maxSpeed Maximum speed cap. Defaults to PHP_FLOAT_MAX (no cap).
     * @param float $deltaTime Time elapsed since the last call. Pass Time::$deltaTime.
     * @return Vector2
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
     * Converts a Vector3 to a Vector2 by dropping the z component.
     *
     * @param Vector3 $v
     * @return self
     */
    public static function fromVector3(Vector3 $v): self
    {
        return new self($v->x, $v->y);
    }
}

