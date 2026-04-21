<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use OutOfBoundsException;

/**
 * 3D vector value type with mutating and pure helper operations.
 *
 * Supports array-style component access using offsets 0 (x), 1 (y), and 2 (z).
 */
final class Vector3 implements ArrayAccess
{
    private const EPSILON = 0.000001;
    private const RAD2DEG = 57.29577951308232;

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

    /** Squared length of the vector (avoids sqrt when only relative lengths are needed). */
    public float $sqrMagnitude {
        get {
            return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z;
        }
    }

    /** Euclidean length of the vector. */
    public float $magnitude {
        get {
            return \sqrt($this->sqrMagnitude);
        }
    }

    /** Returns a normalized copy of this vector, or zero when the vector has zero length. */
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
     * @param float $x
     * @param float $y
     * @param float $z
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
        float $z = 0.0,
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /** Vector with all components set to 0. */
    public static function zero(): self
    {
        return new self(0.0, 0.0, 0.0);
    }

    /** Vector with all components set to 1. */
    public static function one(): self
    {
        return new self(1.0, 1.0, 1.0);
    }

    /** Unit vector pointing up (0, 1, 0). */
    public static function up(): self
    {
        return new self(0.0, 1.0, 0.0);
    }

    /** Unit vector pointing down (0, -1, 0). */
    public static function down(): self
    {
        return new self(0.0, -1.0, 0.0);
    }

    /** Unit vector pointing left (-1, 0, 0). */
    public static function left(): self
    {
        return new self(-1.0, 0.0, 0.0);
    }

    /** Unit vector pointing right (1, 0, 0). */
    public static function right(): self
    {
        return new self(1.0, 0.0, 0.0);
    }

    /** Unit vector pointing forward (0, 0, 1). */
    public static function forward(): self
    {
        return new self(0.0, 0.0, 1.0);
    }

    /** Unit vector pointing back (0, 0, -1). */
    public static function back(): self
    {
        return new self(0.0, 0.0, -1.0);
    }

    /** Shorthand for Vector3(-INF, -INF, -INF). */
    public static function negativeInfinity(): self
    {
        return new self(-INF, -INF, -INF);
    }

    /** Shorthand for Vector3(INF, INF, INF). */
    public static function positiveInfinity(): self
    {
        return new self(INF, INF, INF);
    }

    // ---------------------------------------------------------------------
    // In-place operations (mutate this instance; return self for chaining)
    // ---------------------------------------------------------------------

    /** Adds another vector to this vector in place. */
    public function add(Vector3 $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        $this->z += $other->z;

        return $this;
    }

    /** Subtracts another vector from this vector in place. */
    public function subtract(Vector3 $other): self
    {
        $this->x -= $other->x;
        $this->y -= $other->y;
        $this->z -= $other->z;

        return $this;
    }

    /** Multiplies this vector component-wise by another vector in place. */
    public function multiply(Vector3 $other): self
    {
        $this->x *= $other->x;
        $this->y *= $other->y;
        $this->z *= $other->z;

        return $this;
    }

    /** Divides this vector component-wise by another vector in place. */
    public function divide(Vector3 $other): self
    {
        $this->x /= $other->x;
        $this->y /= $other->y;
        $this->z /= $other->z;

        return $this;
    }

    /** Scales this vector by a scalar in place. */
    public function scale(float $scalar): self
    {
        $this->x *= $scalar;
        $this->y *= $scalar;
        $this->z *= $scalar;

        return $this;
    }

    /** Normalizes the vector to have a magnitude of 1 in place. */
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

    // ---------------------------------------------------------------------
    // Instance convenience methods
    // ---------------------------------------------------------------------

    /** Sets the x, y, and z components of this vector. */
    public function set(float $x, float $y, float $z): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /** Creates and returns a clone of this vector. */
    public function clone(): self
    {
        return clone $this;
    }

    /** Checks if this vector is exactly equal to another vector. */
    public function equals(Vector3 $other): bool
    {
        return $this->x === $other->x
            && $this->y === $other->y
            && $this->z === $other->z;
    }

