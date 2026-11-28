<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use ArrayAccess;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * A rotation in 3D space.
 *
 * Use Quaternion for {@see Transform::$rotation}, smooth turning between
 * orientations, aiming one direction at another and rotating direction
 * vectors. Quaternions avoid the problems of adding Euler angles together,
 * such as gimbal lock. Create one from angles in degrees with
 * {@see Quaternion::euler()}, around an axis with
 * {@see Quaternion::angleAxis()}, or facing a direction with
 * {@see Quaternion::lookRotation()}.
 *
 * ```php
 * $this->transform->rotation = Quaternion::euler(0.0, 90.0, 0.0);
 * ```
 *
 * The components can also be read and written like an array, using offsets
 * `0` to `3` or `'x'`, `'y'`, `'z'` and `'w'`. Methods that return a
 * Quaternion return a new value unless the method says it changes this one.
 */
final class Quaternion implements ArrayAccess
{
    private const float EPSILON = 0.000001;
    private const float SLERP_EPSILON = 0.9995;
    private const float RAD2DEG = 57.29577951308232;
    private const float DEG2RAD = 0.017453292519943295;

    /**
     * The X component of the quaternion.
     *
     * Set components directly only when you know the quaternion maths; use
     * the factory methods to build rotations.
     */
    public float $x {
        get { return $this->x; }
        set { $this->x = $value; }
    }

    /**
     * The Y component of the quaternion.
     *
     * Set components directly only when you know the quaternion maths; use
     * the factory methods to build rotations.
     */
    public float $y {
        get { return $this->y; }
        set { $this->y = $value; }
    }

    /**
     * The Z component of the quaternion.
     *
     * Set components directly only when you know the quaternion maths; use
     * the factory methods to build rotations.
     */
    public float $z {
        get { return $this->z; }
        set { $this->z = $value; }
    }

    /**
     * The W component of the quaternion.
     *
     * `1.0` with X, Y and Z at `0.0` is no rotation.
     */
    public float $w {
        get { return $this->w; }
        set { $this->w = $value; }
    }

    /**
     * The squared length of the quaternion.
     *
     * A rotation has a length of 1.
     */
    public float $sqrMagnitude {
        get { return ($this->x * $this->x) + ($this->y * $this->y) + ($this->z * $this->z) + ($this->w * $this->w); }
    }

    /**
     * The length of the quaternion.
     *
     * A rotation has a length of 1. Use {@see Quaternion::$normalized} to
     * correct a quaternion that has drifted from it.
     */
    public float $magnitude {
        get { return \sqrt($this->sqrMagnitude); }
    }

    /**
     * A copy of this quaternion scaled to a length of 1.
     *
     * A quaternion too close to zero length becomes {@see Quaternion::identity()}.
     */
    public Quaternion $normalized {
        get { return $this->clone()->normalize(); }
    }

    /**
     * The rotation as angles around the X, Y and Z axes, in degrees.
     *
     * Assigning angles replaces this quaternion's rotation. See
     * {@see Quaternion::fromEulerAngles()}.
     */
    public Vector3 $eulerAngles {
        get { return $this->toEulerAngles(); }

        set(Vector3 $value) {
            $rotation = self::fromEulerAngles($value);
            $this->set($rotation->x, $rotation->y, $rotation->z, $rotation->w);
        }
    }

