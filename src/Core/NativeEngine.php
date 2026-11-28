<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Exceptions\LengaRuntimeUnavailableException;
use LogicException;
use function call_user_func_array;
use function function_exists;

/**
 * The single entry point through which the engine's PHP classes call into the
 * Lenga runtime.
 *
 * Scripts use the public engine classes, such as {@see GameObject},
 * {@see Input} and {@see Physics2D}, instead.
 *
 * @internal Engine plumbing. Scripts and samples never call it, and its
 * binding names are not a stable API.
 */
final class NativeEngine
{
    private const string NATIVE_PREFIX = 'lenga_internal_';
    private const string AVAILABILITY_PROBE = 'scene_get_active';

    private function __construct()
    {
        // static-only runtime boundary
    }

    /**
     * Returns whether the Lenga runtime is loaded in this PHP process.
     */
    public static function isAvailable(): bool
    {
        return self::hasFunction(self::AVAILABILITY_PROBE);
    }

    /**
     * Returns whether the runtime provides the named binding.
     *
     * @param string $bindingName The binding name without its prefix.
     * @throws LogicException When the name is empty, prefixed or namespaced.
     */
    public static function hasFunction(string $bindingName): bool
    {
        return function_exists(self::nativeFunctionName($bindingName));
    }

    /**
     * Throws when the runtime, or one of its bindings, is not loaded.
     *
     * @param string|null $bindingName The binding to check, or `null` to check that the runtime is loaded.
     * @throws LengaRuntimeUnavailableException When the binding is not available, for example in a plain PHP process.
     * @throws LogicException When the name is empty, prefixed or namespaced.
     */
    public static function requireAvailable(?string $bindingName = null): void
    {
        $bindingName ??= self::AVAILABILITY_PROBE;
        $functionName = self::nativeFunctionName($bindingName);
        if (function_exists($functionName)) {
            return;
        }

        throw self::unavailable($bindingName);
    }

    /**
     * Calls a runtime binding and returns its result.
     *
     * @param string $bindingName The binding name without its prefix.
     * @param mixed ...$arguments The arguments passed to the binding.
     * @throws LengaRuntimeUnavailableException When the binding is not available.
     * @throws LogicException When the name is empty, prefixed or namespaced.
     */
    public static function call(string $bindingName, mixed ...$arguments): mixed
    {
        self::requireAvailable($bindingName);

        return call_user_func_array(self::nativeFunctionName($bindingName), $arguments);
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
            throw new LogicException('Native bridge calls must use logical binding names.');
        }
    }

    private static function unavailable(string $bindingName): LengaRuntimeUnavailableException
    {
        return new LengaRuntimeUnavailableException(
            "The Lenga native runtime binding '{$bindingName}' is not available. Run this code through the Lenga editor or runtime; it cannot execute in a plain PHP process."
        );
    }
}
