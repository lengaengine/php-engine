<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Process-wide PHP event bus for cross-system gameplay and engine events.
 *
 * Use local Signal instances when one behaviour owns an event. Use EventBus
 * when the event is intentionally global and several unrelated systems may
 * need to listen.
 */
final class EventBus
{
    public const UI_SELECTION_CHANGED = 'ui.selection_changed';

    /**
     * @var array<string, array<int, array{listener: callable, once: bool}>>
     */
    private static array $listeners = [];
    private static int $nextListenerId = 1;

    public static function on(string $eventName, callable $listener): SignalSubscription
    {
        return self::register($eventName, $listener, false);
    }

    public static function addListener(string $eventName, callable $listener): SignalSubscription
    {
        return self::on($eventName, $listener);
    }

    public static function subscribe(string $eventName, callable $listener): SignalSubscription
    {
        return self::on($eventName, $listener);
    }

    public static function once(string $eventName, callable $listener): SignalSubscription
    {
        return self::register($eventName, $listener, true);
    }

    public static function onceListener(string $eventName, callable $listener): SignalSubscription
    {
        return self::once($eventName, $listener);
    }

    public static function off(string $eventName, SignalSubscription|callable $listener): void
    {
        if ($listener instanceof SignalSubscription) {
            $listener->dispose();
            return;
        }

        if (!isset(self::$listeners[$eventName])) {
            return;
        }

        foreach (self::$listeners[$eventName] as $listenerId => $entry) {
            if ($entry['listener'] === $listener) {
                unset(self::$listeners[$eventName][$listenerId]);
            }
        }

        if (self::$listeners[$eventName] === []) {
            unset(self::$listeners[$eventName]);
        }
    }

    public static function removeListener(string $eventName, SignalSubscription|callable $listener): void
    {
        self::off($eventName, $listener);
    }

    public static function emit(string $eventName, mixed $payload = null): void
    {
        self::dispatch($eventName, $payload);
    }

    public static function dispatch(string $eventName, mixed $payload = null): void
    {
        if (!isset(self::$listeners[$eventName]) || self::$listeners[$eventName] === []) {
            return;
        }

        foreach (\array_keys(self::$listeners[$eventName]) as $listenerId) {
            if (!isset(self::$listeners[$eventName][$listenerId])) {
                continue;
            }

            $entry = self::$listeners[$eventName][$listenerId];
            if ($entry['once']) {
                unset(self::$listeners[$eventName][$listenerId]);
            }

            ($entry['listener'])($payload, $eventName);
        }

        if (isset(self::$listeners[$eventName]) && self::$listeners[$eventName] === []) {
            unset(self::$listeners[$eventName]);
        }
    }

    public static function clear(?string $eventName = null): void
    {
        if ($eventName === null) {
            self::$listeners = [];
            return;
        }

        unset(self::$listeners[$eventName]);
    }

    public static function hasListeners(string $eventName): bool
    {
        return isset(self::$listeners[$eventName]) && self::$listeners[$eventName] !== [];
    }

    public static function listenerCount(string $eventName): int
    {
        return \count(self::$listeners[$eventName] ?? []);
    }

    /**
     * Internal bridge entry point used by the native engine event bus.
     *
     * User code should call emit() or dispatch() instead.
     */
    public static function __dispatchFromNative(string $eventName, mixed $payload = null): void
    {
        self::dispatch($eventName, $payload);
    }

    private static function register(string $eventName, callable $listener, bool $once): SignalSubscription
    {
        if ($eventName === '') {
            throw new \InvalidArgumentException('EventBus event name cannot be empty.');
        }

        $listenerId = self::$nextListenerId++;
        self::$listeners[$eventName][$listenerId] = [
            'listener' => $listener,
            'once' => $once,
        ];

        return new SignalSubscription(static function () use ($eventName, $listenerId): void {
            self::removeById($eventName, $listenerId);
        });
    }

    private static function removeById(string $eventName, int $listenerId): void
    {
        unset(self::$listeners[$eventName][$listenerId]);

        if (isset(self::$listeners[$eventName]) && self::$listeners[$eventName] === []) {
            unset(self::$listeners[$eventName]);
        }
    }
}
