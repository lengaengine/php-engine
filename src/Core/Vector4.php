<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;

final class Vector4 implements ArrayAccess
{
    private const EPSILON = 0.000001;

    /** X-axis component. */
    public float $x {
        get {
            return $this->x;
        }
        set {
            $this->x = $value;
        }
    }

    /** Y-axis component. */
    public float $y {
        get {
            return $this->y;
        }
        set {
            $this->y = $value;
        }
    }

    /** Z-axis component. */
    public float $z {
        get {
            return $this->z;
        }
        set {
            $this->z = $value;
        }
    }

    /** W-axis component. */
    public float $w {
        get {
            return $this->w;
        }
        set {
            $this->w = $value;
        }
    }

    /** Squared length of the vector (avoids sqrt when only relative lengths are needed). */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z + $this->w * $this->w;
        }
    }

    /** Euclidean length of the vector. */
    public float $magnitude {
        get {
            return sqrt($this->sqrMagnitude);
        }
    }

    /** Returns a normalized copy of this vector, or zero when the vector has zero length. */
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

    public static function zero(): self { return new self(0.0, 0.0, 0.0, 0.0); }
    public static function one(): self { return new self(1.0, 1.0, 1.0, 1.0); }
    public static function negativeInfinity(): self { return new self(-INF, -INF, -INF, -INF); }
    public static function positiveInfinity(): self { return new self(INF, INF, INF, INF); }

    public function add(self $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        $this->z += $other->z;
        $this->w += $other->w;
        return $this;
    }

    public function subtract(self $other): self
    {
        $this->x -= $other->x;
        $this->y -= $other->y;
        $this->z -= $other->z;
        $this->w -= $other->w;
        return $this;
    }

    public function multiply(self $other): self
    {
        $this->x *= $other->x;
        $this->y *= $other->y;
        $this->z *= $other->z;
        $this->w *= $other->w;
        return $this;
    }

    public function divide(self $other): self
    {
        $this->x /= $other->x;
        $this->y /= $other->y;
        $this->z /= $other->z;
        $this->w /= $other->w;
        return $this;
    }

    public function scale(float $scalar): self
    {
        $this->x *= $scalar;
        $this->y *= $scalar;
        $this->z *= $scalar;
        $this->w *= $scalar;
        return $this;
    }

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

    public function set(float $x, float $y, float $z, float $w): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->w = $w;
    }

    public function clone(): self
    {
        return clone $this;
    }

    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y && $this->z === $other->z && $this->w === $other->w;
    }
    public function __toString(): string
    {
        return sprintf('(%s, %s, %s, %s)', $this->x, $this->y, $this->z, $this->w);
    }


    public function toVector2(): Vector2
    {
        return new Vector2($this->x, $this->y);
    }

    public function toVector3(): Vector3
    {
        return new Vector3($this->x, $this->y, $this->z);
    }

    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z, 'w' => $this->w];
    }

    public static function fromArray(array $data): self
    {
        return new self((float) ($data['x'] ?? 0.0), (float) ($data['y'] ?? 0.0), (float) ($data['z'] ?? 0.0), (float) ($data['w'] ?? 0.0));
    }

    public static function fromVector2(Vector2 $vector, float $z = 0.0, float $w = 0.0): self
    {
        return new self($vector->x, $vector->y, $z, $w);
    }

    public static function fromVector3(Vector3 $vector, float $w = 0.0): self
    {
        return new self($vector->x, $vector->y, $vector->z, $w);
    }
    public static function sum(self $a, self $b): self
    {
        return new self(
            $a->x + $b->x,
            $a->y + $b->y,
            $a->z + $b->z,
            $a->w + $b->w,
        );
    }

    public static function difference(self $a, self $b): self
    {
        return new self(
            $a->x - $b->x,
            $a->y - $b->y,
            $a->z - $b->z,
            $a->w - $b->w,
        );
    }

    public static function product(self $a, self $b): self
    {
        return new self(
            $a->x * $b->x,
            $a->y * $b->y,
            $a->z * $b->z,
            $a->w * $b->w,
        );
    }

    public static function quotient(self $a, self $b): self
    {
        return new self(
            $a->x / $b->x,
            $a->y / $b->y,
            $a->z / $b->z,
            $a->w / $b->w,
        );
    }

    public static function scaleNew(self $vector, float $scalar): self
    {
        return new self(
            $vector->x * $scalar,
            $vector->y * $scalar,
            $vector->z * $scalar,
            $vector->w * $scalar,
        );
    }


    public static function dot(self $a, self $b): float
    {
        return $a->x * $b->x + $a->y * $b->y + $a->z * $b->z + $a->w * $b->w;
    }

    public static function distance(self $a, self $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;
        $dw = $a->w - $b->w;
        return sqrt($dx * $dx + $dy * $dy + $dz * $dz + $dw * $dw);
    }

    public static function lerp(self $a, self $b, float $t): self
    {
        if (\function_exists('lenga_internal_vector4_lerp')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = \lenga_internal_vector4_lerp(
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

    public static function lerpUnclamped(self $a, self $b, float $t): self
    {
        return new self($a->x + ($b->x - $a->x) * $t, $a->y + ($b->y - $a->y) * $t, $a->z + ($b->z - $a->z) * $t, $a->w + ($b->w - $a->w) * $t);
    }

    public static function max(self $a, self $b): self
    {
        return new self(max($a->x, $b->x), max($a->y, $b->y), max($a->z, $b->z), max($a->w, $b->w));
    }

    public static function min(self $a, self $b): self
    {
        return new self(min($a->x, $b->x), min($a->y, $b->y), min($a->z, $b->z), min($a->w, $b->w));
    }

    public static function clampMagnitude(self $vector, float $maxLength): self
    {
        if (\function_exists('lenga_internal_vector4_clamp_magnitude')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = \lenga_internal_vector4_clamp_magnitude(
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

    public static function moveTowards(self $current, self $target, float $maxDelta): self
    {
        if (\function_exists('lenga_internal_vector4_move_towards')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = \lenga_internal_vector4_move_towards(
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

    public static function project(self $vector, self $onNormal): self
    {
        if (\function_exists('lenga_internal_vector4_project')) {
            /** @var array{x: float, y: float, z: float, w: float}|false $result */
            $result = \lenga_internal_vector4_project(
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

    public function offsetExists(mixed $offset): bool
    {
        $offset = match ($offset) { 'x' => 0, 'y' => 1, 'z' => 2, 'w' => 3, default => $offset };
        return $offset === 0 || $offset === 1 || $offset === 2 || $offset === 3;
    }

    public function offsetGet(mixed $offset): mixed
    {
        $offset = match ($offset) { 'x' => 0, 'y' => 1, 'z' => 2, 'w' => 3, default => $offset };
        return match ($offset) { 0 => $this->x, 1 => $this->y, 2 => $this->z, 3 => $this->w, default => throw new OutOfBoundsException("Offset $offset does not exist.") };
    }

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

    public function offsetUnset(mixed $offset): void
    {
        $this->offsetSet($offset, 0.0);
    }
}

