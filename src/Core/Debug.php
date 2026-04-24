<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Lightweight debug logging utility used by the engine and user scripts.
 *
 * Messages are forwarded to the native logger when available and otherwise
 * written to PHP's default error log with a mapped severity label.
 */
final class Debug
{
    /**
     * Writes a generic debug log entry.
     *
     * @param mixed $message Message payload to log.
     */
    public static function log(mixed $message): void
    {
        self::write('log', self::normalizeMessage($message));
    }

    /**
     * Writes an informational log entry.
     *
     * @param mixed $message Message payload to log.
     */
    public static function info(mixed $message): void
    {
        self::write('info', $message);
    }

    /**
     * Writes a warning log entry.
     *
     * @param mixed $message Message payload to log.
     */
    public static function warn(mixed $message): void
    {
        self::write('warn', $message);
    }

    /**
     * Writes an error log entry.
     *
     * @param mixed $message Message payload to log.
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

        if (function_exists('lenga_internal_debug_log')) {
            lenga_internal_debug_log($logMessage, $level);
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
