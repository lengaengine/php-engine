<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Quaternion
{
    private const EPSILON = 0.000001;
    private const RAD2DEG = 57.29577951308232;
    private const DEG2RAD = 0.017453292519943295;

    public float $x {
        get { return $this->x; }
        set { $this->x = $value; }
    }

    public float $y {
        get { return $this->y; }
        set { $this->y = $value; }
    }

    public float $z {
        get { return $this->z; }
        set { $this->z = $value; }
    }

    public float $w {
        get { return $this->w; }
        set { $this->w = $value; }
    }

    public float $sqrMagnitude {
        get { return ($this->x * $this->x) + ($this->y * $this->y) + ($this->z * $this->z) + ($this->w * $this->w); }
    }

    public float $magnitude {
        get { return \sqrt($this->sqrMagnitude); }
    }

    public Quaternion $normalized {
        get { return $this->clone()->normalize(); }
    }

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

    public static function identity(): self
    {
        return new self(0.0, 0.0, 0.0, 1.0);
    }

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

    public function multiply(Quaternion $other): self
    {
        return new self(
            ($this->w * $other->x) + ($this->x * $other->w) + ($this->y * $other->z) - ($this->z * $other->y),
            ($this->w * $other->y) - ($this->x * $other->z) + ($this->y * $other->w) + ($this->z * $other->x),
            ($this->w * $other->z) + ($this->x * $other->y) - ($this->y * $other->x) + ($this->z * $other->w),
            ($this->w * $other->w) - ($this->x * $other->x) - ($this->y * $other->y) - ($this->z * $other->z),
        );
    }

    public function conjugate(): self
    {
        return new self(-$this->x, -$this->y, -$this->z, $this->w);
    }

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

    public function clone(): self
    {
        return clone $this;
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
}
