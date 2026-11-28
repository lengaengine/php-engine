<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use Closure;
use Lenga\Engine\Core\NativeEngine;
use ReflectionFunction;
use Throwable;

use function is_array;
use function is_object;
use function is_string;

/**
 * Reports an exception that script code did not catch, so the game carries
 * on: the engine logs it with its class, message, file, line and trace, in
 * the same format as exceptions it catches itself.
 *
 * The engine guards every call it makes into PHP. These guards sit inside
 * PHP where one engine call runs several pieces of script code that must
 * not stop each other: a Behaviour's Update and its coroutines, and the
 * listeners of a Signal or an EventBus event.
 *
 * @internal
 */
final class ScriptExceptions
{
    private function __construct()
    {
    }

    /**
     * Logs a throwable that script code did not catch.
     *
     * @param string $context Where it was thrown, for example "Player::update()".
     */
    public static function report(Throwable $throwable, string $context): void
    {
        NativeEngine::call('script_exception_report', $throwable, $context);
    }

    /**
     * Names a listener for an exception report: "Class::method()",
     * "function()", or "closure in File.php:12".
     */
    public static function describeListener(callable $listener): string
    {
        if (is_string($listener)) {
            return $listener . '()';
        }
        if (is_array($listener)) {
            $owner = is_object($listener[0]) ? $listener[0]::class : (string) $listener[0];

            return $owner . '::' . $listener[1] . '()';
        }
        if ($listener instanceof Closure) {
            $function = new ReflectionFunction($listener);
            $file = $function->getFileName();

            return $file === false
                ? 'closure'
                : 'closure in ' . \basename($file) . ':' . $function->getStartLine();
        }

        return $listener::class . '::__invoke()';
    }
}
