<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Exceptions\LengaRuntimeUnavailableException;

final class NativeEngine
{
    private const NATIVE_PREFIX = 'lenga_internal_';
    private const AVAILABILITY_PROBE = 'scene_get_active';

    private function __construct()
    {
        // static-only runtime boundary
    }

    public static function isAvailable(): bool
    {
        return self::hasFunction(self::AVAILABILITY_PROBE);
    }

    public static function hasFunction(string $bindingName): bool
    {
        return \function_exists(self::nativeFunctionName($bindingName));
    }

    public static function requireAvailable(?string $bindingName = null): void
    {
        $bindingName ??= self::AVAILABILITY_PROBE;
        $functionName = self::nativeFunctionName($bindingName);
        if (\function_exists($functionName)) {
            return;
        }

        throw self::unavailable($bindingName);
    }

    public static function call(string $bindingName, mixed ...$arguments): mixed
    {
        self::requireAvailable($bindingName);

        return \call_user_func_array(self::nativeFunctionName($bindingName), $arguments);
    }

    private static function nativeFunctionName(string $bindingName): string
    {
        self::assertLogicalBindingName($bindingName);

        return self::NATIVE_PREFIX . $bindingName;
    }

    private static function assertLogicalBindingName(string $bindingName): void
    {
        if (
            $bindingName === ''
            || str_starts_with($bindingName, self::NATIVE_PREFIX)
            || str_contains($bindingName, '\\')
            || str_contains($bindingName, '::')
        ) {
            throw new \LogicException('Native bridge calls must use logical binding names.');
        }
    }

    private static function unavailable(string $bindingName): LengaRuntimeUnavailableException
    {
        return new LengaRuntimeUnavailableException(
            "The Lenga native runtime binding '{$bindingName}' is not available. Run this code through the Lenga editor or runtime; it cannot execute in a plain PHP process."
        );
    }
}
