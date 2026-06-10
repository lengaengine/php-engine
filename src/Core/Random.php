<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Gameplay-oriented pseudo-random helper with a deterministic fallback path.
 *
 * PHP's random extension can depend on host entropy and can currently abort the
 * wasm runtime when it throws internally. This helper intentionally stays in
 * pure PHP so gameplay samples have the same path on desktop and Web exports.
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

    public static function setSeed(int $seed): void
    {
        self::$seed = self::normalizeSeed($seed);
        self::$lcgState = self::$seed;
    }

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