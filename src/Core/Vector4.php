<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;

/**
 * Four-component vector for values with X, Y, Z and W parts.
 *
 * Use it for any value made of four numbers, such as a rectangle stored as X, Y, width
 * and height. A public Vector4 field on a {@see Behaviour} is saved with the scene like
 * other vector fields. It works like {@see Vector3} with a fourth component:
 *
 * - Instance methods such as {@see Vector4::add()} and {@see Vector4::scale()} change the
 *   vector itself and return it, so calls can be chained.
 * - Static methods such as {@see Vector4::sum()} and {@see Vector4::lerp()} leave their
 *   inputs alone and return a new value.
 *
 * Components can also be read and written like an array, with `0` to `3` or `'x'`, `'y'`,
 * `'z'` and `'w'`.
 */
final class Vector4 implements ArrayAccess
{
    private const float EPSILON = 0.000001;

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
     * W component.
     */
    public float $w {
        get {
            return $this->w;
        }
        set {
            $this->w = $value;
        }
    }

    /**
     * Squared length of the vector. Read-only.
     *
     * Cheaper than {@see Vector4::$magnitude} because it skips the square root.
     */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z + $this->w * $this->w;
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
     * Returns `(0, 0, 0, 0)` when the vector is shorter than about `0.001`. The vector itself
     * does not change.
     */
    public Vector4 $normalized {
        get {
            if ($this->sqrMagnitude <= self::EPSILON) {
                return new self();
            }

            $magnitude = $this->magnitude;

            return new self(
                $this->x / $magnitude,
                $this->y / $magnitude,
                $this->z / $magnitude,
                $this->w / $magnitude,
            );
        }
    }

    /**
     * Creates a vector from its components.
     *
     * @param float $x X component. Defaults to `0`.
     * @param float $y Y component. Defaults to `0`.
     * @param float $z Z component. Defaults to `0`.
     * @param float $w W component. Defaults to `0`.
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
        float $z = 0.0,
        float $w = 0.0,
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->w = $w;
    }

    /**
     * Returns a new vector with all components set to 0.
     */
    public static function zero(): self { return new self(0.0, 0.0, 0.0, 0.0); }
    /**
     * Returns a new vector with all components set to 1.
     */
    public static function one(): self { return new self(1.0, 1.0, 1.0, 1.0); }
    /**
     * Returns a new vector with all components set to negative infinity.
     */
    public static function negativeInfinity(): self { return new self(-INF, -INF, -INF, -INF); }
    /**
     * Returns a new vector with all components set to positive infinity.
     */
    public static function positiveInfinity(): self { return new self(INF, INF, INF, INF); }

    /**
     * Adds another vector to this vector in place.
     *
     * @param Vector4 $other Vector to add.
     * @return self This vector, for chaining.
     */
    public function add(self $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        $this->z += $other->z;
        $this->w += $other->w;
        return $this;
    }

    /**
     * Subtracts another vector from this vector in place.
     *
     * @param Vector4 $other Vector to subtract.
     * @return self This vector, for chaining.
     */
    public function subtract(self $other): self
    {
        $this->x -= $other->x;
        $this->y -= $other->y;
        $this->z -= $other->z;
        $this->w -= $other->w;
        return $this;
    }

    /**
     * Multiplies this vector component by component with another vector, in place.
     *
     * @param Vector4 $other Vector whose components multiply this vector's components.
     * @return self This vector, for chaining.
     */
    public function multiply(self $other): self
    {
        $this->x *= $other->x;
        $this->y *= $other->y;
        $this->z *= $other->z;
        $this->w *= $other->w;
        return $this;
    }

    /**
     * Divides this vector component by component by another vector, in place.
     *
     * @param Vector4 $other Vector whose components divide this vector's components.
     * @return self This vector, for chaining.
     * @throws \DivisionByZeroError When a component of `$other` is `0`.
     */
    public function divide(self $other): self
    {
        $this->x /= $other->x;
        $this->y /= $other->y;
        $this->z /= $other->z;
        $this->w /= $other->w;
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
        $this->w *= $scalar;
        return $this;
    }