    /**
     * Creates a quaternion from its four components.
     *
     * With no arguments it is the identity rotation. To build a rotation from
     * angles, use {@see Quaternion::euler()} instead.
     *
     * @param float $x The X component.
     * @param float $y The Y component.
     * @param float $z The Z component.
     * @param float $w The W component.
     */
    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
        float $z = 0.0,
        float $w = 1.0,
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->w = $w;
    }

    /**
     * Gets the rotation that does not rotate anything.
     */
    public static function identity(): self
    {
        return new self(0.0, 0.0, 0.0, 1.0);
    }

    /**
     * Creates a rotation from angles around the X, Y and Z axes, in degrees.
     *
     * Pass a {@see Vector3} of angles, or the three angles separately:
     * `Quaternion::euler(0.0, 90.0, 0.0)` turns 90 degrees around the Y axis.
     * Works the same as {@see Quaternion::fromEulerAngles()}.
     *
     * @param Vector3|float $x A Vector3 of angles, or the angle around the X axis.
     * @param float|null $y The angle around the Y axis, when `$x` is a number.
     * @param float|null $z The angle around the Z axis, when `$x` is a number.
     * @throws InvalidArgumentException When `$x` is a number and `$y` or `$z` is missing.
     */
    public static function euler(Vector3|float $x, ?float $y = null, ?float $z = null): self
    {
        if ($x instanceof Vector3) {
            return self::fromEulerAngles($x);
        }

        if ($y === null || $z === null) {
            throw new InvalidArgumentException('Quaternion::euler() expects either a Vector3 or x, y, z degrees.');
        }

        return self::fromEulerAngles(new Vector3($x, $y, $z));
    }

    /**
     * Creates a rotation from angles around the X, Y and Z axes, in degrees.
     *
     * The rotation turns around the X axis first, then the Y axis, then the
     * Z axis.
     *
     * @param Vector3 $degrees The angles around the X, Y and Z axes, in degrees.
     */
    public static function fromEulerAngles(Vector3 $degrees): self
    {
        $pitch = $degrees->x * self::DEG2RAD;
        $yaw = $degrees->y * self::DEG2RAD;
        $roll = $degrees->z * self::DEG2RAD;

        $x0 = \cos($pitch * 0.5);
        $x1 = \sin($pitch * 0.5);
        $y0 = \cos($yaw * 0.5);
        $y1 = \sin($yaw * 0.5);
        $z0 = \cos($roll * 0.5);
        $z1 = \sin($roll * 0.5);

        return new self(
            ($x1 * $y0 * $z0) - ($x0 * $y1 * $z1),
            ($x0 * $y1 * $z0) + ($x1 * $y0 * $z1),
            ($x0 * $y0 * $z1) - ($x1 * $y1 * $z0),
            ($x0 * $y0 * $z0) + ($x1 * $y1 * $z1),
        );
    }

    /**
     * Creates a rotation around an axis by an angle in degrees.
     *
     * A zero-length axis gives {@see Quaternion::identity()}.
     *
     * @param Vector3 $axis The axis to turn around. It does not need to have a length of 1.
     * @param float $angleDegrees How far to turn, in degrees.
     */
    public static function fromAxisAngle(Vector3 $axis, float $angleDegrees): self
    {
        $normalizedAxis = $axis->normalized;
        if ($normalizedAxis->sqrMagnitude <= self::EPSILON) {
            return self::identity();
        }

        $halfAngleRadians = $angleDegrees * self::DEG2RAD * 0.5;
        $sinHalfAngle = \sin($halfAngleRadians);

        return new self(
            $normalizedAxis->x * $sinHalfAngle,
            $normalizedAxis->y * $sinHalfAngle,
            $normalizedAxis->z * $sinHalfAngle,
            \cos($halfAngleRadians),
        );
    }

    /**
     * Creates a rotation around an axis by an angle in degrees.
     *
     * Does the same as {@see Quaternion::fromAxisAngle()} with the arguments
     * in the other order.
     *
     * @param float $angleDegrees How far to turn, in degrees.
     * @param Vector3 $axis The axis to turn around. It does not need to have a length of 1.
     */
    public static function angleAxis(float $angleDegrees, Vector3 $axis): self
    {
        return self::fromAxisAngle($axis, $angleDegrees);
    }

    /**
     * Gets the dot product of two quaternions.
     *
     * For rotations, a value close to 1 or -1 means they are nearly the same.
     *
     * @param Quaternion $a The first quaternion.
     * @param Quaternion $b The second quaternion.
     */
    public static function dot(Quaternion $a, Quaternion $b): float
    {
        return ($a->x * $b->x)
            + ($a->y * $b->y)
            + ($a->z * $b->z)
            + ($a->w * $b->w);
    }

    /**
     * Gets the smallest angle between two rotations, in degrees.
     *
     * @param Quaternion $a The first rotation.
     * @param Quaternion $b The second rotation.
     * @return float The angle from 0 to 180 degrees.
     */
    public static function angle(Quaternion $a, Quaternion $b): float
    {
        $dot = \min(\abs(self::dot($a->normalized, $b->normalized)), 1.0);
        if ($dot > 1.0 - self::EPSILON) {
            return 0.0;
        }

        return \acos($dot) * 2.0 * self::RAD2DEG;
    }

    /**
     * Creates the rotation that turns one direction into another.
     *
     * Use it to align an object with a surface normal, for example. Returns
     * {@see Quaternion::identity()} when either direction has zero length.
     *
     * @param Vector3 $fromDirection The direction to start from.
     * @param Vector3 $toDirection The direction to end at.
     */
    public static function fromToRotation(Vector3 $fromDirection, Vector3 $toDirection): self
    {
        if ($fromDirection->sqrMagnitude <= self::EPSILON || $toDirection->sqrMagnitude <= self::EPSILON) {
            return self::identity();
        }

        $from = $fromDirection->normalized;
        $to = $toDirection->normalized;
        $dot = Vector3::dot($from, $to);

        if ($dot >= 1.0 - self::EPSILON) {
            return self::identity();
        }

        if ($dot <= -1.0 + self::EPSILON) {
            $axis = Vector3::cross($from, Vector3::right());
            if ($axis->sqrMagnitude <= self::EPSILON) {
                $axis = Vector3::cross($from, Vector3::up());
            }

            return self::fromAxisAngle($axis->normalized, 180.0);
        }

        $axis = Vector3::cross($from, $to);
        $scale = \sqrt((1.0 + $dot) * 2.0);
        $inverseScale = 1.0 / $scale;

        return (new self(
            $axis->x * $inverseScale,
            $axis->y * $inverseScale,
            $axis->z * $inverseScale,
            $scale * 0.5,
        ))->normalize();
    }

    /**
     * Creates a rotation that faces a direction.
     *
     * Use it to aim a camera, turret or character at a target:
     * `Quaternion::lookRotation(Vector3::difference($target, $position))`.
     * The rotation turns the object's forward direction, negative Z
     * ({@see Vector3::back()}), toward `$forward`. Returns
     * {@see Quaternion::identity()} when `$forward` has zero length.
     *
     * @param Vector3 $forward The direction to face.
     * @param Vector3|null $upwards Which way is up for the rotated object; null uses {@see Vector3::up()}.
     */
    public static function lookRotation(Vector3 $forward, ?Vector3 $upwards = null): self
    {
        if ($forward->sqrMagnitude <= self::EPSILON) {
            return self::identity();
        }

        $forwardAxis = $forward->normalized;
        $upAxis = ($upwards ?? Vector3::up())->normalized;
        if ($upAxis->sqrMagnitude <= self::EPSILON) {
            $upAxis = Vector3::up();
        }

        $rightAxis = Vector3::cross($forwardAxis, $upAxis);
        if ($rightAxis->sqrMagnitude <= self::EPSILON) {
            $rightAxis = Vector3::cross(
                $forwardAxis,
                \abs(Vector3::dot($forwardAxis, Vector3::up())) < 0.999 ? Vector3::up() : Vector3::right(),
            );
        }

        $rightAxis = $rightAxis->normalized;
        $correctedUpAxis = Vector3::cross($rightAxis, $forwardAxis)->normalized;
        $zAxis = Vector3::scaleNew($forwardAxis, -1.0);

        return self::fromBasis($rightAxis, $correctedUpAxis, $zAxis);
    }

    /**
     * Gets the rotation that undoes a rotation.
     *
     * Does the same as calling {@see Quaternion::inverse()} on it.
     *
     * @param Quaternion $rotation The rotation to undo.
     */
    public static function inverseOf(Quaternion $rotation): self
    {
        return $rotation->inverse();
    }

    /**
     * Gets a copy of a rotation scaled to a length of 1.
     *
     * @param Quaternion $rotation The rotation to normalize.
     */
    public static function normalizeRotation(Quaternion $rotation): self
    {
        return $rotation->normalized;
    }

    /**
     * Blends between two rotations, with `$t` kept between 0 and 1.
     *
     * Faster than {@see Quaternion::slerp()}, but the turning speed is less
     * even. The result is normalized and takes the shorter way round.
     *
     * @param Quaternion $a The rotation at `$t = 0`.
     * @param Quaternion $b The rotation at `$t = 1`.
     * @param float $t How far to blend from `$a` to `$b`, from 0 to 1.
     */
    public static function lerp(Quaternion $a, Quaternion $b, float $t): self
    {
        return self::lerpUnclamped($a, $b, self::clamp01($t));
    }

    /**
     * Blends between two rotations without limiting `$t`.
     *
     * Values of `$t` outside 0 to 1 continue past `$a` or `$b`. The result is
     * normalized.
     *
     * @param Quaternion $a The rotation at `$t = 0`.
     * @param Quaternion $b The rotation at `$t = 1`.
     * @param float $t How far to blend from `$a` to `$b`.
     */
    public static function lerpUnclamped(Quaternion $a, Quaternion $b, float $t): self
    {
        $target = $b;
        if (self::dot($a, $b) < 0.0) {
            $target = new self(-$b->x, -$b->y, -$b->z, -$b->w);
        }

        return (new self(
            $a->x + (($target->x - $a->x) * $t),
            $a->y + (($target->y - $a->y) * $t),
            $a->z + (($target->z - $a->z) * $t),
            $a->w + (($target->w - $a->w) * $t),
        ))->normalize();
    }

    /**
     * Turns smoothly from one rotation to another at an even speed, with `$t` kept between 0 and 1.
     *
     * Call it every frame with a growing `$t` to animate a turn. It takes the
     * shorter way round.
     *
     * @param Quaternion $a The rotation at `$t = 0`.
     * @param Quaternion $b The rotation at `$t = 1`.
     * @param float $t How far to turn from `$a` to `$b`, from 0 to 1.
     */
    public static function slerp(Quaternion $a, Quaternion $b, float $t): self
    {
        return self::slerpUnclamped($a, $b, self::clamp01($t));
    }

    /**
     * Turns from one rotation to another at an even speed without limiting `$t`.
     *
     * @param Quaternion $a The rotation at `$t = 0`.
     * @param Quaternion $b The rotation at `$t = 1`.
     * @param float $t How far to turn from `$a` to `$b`.
     */
    public static function slerpUnclamped(Quaternion $a, Quaternion $b, float $t): self
    {
        $from = $a->normalized;
        $to = $b->normalized;
        $dot = self::dot($from, $to);

        if ($dot < 0.0) {
            $to = new self(-$to->x, -$to->y, -$to->z, -$to->w);
            $dot = -$dot;
        }

        if ($dot > self::SLERP_EPSILON) {
            return self::lerpUnclamped($from, $to, $t);
        }

        $dot = self::clamp($dot, -1.0, 1.0);
        $theta0 = \acos($dot);
        $theta = $theta0 * $t;
        $sinTheta = \sin($theta);
        $sinTheta0 = \sin($theta0);

        if (\abs($sinTheta0) <= self::EPSILON) {
            return $from;
        }

        $scale0 = \cos($theta) - ($dot * $sinTheta / $sinTheta0);
        $scale1 = $sinTheta / $sinTheta0;

        return (new self(
            ($from->x * $scale0) + ($to->x * $scale1),
            ($from->y * $scale0) + ($to->y * $scale1),
            ($from->z * $scale0) + ($to->z * $scale1),
            ($from->w * $scale0) + ($to->w * $scale1),
        ))->normalize();
    }

    /**
     * Turns from one rotation toward another by at most a number of degrees.
     *
     * Call it every frame with `$speed * Time::deltaTime()` to turn at a
     * steady speed without overshooting the target.
     *
     * @param Quaternion $from The rotation to start from.
     * @param Quaternion $to The rotation to turn toward.
     * @param float $maxDegreesDelta The largest angle to turn, in degrees. Negative values count as 0.
     * @return Quaternion The new rotation, or `$to` once it is within reach.
     */
    public static function rotateTowards(Quaternion $from, Quaternion $to, float $maxDegreesDelta): self
    {
        $angle = self::angle($from, $to);
        if ($angle <= self::EPSILON) {
            return $to->normalized;
        }

        return self::slerpUnclamped($from, $to, \min(1.0, \max(0.0, $maxDegreesDelta) / $angle));
    }

    /**
     * Converts the rotation to angles around the X, Y and Z axes, in degrees.
     *
     * The X and Z angles are between -180 and 180 degrees; the Y angle is
     * between -90 and 90 degrees.
     */
    public function toEulerAngles(): Vector3
    {
        $q = $this->normalized;

        $x0 = 2.0 * (($q->w * $q->x) + ($q->y * $q->z));
        $x1 = 1.0 - (2.0 * (($q->x * $q->x) + ($q->y * $q->y)));
        $y0 = 2.0 * (($q->w * $q->y) - ($q->z * $q->x));
        $y0 = \max(-1.0, \min(1.0, $y0));
        $z0 = 2.0 * (($q->w * $q->z) + ($q->x * $q->y));
        $z1 = 1.0 - (2.0 * (($q->y * $q->y) + ($q->z * $q->z)));

        return new Vector3(
            \atan2($x0, $x1) * self::RAD2DEG,
            \asin($y0) * self::RAD2DEG,
            \atan2($z0, $z1) * self::RAD2DEG,
        );
    }

    /**
     * Combines this rotation with another.
     *
     * `$a->multiply($b)` gives the rotation of turning by `$b` first, then by
     * `$a`. Use it to add a turn to an existing rotation, for example
     * `$rotation->multiply(Quaternion::euler(0.0, 15.0, 0.0))` turns an object
     * a further 15 degrees around its own Y axis.
     *
     * @param Quaternion $other The rotation applied first.
     * @return Quaternion The combined rotation. This quaternion is unchanged.
     */
    public function multiply(Quaternion $other): self
    {
        return new self(
            ($this->w * $other->x) + ($this->x * $other->w) + ($this->y * $other->z) - ($this->z * $other->y),
            ($this->w * $other->y) - ($this->x * $other->z) + ($this->y * $other->w) + ($this->z * $other->x),
            ($this->w * $other->z) + ($this->x * $other->y) - ($this->y * $other->x) + ($this->z * $other->w),
            ($this->w * $other->w) - ($this->x * $other->x) - ($this->y * $other->y) - ($this->z * $other->z),
        );
    }

    /**
     * Sets all four components of this quaternion.
     *
     * @param float $x The X component.
     * @param float $y The Y component.
     * @param float $z The Z component.
     * @param float $w The W component.
     */
    public function set(float $x, float $y, float $z, float $w): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->w = $w;
    }

    /**
     * Changes this quaternion to the rotation that turns one direction into another.
     *
     * See {@see Quaternion::fromToRotation()}.
     *
     * @param Vector3 $fromDirection The direction to start from.
     * @param Vector3 $toDirection The direction to end at.
     */
    public function setFromToRotation(Vector3 $fromDirection, Vector3 $toDirection): void
    {
        $rotation = self::fromToRotation($fromDirection, $toDirection);
        $this->set($rotation->x, $rotation->y, $rotation->z, $rotation->w);
    }

    /**
     * Changes this quaternion to a rotation that faces a direction.
     *
     * See {@see Quaternion::lookRotation()}.
     *
     * @param Vector3 $forward The direction to face.
     * @param Vector3|null $upwards Which way is up for the rotated object; null uses {@see Vector3::up()}.
     */
    public function setLookRotation(Vector3 $forward, ?Vector3 $upwards = null): void
    {
        $rotation = self::lookRotation($forward, $upwards);
        $this->set($rotation->x, $rotation->y, $rotation->z, $rotation->w);
    }

    /**
     * Splits the rotation into an angle and the axis it turns around.
     *
     * A rotation of 0 degrees reports {@see Vector3::right()} as its axis.
     *
     * @return array{angle: float, axis: Vector3} The angle in degrees and the axis, with a length of 1.
     */
    public function toAngleAxis(): array
    {
        $q = $this->normalized;
        $angle = 2.0 * \acos(self::clamp($q->w, -1.0, 1.0)) * self::RAD2DEG;
        $denominator = \sqrt(\max(0.0, 1.0 - ($q->w * $q->w)));
        if ($denominator <= self::EPSILON) {
            return ['angle' => 0.0, 'axis' => Vector3::right()];
        }

        return [
            'angle' => $angle,
            'axis' => new Vector3($q->x / $denominator, $q->y / $denominator, $q->z / $denominator),
        ];
    }

    /**
     * Gets the quaternion with its X, Y and Z components negated.
     *
     * For a rotation with a length of 1, this is the same as
     * {@see Quaternion::inverse()}.
     */
    public function conjugate(): self
    {
        return new self(-$this->x, -$this->y, -$this->z, $this->w);
    }

    /**
     * Gets the rotation that undoes this one.
     *
     * A quaternion too close to zero length gives {@see Quaternion::identity()}.
     */
    public function inverse(): self
    {
        $lengthSquared = $this->sqrMagnitude;
        if ($lengthSquared <= self::EPSILON) {
            return self::identity();
        }

        $conjugate = $this->conjugate();
        return new self(
            $conjugate->x / $lengthSquared,
            $conjugate->y / $lengthSquared,
            $conjugate->z / $lengthSquared,
            $conjugate->w / $lengthSquared,
        );
    }

    /**
     * Rotates a direction or offset by this rotation.
     *
     * Use it to turn a local offset, such as a muzzle position, into the
     * direction an object faces.
     *
     * @param Vector3 $vector The direction or offset to rotate.
     * @return Vector3 The rotated vector. Its length is unchanged.
     */
    public function rotateVector(Vector3 $vector): Vector3
    {
        $q = $this->normalized;
        $quatVector = new Vector3($q->x, $q->y, $q->z);
        $uv = Vector3::cross($quatVector, $vector);
        $uuv = Vector3::cross($quatVector, $uv);

        return Vector3::sum(
            $vector,
            Vector3::sum(
                Vector3::scaleNew($uv, 2.0 * $q->w),
                Vector3::scaleNew($uuv, 2.0),
            ),
        );
    }

    /**
     * Scales this quaternion to a length of 1.
     *
     * A quaternion too close to zero length becomes the identity rotation.
     *
     * @return Quaternion This quaternion, changed in place.
     */
    public function normalize(): self
    {
        $length = $this->magnitude;
        if ($length <= self::EPSILON) {
            $this->x = 0.0;
            $this->y = 0.0;
            $this->z = 0.0;
            $this->w = 1.0;
            return $this;
        }

        $this->x /= $length;
        $this->y /= $length;
        $this->z /= $length;
        $this->w /= $length;
        return $this;
    }

    /**
     * Creates an independent copy of this quaternion.
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Checks whether two quaternions have exactly the same components.
     *
     * The comparison is exact, so rotations that differ by tiny rounding
     * errors are not equal. Use {@see Quaternion::angle()} to check whether
     * two rotations are close.
     *
     * @param Quaternion $other The quaternion to compare with.
     */
    public function equals(Quaternion $other): bool
    {
        return $this->x === $other->x
            && $this->y === $other->y
            && $this->z === $other->z
            && $this->w === $other->w;
    }

    /**
     * Formats the quaternion as `(x, y, z, w)`.
     */
    public function __toString(): string
    {
        return "($this->x, $this->y, $this->z, $this->w)";
    }

    public function __serialize(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z, 'w' => $this->w];
    }

    public function __unserialize(array $data): void
    {
        $this->x = (float) ($data['x'] ?? 0.0);
        $this->y = (float) ($data['y'] ?? 0.0);
        $this->z = (float) ($data['z'] ?? 0.0);
        $this->w = (float) ($data['w'] ?? 1.0);
    }

    /**
     * Reads a component with array syntax, such as `$rotation[3]` or `$rotation['w']`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @throws OutOfBoundsException When the offset is not a component.
     */
    public function offsetGet(mixed $offset): float
    {
        $offset = $this->normalizeOffset($offset);

        return match ($offset) {
            0 => $this->x,
            1 => $this->y,
            2 => $this->z,
            3 => $this->w,
            default => throw new OutOfBoundsException("Offset $offset does not exist."),
        };
    }

    /**
     * Writes a component with array syntax, such as `$rotation[3] = 1.0`.
     *
     * @param mixed $offset `0` to `3`, or `'x'`, `'y'`, `'z'` or `'w'`.
     * @param mixed $value The new value, converted to a float.
     * @throws OutOfBoundsException When the offset is not a component.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset = $this->normalizeOffset($offset);
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Offset $offset does not exist.");
        }

        if ($offset === 0) {
            $this->x = (float) $value;
        }

        if ($offset === 1) {
            $this->y = (float) $value;
        }

        if ($offset === 2) {
            $this->z = (float) $value;
        }

        if ($offset === 3) {
            $this->w = (float) $value;
        }
    }

    /**
     * Checks whether an offset names a component, for `isset($rotation[0])`.
     *
     * @param mixed $offset The offset to check.
     */
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeOffset($offset);

        return $offset === 0 || $offset === 1 || $offset === 2 || $offset === 3;
    }

    /**
     * Handles `unset($rotation[...])`, which a Quaternion does not support.
     *
     * Components cannot be removed: unsetting a component throws an
     * `Error`, and any other offset throws an `OutOfBoundsException`.
     *
     * @param mixed $offset The offset to unset.
     * @throws OutOfBoundsException When the offset is not a component.
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
            2 => 'z',
            default => 'w',
        };

        unset($this->{$property});
    }

    private static function fromBasis(Vector3 $xAxis, Vector3 $yAxis, Vector3 $zAxis): self
    {
        $m00 = $xAxis->x;
        $m01 = $yAxis->x;
        $m02 = $zAxis->x;
        $m10 = $xAxis->y;
        $m11 = $yAxis->y;
        $m12 = $zAxis->y;
        $m20 = $xAxis->z;
        $m21 = $yAxis->z;
        $m22 = $zAxis->z;

        $trace = $m00 + $m11 + $m22;
        if ($trace > 0.0) {
            $scale = \sqrt($trace + 1.0) * 2.0;
            return (new self(
                ($m21 - $m12) / $scale,
                ($m02 - $m20) / $scale,
                ($m10 - $m01) / $scale,
                0.25 * $scale,
            ))->normalize();
        }

        if ($m00 > $m11 && $m00 > $m22) {
            $scale = \sqrt(1.0 + $m00 - $m11 - $m22) * 2.0;
            return (new self(
                0.25 * $scale,
                ($m01 + $m10) / $scale,
                ($m02 + $m20) / $scale,
                ($m21 - $m12) / $scale,
            ))->normalize();
        }

        if ($m11 > $m22) {
            $scale = \sqrt(1.0 + $m11 - $m00 - $m22) * 2.0;
            return (new self(
                ($m01 + $m10) / $scale,
                0.25 * $scale,
                ($m12 + $m21) / $scale,
                ($m02 - $m20) / $scale,
            ))->normalize();
        }

        $scale = \sqrt(1.0 + $m22 - $m00 - $m11) * 2.0;
        return (new self(
            ($m02 + $m20) / $scale,
            ($m12 + $m21) / $scale,
            0.25 * $scale,
            ($m10 - $m01) / $scale,
        ))->normalize();
    }

    private static function clamp(float $value, float $min, float $max): float
    {
        return \max($min, \min($max, $value));
    }

    private static function clamp01(float $value): float
    {
        return self::clamp($value, 0.0, 1.0);
    }

    private function normalizeOffset(mixed $offset): mixed
    {
        return match ($offset) {
            'x' => 0,
            'y' => 1,
            'z' => 2,
            'w' => 3,
            default => $offset,
        };
    }
}
