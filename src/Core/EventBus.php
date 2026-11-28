<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;

/**
 * A global event channel that lets scripts react to gameplay events without
 * holding a reference to the object that sends them.
 *
 * Any script can send a named event with a payload, and every listener
 * registered for that name receives it. Use EventBus when several unrelated
 * systems react to the same moment, such as `game.health.died` or
 * `game.score.changed`. Use a {@see Signal} when one object owns the event and
 * listeners already hold a reference to it.
 *
 * Inside a {@see Behaviour}, prefer its `emitEvent()`, `onEvent()` and
 * `onceEvent()` helpers. They remove the listener when the behaviour is
 * disabled, so you do not have to dispose subscriptions yourself.
 *
 * Listeners run immediately, in the order they were added, during the call
 * that sends the event. Each listener receives the payload first and the event
 * name second.
 */
final class EventBus
{
    /**
     * The event the engine sends when the selected UI element changes through
     * UI navigation.
     *
     * The payload is an array with these keys:
     *
     * - `name`: the event name, `ui.selection_changed`.
     * - `viaPointer`: `true` when mouse or touch input changed the selection.
     * - `canvas`: lookup data for the {@see \Lenga\Engine\UI\Canvas} that owns the selection.
     * - `previous`: lookup data for the previously selected UI element, or `null`.
     * - `current`: lookup data for the newly selected UI element, or `null`.
     *
     * Turn element lookup data into an object with
     * {@see \Lenga\Engine\UI\UIElement::fromNativeLookupData()}.
     */
    public const string UI_SELECTION_CHANGED = 'ui.selection_changed';

    /**
     * @var array<string, array<int, array{listener: callable, once: bool}>>
     */
    private static array $listeners = [];
    private static int $nextListenerId = 1;

    /**
     * Adds a listener that runs every time the named event is sent.
     *
     * ```php
     * $subscription = EventBus::on('game.round_started', function (mixed $payload, string $eventName): void {
     *     // React to the new round.
     * });
     *
     * // Later, stop listening.
     * $subscription->dispose();
     * ```
     *
     * @param string $eventName The event name to listen for. Cannot be empty.
     * @param callable $listener Called with the payload and the event name each time the event is sent.
     * @return SignalSubscription Dispose it to remove the listener.
     * @throws InvalidArgumentException When `$eventName` is empty.
     */
    public static function on(string $eventName, callable $listener): SignalSubscription
    {
        return self::register($eventName, $listener, false);
    }

    /**
     * Adds a listener that runs every time the named event is sent. Same as {@see EventBus::on()}.
     *
     * @param string $eventName The event name to listen for. Cannot be empty.
     * @param callable $listener Called with the payload and the event name each time the event is sent.
     * @return SignalSubscription Dispose it to remove the listener.
     * @throws InvalidArgumentException When `$eventName` is empty.
     */
    public static function addListener(string $eventName, callable $listener): SignalSubscription
    {
        return self::on($eventName, $listener);
    }

    /**
     * Adds a listener that runs every time the named event is sent. Same as {@see EventBus::on()}.
     *
     * @param string $eventName The event name to listen for. Cannot be empty.
     * @param callable $listener Called with the payload and the event name each time the event is sent.
     * @return SignalSubscription Dispose it to remove the listener.
     * @throws InvalidArgumentException When `$eventName` is empty.
     */
    public static function subscribe(string $eventName, callable $listener): SignalSubscription
    {
        return self::on($eventName, $listener);
    }

    /**
     * Adds a listener that runs the next time the named event is sent, then
     * removes itself.
     *
     * The listener is removed before it runs, so sending the same event from
     * inside the listener does not call it again.
     *
     * @param string $eventName The event name to listen for. Cannot be empty.
     * @param callable $listener Called once with the payload and the event name.
     * @return SignalSubscription Dispose it to remove the listener before the event is sent.
     * @throws InvalidArgumentException When `$eventName` is empty.
     */
    public static function once(string $eventName, callable $listener): SignalSubscription
    {
        return self::register($eventName, $listener, true);
    }

    /**
     * Adds a listener that runs the next time the named event is sent, then
     * removes itself. Same as {@see EventBus::once()}.
     *
     * @param string $eventName The event name to listen for. Cannot be empty.
     * @param callable $listener Called once with the payload and the event name.
     * @return SignalSubscription Dispose it to remove the listener before the event is sent.
     * @throws InvalidArgumentException When `$eventName` is empty.
     */
    public static function onceListener(string $eventName, callable $listener): SignalSubscription
    {
        return self::once($eventName, $listener);
    }

    /**
     * Removes a listener from the named event.
     *
     * Passing the {@see SignalSubscription} disposes it. Passing a callable
     * removes every registration of that exact callable for the event, so keep
     * a reference to a closure if you plan to remove it this way.
     *
     * @param string $eventName The event the listener was added to.
     * @param SignalSubscription|callable $listener The subscription returned when the listener was added, or the same callable that was passed in.
     */
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

    /**
     * Removes a listener from the named event. Same as {@see EventBus::off()}.
     *
     * @param string $eventName The event the listener was added to.
     * @param SignalSubscription|callable $listener The subscription returned when the listener was added, or the same callable that was passed in.
     */
    public static function removeListener(string $eventName, SignalSubscription|callable $listener): void
    {
        self::off($eventName, $listener);
    }

    /**
     * Sends the named event to every listener registered for it.
     *
     * Listeners run immediately, in the order they were added. Nothing happens
     * when the event has no listeners.
     *
     * @param string $eventName The event to send.
     * @param mixed $payload Data passed to every listener, such as an array of details.
     */
    public static function emit(string $eventName, mixed $payload = null): void
    {
        self::dispatch($eventName, $payload);
    }

    /**
     * Sends the named event to every listener registered for it. Same as {@see EventBus::emit()}.
     *
     * @param string $eventName The event to send.
     * @param mixed $payload Data passed to every listener, such as an array of details.
     */
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

    /**
     * Removes every listener for one event, or for all events.
     *
     * This also removes listeners that other scripts added. Most scripts
     * should dispose the subscriptions they own instead.
     *
     * @param string|null $eventName The event to clear, or `null` to clear every event.
     */
    public static function clear(?string $eventName = null): void
    {
        if ($eventName === null) {
            self::$listeners = [];
            return;
        }

        unset(self::$listeners[$eventName]);
    }

    /**
     * Returns whether the named event has at least one listener.
     *
     * @param string $eventName The event to check.
     */
    public static function hasListeners(string $eventName): bool
    {
        return isset(self::$listeners[$eventName]) && self::$listeners[$eventName] !== [];
    }

    /**
     * Returns how many listeners are registered for the named event.
     *
     * @param string $eventName The event to count listeners for.
     */
    public static function listenerCount(string $eventName): int
    {
        return \count(self::$listeners[$eventName] ?? []);
    }

    /**
     * Delivers an event sent by the engine to the PHP listeners.
     *
     * Scripts send events with {@see EventBus::emit()} or {@see EventBus::dispatch()}.
     *
     * @internal Called by the runtime when the engine sends an event, such as
     *     {@see EventBus::UI_SELECTION_CHANGED}.
     *
     * @param string $eventName The event to send.
     * @param mixed $payload Data passed to every listener.
     */
    public static function __dispatchFromNative(string $eventName, mixed $payload = null): void
    {
        self::dispatch($eventName, $payload);
    }

    private static function register(string $eventName, callable $listener, bool $once): SignalSubscription
    {
        if ($eventName === '') {
            throw new InvalidArgumentException('EventBus event name cannot be empty.');
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