    /**
     * Changes this vector to a length of 1, keeping its direction.
     *
     * A vector shorter than about `0.000001` stays unchanged. Use {@see Vector4::$normalized}
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
        $this->w /= $magnitude;
        return $this;
    }

    /**
     * Sets all four components of this vector.
     *
     * @param float $x New X component.
     * @param float $y New Y component.
     * @param float $z New Z component.
     * @param float $w New W component.
     */
    public function set(float $x, float $y, float $z, float $w): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->w = $w;
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
     * equal. Compare {@see Vector4::distance()} against a small tolerance for those.
     *
     * @param Vector4 $other Vector to compare with.
     */
    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y && $this->z === $other->z && $this->w === $other->w;
    }
    /**
     * Returns the vector as text in the form `(x, y, z, w)`, for logging.
     */
    public function __toString(): string
    {
        return sprintf('(%s, %s, %s, %s)', $this->x, $this->y, $this->z, $this->w);
    }


    /**
     * Returns a new Vector2 with this vector's X and Y, dropping Z and W.
     */
    public function toVector2(): Vector2
    {
        return new Vector2($this->x, $this->y);
    }

    /**
     * Returns a new Vector3 with this vector's X, Y and Z, dropping W.
     */
    public function toVector3(): Vector3
    {
        return new Vector3($this->x, $this->y, $this->z);
    }

    /**
     * Returns the components as an array with `x`, `y`, `z` and `w` keys.
     *
     * @return array{x: float, y: float, z: float, w: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z, 'w' => $this->w];
    }

    /**
     * Creates a vector from an array with `x`, `y`, `z` and `w` keys.
     *
     * Missing keys become `0`.
     *
     * @param array{x?: float|int, y?: float|int, z?: float|int, w?: float|int} $data Component values.
     */
    public static function fromArray(array $data): self
    {
        return new self((float) ($data['x'] ?? 0.0), (float) ($data['y'] ?? 0.0), (float) ($data['z'] ?? 0.0), (float) ($data['w'] ?? 0.0));
    }

    /**
     * Returns a new Vector4 with the X and Y of a Vector2.
     *
     * @param Vector2 $vector Vector to convert.
     * @param float $z Z component of the result. Defaults to `0`.
     * @param float $w W component of the result. Defaults to `0`.
     */
    public static function fromVector2(Vector2 $vector, float $z = 0.0, float $w = 0.0): self
    {
        return new self($vector->x, $vector->y, $z, $w);
    }

    /**
     * Returns a new Vector4 with the X, Y and Z of a Vector3.
     *
     * @param Vector3 $vector Vector to convert.
     * @param float $w W component of the result. Defaults to `0`.
     */
    public static function fromVector3(Vector3 $vector, float $w = 0.0): self
    {
        return new self($vector->x, $vector->y, $vector->z, $w);
    }
    /**
     * Returns the sum of two vectors as a new vector.
     *
     * @param Vector4 $a First vector.
     * @param Vector4 $b Second vector.
     */
    public static function sum(self $a, self $b): self
    {
        return new self(
            $a->x + $b->x,
            $a->y + $b->y,
            $a->z + $b->z,
            $a->w + $b->w,
        );
    }

    /**
     * Returns `$a` minus `$b` as a new vector.
     *
     * @param Vector4 $a Vector to subtract from.
     * @param Vector4 $b Vector to subtract.
     */
    public static function difference(self $a, self $b): self
    {
        return new self(
            $a->x - $b->x,
            $a->y - $b->y,
            $a->z - $b->z,
            $a->w - $b->w,
        );
    }

    /**
     * Returns a new vector made by multiplying two vectors component by component.
     *
     * @param Vector4 $a First vector.
     * @param Vector4 $b Second vector.
     */
    public static function product(self $a, self $b): self
    {
        return new self(
            $a->x * $b->x,
            $a->y * $b->y,
            $a->z * $b->z,
            $a->w * $b->w,
        );
    }

    /**
     * Returns a new vector made by dividing `$a` by `$b` component by component.
     *
     * @param Vector4 $a Vector to divide.
     * @param Vector4 $b Vector to divide by.
     * @throws \DivisionByZeroError When a component of `$b` is `0`.
     */
    public static function quotient(self $a, self $b): self
    {
        return new self(
            $a->x / $b->x,
            $a->y / $b->y,
            $a->z / $b->z,
            $a->w / $b->w,
        );
    }

    /**
     * Returns a new vector equal to a vector multiplied by a number.
     *
     * @param Vector4 $vector Vector to scale.
     * @param float $scalar Number to multiply by.
     */
    public static function scaleNew(self $vector, float $scalar): self
    {
        return new self(
            $vector->x * $scalar,
            $vector->y * $scalar,
            $vector->z * $scalar,
            $vector->w * $scalar,
        );
    }


    /**
     * Returns the dot product of two vectors.
     *
     * @param Vector4 $a First vector.
     * @param Vector4 $b Second vector.
     */
    public static function dot(self $a, self $b): float
    {
        return $a->x * $b->x + $a->y * $b->y + $a->z * $b->z + $a->w * $b->w;
    }

    /**
     * Returns the distance between two four-component points.
     *
     * @param Vector4 $a First point.
     * @param Vector4 $b Second point.
     */
    public static function distance(self $a, self $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;
        $dw = $a->w - $b->w;
        return sqrt($dx * $dx + $dy * $dy + $dz * $dz + $dw * $dw);
    }

    /**
     * Returns the value a fraction of the way from `$a` to `$b`.
     *
     * `$t` is clamped to the range 0 to 1, so the result never goes past either end.
     *
     * @param Vector4 $a Value at `$t = 0`.
     * @param Vector4 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`, from 0 to 1.
     * @return Vector4 The interpolated vector.
     */
    public static function lerp(self $a, self $b, float $t): self
    {
        if (NativeEngine::hasFunction('vector4_lerp')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = NativeEngine::call('vector4_lerp',
                $a->x,
                $a->y,
                $a->z,
                $a->w,
                $b->x,
                $b->y,
                $b->z,
                $b->w,
                $t,
            );

            if (\is_array($result)) {
                return self::fromArray($result);
            }
        }

        if ($t <= 0.0) {
            return new self($a->x, $a->y, $a->z, $a->w);
        }

        if ($t >= 1.0) {
            return new self($b->x, $b->y, $b->z, $b->w);
        }

        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
            $a->z + ($b->z - $a->z) * $t,
            $a->w + ($b->w - $a->w) * $t,
        );
    }

    /**
     * Returns the value a fraction of the way from `$a` to `$b`, without clamping `$t`.
     *
     * Values of `$t` below 0 or above 1 extend past `$a` or `$b`.
     *
     * @param Vector4 $a Value at `$t = 0`.
     * @param Vector4 $b Value at `$t = 1`.
     * @param float $t Fraction of the way from `$a` to `$b`.
     * @return Vector4 The interpolated vector.
     */
    public static function lerpUnclamped(self $a, self $b, float $t): self
    {
        return new self($a->x + ($b->x - $a->x) * $t, $a->y + ($b->y - $a->y) * $t, $a->z + ($b->z - $a->z) * $t, $a->w + ($b->w - $a->w) * $t);
    }

    /**
     * Returns a new vector made from the largest of each component of two vectors.
     *
     * @param Vector4 $a First vector.
     * @param Vector4 $b Second vector.
     */
    public static function max(self $a, self $b): self
    {
        return new self(max($a->x, $b->x), max($a->y, $b->y), max($a->z, $b->z), max($a->w, $b->w));
    }

    /**
     * Returns a new vector made from the smallest of each component of two vectors.
     *
     * @param Vector4 $a First vector.
     * @param Vector4 $b Second vector.
     */
    public static function min(self $a, self $b): self
    {
        return new self(min($a->x, $b->x), min($a->y, $b->y), min($a->z, $b->z), min($a->w, $b->w));
    }

    /**
     * Returns a copy of a vector shortened to a maximum length.
     *
     * Vectors already within the limit are returned unchanged.
     *
     * @param Vector4 $vector Vector to limit.
     * @param float $maxLength Largest length allowed. Negative values are treated as `0`.
     * @return Vector4 A new vector with the same direction and a length of at most `$maxLength`.
     */
    public static function clampMagnitude(self $vector, float $maxLength): self
    {
        if (NativeEngine::hasFunction('vector4_clamp_magnitude')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = NativeEngine::call('vector4_clamp_magnitude',
                $vector->x,
                $vector->y,
                $vector->z,
                $vector->w,
                $maxLength,
            );

            if (\is_array($result)) {
                return self::fromArray($result);
            }
        }

        if ($maxLength < 0.0) {
            $maxLength = 0.0;
        }

        if ($vector->sqrMagnitude <= $maxLength * $maxLength) {
            return new self($vector->x, $vector->y, $vector->z, $vector->w);
        }

        return self::scaleNew($vector->normalized, $maxLength);
    }

    /**
     * Returns a value moved from `$current` toward `$target` by at most `$maxDelta`.
     *
     * The result never passes the target.
     *
     * @param Vector4 $current Starting value.
     * @param Vector4 $target Value to move toward.
     * @param float $maxDelta Largest distance to move. A negative value moves away from the target.
     * @return Vector4 The new value.
     */
    public static function moveTowards(self $current, self $target, float $maxDelta): self
    {
        if (NativeEngine::hasFunction('vector4_move_towards')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = NativeEngine::call('vector4_move_towards',
                $current->x,
                $current->y,
                $current->z,
                $current->w,
                $target->x,
                $target->y,
                $target->z,
                $target->w,
                $maxDelta,
            );

            if (\is_array($result)) {
                return self::fromArray($result);
            }
        }

        $delta = self::difference($target, $current);
        $sqrDistance = $delta->sqrMagnitude;

        if ($sqrDistance <= self::EPSILON) {
            return new self($target->x, $target->y, $target->z, $target->w);
        }

        if ($maxDelta >= 0.0 && $sqrDistance <= $maxDelta * $maxDelta) {
            return new self($target->x, $target->y, $target->z, $target->w);
        }

        $distance = sqrt($sqrDistance);

        return new self(
            $current->x + ($delta->x / $distance) * $maxDelta,
            $current->y + ($delta->y / $distance) * $maxDelta,
            $current->z + ($delta->z / $distance) * $maxDelta,
            $current->w + ($delta->w / $distance) * $maxDelta,
        );
    }

    /**
     * Projects a vector onto another vector.
     *
     * The result is the part of `$vector` that points along `$onNormal`.
     *
     * @param Vector4 $vector Vector to project.
     * @param Vector4 $onNormal Direction to project onto. It does not need to be normalized.
     * @return Vector4 The projected vector, or `(0, 0, 0, 0)` when `$onNormal` has zero length.
     */
    public static function project(self $vector, self $onNormal): self
    {
        if (NativeEngine::hasFunction('vector4_project')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = NativeEngine::call('vector4_project',
                $vector->x,
                $vector->y,
                $vector->z,
                $vector->w,
                $onNormal->x,
                $onNormal->y,
                $onNormal->z,
                $onNormal->w,
            );

            if (\is_array($result)) {
                return self::fromArray($result);
            }
        }

        $sqrMagnitude = $onNormal->sqrMagnitude;
        if ($sqrMagnitude <= self::EPSILON) {
            return self::zero();
        }

        return self::scaleNew($onNormal, self::dot($vector, $onNormal) / $sqrMagnitude);
    }

    /**
     * Returns whether an array offset names a component, for `isset($vector[...])`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @return bool `true` for a valid component offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = match ($offset) { 'x' => 0, 'y' => 1, 'z' => 2, 'w' => 3, default => $offset };
        return $offset === 0 || $offset === 1 || $offset === 2 || $offset === 3;
    }

    /**
     * Reads a component with array syntax, as in `$vector[3]` or `$vector['w']`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @return mixed The component value.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetGet(mixed $offset): mixed
    {
        $offset = match ($offset) { 'x' => 0, 'y' => 1, 'z' => 2, 'w' => 3, default => $offset };
        return match ($offset) { 0 => $this->x, 1 => $this->y, 2 => $this->z, 3 => $this->w, default => throw new OutOfBoundsException("Offset $offset does not exist.") };
    }

    /**
     * Writes a component with array syntax, as in `$vector['w'] = 1.0`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @param mixed $value New component value. It is converted to a float.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset = match ($offset) { 'x' => 0, 'y' => 1, 'z' => 2, 'w' => 3, default => $offset };
        match ($offset) {
            0 => $this->x = (float) $value,
            1 => $this->y = (float) $value,
            2 => $this->z = (float) $value,
            3 => $this->w = (float) $value,
            default => throw new OutOfBoundsException("Offset $offset does not exist."),
        };
    }

    /**
     * Sets a component to `0` with `unset($vector[...])`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @throws OutOfBoundsException When the offset does not name a component.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->offsetSet($offset, 0.0);
    }
}
