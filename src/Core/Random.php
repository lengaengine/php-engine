<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Picks random numbers for gameplay, such as spawn positions, damage rolls or a ball's starting direction.
 *
 * Random works the same way in desktop and Web builds. Until a script calls
 * {@see Random::setSeed()}, it is seeded from the current time, so each run
 * gives different numbers. Set a seed to get the same sequence of numbers
 * every time, for example to replay a level layout.
 *
 * The numbers are predictable to anyone who knows the seed. Do not use them
 * for passwords, tokens or anything security-related.
 *
 * ```php
 * $direction = Random::rangeInt(0, 1) === 0 ? -1.0 : 1.0;
 * $speed = Random::rangeFloat(180.0, 240.0);
 * ```
 */
final class Random
{
    private const int LCG_MODULUS = 2147483647;
    private const int LCG_MULTIPLIER = 48271;
    private const int LCG_QUOTIENT = 44488;
    private const int LCG_REMAINDER = 3399;

    private static int $seed = 0;
    private static int $lcgState = 0;

    private function __construct()
    {
    }

    /**
     * Starts the random sequence from a seed, so the same seed always produces the same numbers.
     *
     * @param int $seed Any integer. Zero and negative values are accepted.
     */
    public static function setSeed(int $seed): void
    {
        self::$seed = self::normalizeSeed($seed);
        self::$lcgState = self::$seed;
    }

    /**
     * Picks a random whole number between two values, including both.
     *
     * `Random::rangeInt(1, 6)` rolls a six-sided die. When `$max` is less than
     * `$min`, the two are swapped.
     *
     * @param int $min The smallest number that can be returned.
     * @param int $max The largest number that can be returned.
     */
    public static function rangeInt(int $min, int $max): int
    {
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        if ($min === $max) {
            return $min;
        }

        return $min + (self::nextLcgValue() % (($max - $min) + 1));
    }

    /**
     * Picks a random decimal number between two values.
     *
     * When `$max` is less than `$min`, the two are swapped. When they are
     * equal, that value is returned.
     *
     * @param float $min The lower end of the range.
     * @param float $max The upper end of the range.
     */
    public static function rangeFloat(float $min, float $max): float
    {
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        if ($min === $max) {
            return $min;
        }

        return $min + (($max - $min) * self::nextUnitFloat());
    }

    private static function nextUnitFloat(): float
    {
        return self::nextLcgValue() / self::LCG_MODULUS;
    }

    private static function nextLcgValue(): int
    {
        self::ensureSeeded();

        $high = intdiv(self::$lcgState, self::LCG_QUOTIENT);
        $low = self::$lcgState % self::LCG_QUOTIENT;
        $next = (self::LCG_MULTIPLIER * $low) - (self::LCG_REMAINDER * $high);
        if ($next <= 0) {
            $next += self::LCG_MODULUS;
        }

        self::$lcgState = $next;
        return self::$lcgState;
    }

    private static function ensureSeeded(): void
    {
        if (self::$seed !== 0 && self::$lcgState !== 0) {
            return;
        }

        self::setSeed(self::defaultSeed());
    }

    private static function defaultSeed(): int
    {
        $secondsSeed = time();
        $microtime = microtime(true);
        $fractionSeed = $microtime === false
            ? 0
            : (int)(($microtime - (float)$secondsSeed) * 1000000.0);
        $processSeed = function_exists('getmypid') ? (int)getmypid() : 0;
        $fileSeed = (int)(hexdec(substr(hash('crc32b', __FILE__), -6)) % 1048576);

        return $secondsSeed ^ $fractionSeed ^ ($processSeed * 1009) ^ $fileSeed;
    }

    private static function normalizeSeed(int $seed): int
    {
        $seed %= self::LCG_MODULUS;
        if ($seed < 0) {
            $seed += self::LCG_MODULUS;
        }

        return $seed === 0 ? 1 : $seed;
    }
}