    /** Returns a formatted string representation of this vector. */
    public function __toString(): string
    {
        return "({$this->x}, {$this->y}, {$this->z})";
    }
    public function toVector2(): Vector2
    {
        return new Vector2($this->x, $this->y);
    }

    public function toVector4(float $w = 0.0): Vector4
    {
        return new Vector4($this->x, $this->y, $this->z, $w);
    }


    /**
     * @return array{x: float, y: float, z: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    /**
     * @param array{x?: float, y?: float, z?: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    public static function fromVector2(Vector2 $vector, float $z = 0.0): self
    {
        return new self($vector->x, $vector->y, $z);
    }

    public static function fromVector4(Vector4 $vector): self
    {
        return new self($vector->x, $vector->y, $vector->z);
    }

    // ---------------------------------------------------------------------
    // Static pure helpers (return new vectors; no mutation)
    // ---------------------------------------------------------------------

    /** Returns the sum of two vectors. */
    public static function sum(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x + $b->x,
            $a->y + $b->y,
            $a->z + $b->z,
        );
    }

    /** Returns the difference of two vectors. */
    public static function difference(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x - $b->x,
            $a->y - $b->y,
            $a->z - $b->z,
        );
    }

    /** Multiplies two vectors component-wise and returns the result. */
    public static function product(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x * $b->x,
            $a->y * $b->y,
            $a->z * $b->z,
        );
    }

    /** Returns the quotient of two vectors. */
    public static function quotient(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->x / $b->x,
            $a->y / $b->y,
            $a->z / $b->z,
        );
    }

    /** Returns a new vector scaled by a scalar. */
    public static function scaleNew(Vector3 $vector, float $scalar): self
    {
        return new self(
            $vector->x * $scalar,
            $vector->y * $scalar,
            $vector->z * $scalar,
        );
    }

    /** Returns the dot product of two vectors. */
    public static function dot(Vector3 $a, Vector3 $b): float
    {
        return $a->x * $b->x
            + $a->y * $b->y
            + $a->z * $b->z;
    }

    /** Returns the cross product of two vectors. */
    public static function cross(Vector3 $a, Vector3 $b): self
    {
        return new self(
            $a->y * $b->z - $a->z * $b->y,
            $a->z * $b->x - $a->x * $b->z,
            $a->x * $b->y - $a->y * $b->x,
        );
    }

    /** Returns the unsigned angle in degrees between two vectors. */
    public static function angle(Vector3 $from, Vector3 $to): float
    {
        $denominator = \sqrt($from->sqrMagnitude * $to->sqrMagnitude);
        if ($denominator <= self::EPSILON) {
            return 0.0;
        }

        $dot = self::clamp(self::dot($from, $to) / $denominator, -1.0, 1.0);

        return \acos($dot) * self::RAD2DEG;
    }

    /** Returns the signed angle in degrees between two vectors around an axis. */
    public static function signedAngle(Vector3 $from, Vector3 $to, Vector3 $axis): float
    {
        $unsigned = self::angle($from, $to);
        $cross = self::cross($from, $to);

        return self::dot($axis, $cross) < 0.0 ? -$unsigned : $unsigned;
    }

    /** Returns the distance between two points. */
    public static function distance(Vector3 $a, Vector3 $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;

        return \sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * Linearly interpolates between two vectors by t, clamped to [0, 1],
     * using the native engine implementation.
     */
    public static function lerp(Vector3 $a, Vector3 $b, float $t): self
    {
        /** @var array{x: float, y: float, z: float}|false $result */
        $result = \lenga_internal_vector3_lerp($a->x, $a->y, $a->z, $b->x, $b->y, $b->z, $t);
        if (!\is_array($result)) {
            return new self();
        }

        return self::fromArray($result);
    }

    /** Linearly interpolates between two vectors by t, without clamping. */
    public static function lerpUnclamped(Vector3 $a, Vector3 $b, float $t): self
    {
        return new self(
            $a->x + ($b->x - $a->x) * $t,
            $a->y + ($b->y - $a->y) * $t,
            $a->z + ($b->z - $a->z) * $t,
        );
    }

    /**
     * Spherically interpolates between two vectors, treating them as directions.
     * The direction is interpolated on the sphere and the magnitude is
     * interpolated linearly, matching Unity's Vector3.Slerp behavior.
     */
    public static function slerp(Vector3 $a, Vector3 $b, float $t): self
    {
        /** @var array{x: float, y: float, z: float}|false $result */
        $result = \lenga_internal_vector3_slerp($a->x, $a->y, $a->z, $b->x, $b->y, $b->z, $t);
        if (!\is_array($result)) {
            return new self();
        }

        return self::fromArray($result);
    }

    /** Spherically interpolates between two vectors, without clamping t. */
    public static function slerpUnclamped(Vector3 $a, Vector3 $b, float $t): self
    {
        return self::slerpUnclampedInternal($a, $b, $t);
    }

    /** Returns a vector made from the largest components of two vectors. */
    public static function max(Vector3 $a, Vector3 $b): self
    {
        return new self(
            \max($a->x, $b->x),
            \max($a->y, $b->y),
            \max($a->z, $b->z),
        );
    }

    /** Returns a vector made from the smallest components of two vectors. */
    public static function min(Vector3 $a, Vector3 $b): self
    {
        return new self(
            \min($a->x, $b->x),
            \min($a->y, $b->y),
            \min($a->z, $b->z),
        );
    }

    /** Returns a copy of vector with its magnitude clamped to maxLength. */
    public static function clampMagnitude(Vector3 $vector, float $maxLength): self
    {
        $maxLength = \max(0.0, $maxLength);
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
     * Moves current towards target by at most maxDelta.
     * Will not overshoot the target when maxDelta is positive.
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

        $dist = \sqrt($sqrDist);

        return new self(
            $current->x + $dx / $dist * $maxDelta,
            $current->y + $dy / $dist * $maxDelta,
            $current->z + $dz / $dist * $maxDelta,
        );
    }

    /** Projects a vector onto another vector. */
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

    /** Projects a vector onto a plane defined by a normal. */
    public static function projectOnPlane(Vector3 $vector, Vector3 $planeNormal): self
    {
        $projection = self::project($vector, $planeNormal);

        return new self(
            $vector->x - $projection->x,
            $vector->y - $projection->y,
            $vector->z - $projection->z,
        );
    }

    /** Reflects a vector off the plane defined by a normal vector. */
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
     * Rotates current towards target by an angular step, while also moving the
     * magnitude towards the target magnitude.
     */
    public static function rotateTowards(
        Vector3 $current,
        Vector3 $target,
        float $maxRadiansDelta,
        float $maxMagnitudeDelta,
    ): self {
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

        $angleRadians = \acos(self::clamp(self::dot($currentDirection, $targetDirection), -1.0, 1.0));
        if ($angleRadians <= self::EPSILON) {
            return new self(
                $targetDirection->x * $nextMagnitude,
                $targetDirection->y * $nextMagnitude,
                $targetDirection->z * $nextMagnitude,
            );
        }

        $step = \min(1.0, \abs($maxRadiansDelta) / $angleRadians);
        $rotatedDirection = self::slerpUnclampedInternal($currentDirection, $targetDirection, $step)->normalized;

        return new self(
            $rotatedDirection->x * $nextMagnitude,
            $rotatedDirection->y * $nextMagnitude,
            $rotatedDirection->z * $nextMagnitude,
        );
    }

    /**
     * Gradually changes a vector towards a target over time using a spring-like damper.
     *
     * @param Vector3 $current
     * @param Vector3 $target
     * @param Vector3 &$currentVelocity Modified in place on each call.
     * @param float $smoothTime
     * @param float $maxSpeed
     * @param float|null $deltaTime Defaults to the current frame delta time.
     */
    public static function smoothDamp(
        Vector3 $current,
        Vector3 $target,
        Vector3 &$currentVelocity,
        float $smoothTime,
        float $maxSpeed = PHP_FLOAT_MAX,
        ?float $deltaTime = null,
    ): self {
        $resolvedDeltaTime = $deltaTime ?? Time::deltaTime();
        if ($resolvedDeltaTime <= 0.0) {
            return new self($current->x, $current->y, $current->z);
        }

        /** @var array{
         *     result?: array{x?: float, y?: float, z?: float},
         *     currentVelocity?: array{x?: float, y?: float, z?: float}
         * }|false $result
         */
        $result = \lenga_internal_vector3_smooth_damp(
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

        if (!\is_array($result)) {
            return new self($current->x, $current->y, $current->z);
        }

        if (\is_array($result['currentVelocity'] ?? null)) {
            $currentVelocity = self::fromArray($result['currentVelocity']);
        }

        if (\is_array($result['result'] ?? null)) {
            return self::fromArray($result['result']);
        }

        return new self($current->x, $current->y, $current->z);
    }

    /**
     * Makes normal and tangent orthogonal and normalized using Gram-Schmidt.
     * If tangent collapses, an arbitrary orthogonal tangent is chosen.
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
     * Checks whether an array offset maps to a valid vector component.
     * Supports 0/'x', 1/'y', and 2/'z'.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeOffset($offset);

        return $offset === 0 || $offset === 1 || $offset === 2;
    }

    /**
     * Gets a vector component by index: 0|'x' => x, 1|'y' => y, 2|'z' => z.
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
            2 => $this->z,
            default => throw new OutOfBoundsException("Offset $offset does not exist."),
        };
    }

    /**
     * Sets a vector component by index: 0|'x' => x, 1|'y' => y, 2|'z' => z.
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

        $property = match ($offset) {
            0 => 'x',
            1 => 'y',
            default => 'z',
        };

        unset($this->{$property});
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

    private static function clamp(float $value, float $min, float $max): float
    {
        return \max($min, \min($max, $value));
    }

    private static function moveTowardsScalar(float $current, float $target, float $maxDelta): float
    {
        $delta = $target - $current;
        if ($maxDelta >= 0.0 && \abs($delta) <= $maxDelta) {
            return $target;
        }

        $direction = $delta >= 0.0 ? 1.0 : -1.0;
        $signedStep = $direction * \abs($maxDelta) * ($maxDelta >= 0.0 ? 1.0 : -1.0);

        return $current + $signedStep;
    }

    private static function buildOrthogonalVector(Vector3 $vector): self
    {
        if ($vector->sqrMagnitude <= self::EPSILON) {
            return self::right();
        }

        $orthogonal = self::cross(
            $vector,
            \abs($vector->z) < 0.999 ? self::forward() : self::up(),
        );

        if ($orthogonal->sqrMagnitude <= self::EPSILON) {
            $orthogonal = self::cross($vector, self::right());
        }

        return $orthogonal->normalized;
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
                \abs($normalizedA->x) < 0.99 ? self::right() : self::up(),
            )->normalized;

            if ($orthogonal->sqrMagnitude <= self::EPSILON) {
                $orthogonal = self::cross($normalizedA, self::forward())->normalized;
            }

            $angle = M_PI * $t;
            $direction = new self(
                $normalizedA->x * \cos($angle) + $orthogonal->x * \sin($angle),
                $normalizedA->y * \cos($angle) + $orthogonal->y * \sin($angle),
                $normalizedA->z * \cos($angle) + $orthogonal->z * \sin($angle),
            );
        } else {
            $theta = \acos($dot);
            $sinTheta = \sin($theta);
            if (\abs($sinTheta) <= self::EPSILON) {
                $direction = self::lerpUnclamped($normalizedA, $normalizedB, $t)->normalized;
            } else {
                $fromWeight = \sin((1.0 - $t) * $theta) / $sinTheta;
                $toWeight = \sin($t * $theta) / $sinTheta;
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
}
