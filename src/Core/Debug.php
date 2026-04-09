<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Debug
{
    public static function log(mixed $message): void
    {
        self::write('log', $message);
    }

    public static function info(mixed $message): void
    {
        self::write('info', $message);
    }

    public static function warn(mixed $message): void
    {
        self::write('warn', $message);
    }

    public static function error(mixed $message): void
    {
        self::write('error', $message);
    }

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
            default => 'Debug',
        };
        error_log($fallbackLevel . ': ' . $logMessage);
    }
}
