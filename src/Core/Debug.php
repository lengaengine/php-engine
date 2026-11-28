<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Writes messages to the editor's Console while the game runs.
 *
 * Use it to trace what your scripts do and to report problems. Each message
 * ends with the file and line of the call, for example
 * `Player spawned (at /path/to/Player.php:42)`. Choose the method by
 * severity: {@see Debug::log()} and {@see Debug::info()} for information,
 * {@see Debug::warn()} for something that may be wrong, and
 * {@see Debug::error()} for a failure.
 *
 * When the game runs without the engine's logger, messages go to PHP's
 * error log with a `Debug:`, `Info:`, `Warning:` or `Error:` prefix.
 *
 * ```php
 * Debug::log('Player spawned');
 * Debug::warn("No spawn point found, using the origin");
 * Debug::error(['state' => $this->state, 'health' => $this->health]);
 * ```
 */
final class Debug
{
    /**
     * Writes a debug message.
     *
     * @param mixed $message A string, or an object with a `__toString()` method, which is
     *     converted to its string. Other values raise a `TypeError`; log arrays and
     *     numbers with {@see Debug::info()}.
     */
    public static function log(mixed $message): void
    {
        self::write('log', self::normalizeMessage($message));
    }

    /**
     * Writes an informational message.
     *
     * @param mixed $message The value to write. Strings are written as they are, arrays and
     *     objects in `print_r()` form, and other values in `var_export()` form.
     */
    public static function info(mixed $message): void
    {
        self::write('info', $message);
    }

    /**
     * Writes a warning, for something that may be wrong but does not stop the game.
     *
     * @param mixed $message The value to write. Strings are written as they are, arrays and
     *     objects in `print_r()` form, and other values in `var_export()` form.
     */
    public static function warn(mixed $message): void
    {
        self::write('warn', $message);
    }

    /**
     * Writes an error, for a failure that needs fixing.
     *
     * @param mixed $message The value to write. Strings are written as they are, arrays and
     *     objects in `print_r()` form, and other values in `var_export()` form.
     */
    public static function error(mixed $message): void
    {
        self::write('error', $message);
    }

    /**
     * Converts the given payload to a printable message and sends it to the
     * configured logging backend.
     *
     * Adds caller file/line metadata when available.
     *
     * @param string $level Logical log level (log, info, warn, error).
     * @param mixed $message Message payload to log.
     */
    private static function write(string $level, mixed $message): void
    {
        $logMessage = match(true) {
            is_string($message) => $message,
            is_array($message), is_object($message) => print_r($message, true),
            default => var_export($message, true),
        };
        $footer = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null;
        if ($footer !== null && isset($footer['file'], $footer['line'])) {
            $logMessage .= " (at {$footer['file']}:{$footer['line']})";
        }

        if (NativeEngine::hasFunction('debug_log')) {
            NativeEngine::call('debug_log', $logMessage, $level);
            return;
        }

        $fallbackLevel = match ($level) {
            'log' => 'Debug',
            'info' => 'Info',
            'warn', 'warning' => 'Warning',
            'error' => 'Error',
            default => 'Undefined',
        };
        error_log($fallbackLevel . ': ' . $logMessage);
    }

    /**
     * Normalizes stringable objects to a string for plain debug logging.
     *
     * @param mixed $message Message payload to normalize.
     * @return string Normalized message string value.
     */
    private static function normalizeMessage(mixed $message): string
    {
        return (is_object($message) && method_exists($message, '__toString')) ? (string) $message : $message;
    }
}
