<?php

namespace Lenga\Engine\Core;

use Stringable;

/**
 *
 */
final class Color implements Stringable
{
    public const float MIN_VALUE = 0;
    public const float MAX_VALUE = 1;

    public float $r {
        get {
            $red = $this->colors['red'] ?? 0;
            return MathUtil::clamp($red, self::MIN_VALUE, self::MAX_VALUE);
        }
    }
    public float $g {
        get {
            $green = $this->colors['green'] ?? 0;
            return MathUtil::clamp($green, self::MIN_VALUE, self::MAX_VALUE);
        }
    }
    public float $b {
        get {
            $blue = $this->colors['blue'] ?? 0;
            return MathUtil::clamp($blue, self::MIN_VALUE, self::MAX_VALUE);
        }
    }
    public float $a {
        get {
            $alpha = $this->colors['alpha'] ?? 0;
            return MathUtil::clamp($alpha, self::MIN_VALUE, self::MAX_VALUE);
        }
    }


    private array $colors;

    /**
     *
     * @param float $r Red value
     * @param float $g Green value
     * @param float $b Blue value
     * @param float $a Alpha channel value
     */
    public function __construct(
        float $r = self::MIN_VALUE,
        float $g = self::MIN_VALUE,
        float $b = self::MIN_VALUE,
        float $a = self::MAX_VALUE,
    )
    {
        $this->colors = ['red' => $r, 'green' => $g, 'blue' => $b, 'alpha' => $a];
    }

    /**
     * (0, 0, 0, 1)
     * @return self
     */
    public static function black(): self
    {
        return new self(self::MIN_VALUE, self::MIN_VALUE, self::MIN_VALUE, self::MAX_VALUE);
    }

    /**
     * (0, 0, 1, 1)
     * @return self
     */
    public static function blue(): self
    {
        return new self(self::MIN_VALUE, self::MIN_VALUE, self::MAX_VALUE, self::MAX_VALUE);
    }

    /**
     * (0, 0, 0, 0)
     * @return self
     */
    public static function clear(): self
    {
        return new self(self::MIN_VALUE, self::MIN_VALUE, self::MIN_VALUE, self::MIN_VALUE);
    }

    /**
     * (0, 1, 1, 1)
     * @return self
     */
    public static function cyan(): self
    {
        return new self(self::MIN_VALUE, self::MAX_VALUE, self::MAX_VALUE, self::MAX_VALUE);
    }

    /**
     * (0.5, 0.5, 0.5, 1)
     * @return self
     */
    public static function gray(): self
    {
        return new self(0.5, 0.5, 0.5, self::MAX_VALUE);
    }

    /**
     * (1, 0, 1, 1)
     * @return self
     */
    public static function magenta(): self
    {
        return new self(self::MAX_VALUE, self::MIN_VALUE, self::MAX_VALUE, self::MAX_VALUE);
    }

    /**
     * (1, 0, 0, 1)
     * @return self
     */
    public static function red(): self
    {
        return new self(self::MAX_VALUE, self::MIN_VALUE, self::MIN_VALUE, self::MAX_VALUE);
    }

    /**
     * (1, 1, 1, 1)
     * @return self
     */
    public static function white(): self
    {
        return new self(self::MAX_VALUE, self::MAX_VALUE, self::MAX_VALUE, self::MAX_VALUE);
    }

    /**
     * (0, 1, 1, 1)
     * @return self
     */
    public static function yellow(): self
    {
        return new self(self::MIN_VALUE, self::MAX_VALUE, self::MAX_VALUE, self::MAX_VALUE);
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @return self
     */
    public static function fromRGBA(int $red, int $green, int $blue, int $alpha = 255): self
    {
        return new self($red / 255, $green / 255, $blue / 255);
    }

    public function toRGBA(): array
    {
        return [
            'r' => $this->r * 255,
            'g' => $this->g * 255,
            'b' => $this->b * 255,
            'a' => $this->a * 255,
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toRGBA());
    }

    public function __serialize(): array
    {
        return $this->toRGBA();
    }

    public function __unserialize(array $data): void
    {
        $this->colors['r'] = $data['r'] ?? ($this->colors['r'] ?? self::MIN_VALUE);
        $this->colors['g'] = $data['g'] ?? ($this->colors['g'] ?? self::MIN_VALUE);
        $this->colors['b'] = $data['b'] ?? ($this->colors['b'] ?? self::MIN_VALUE);
        $this->colors['a'] = $data['a'] ?? ($this->colors['a'] ?? self::MAX_VALUE);
    }
}