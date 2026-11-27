<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A list of callbacks that one object exposes and calls when something happens to it.
 *
 * Use a Signal when listeners already have a reference to the object they
 * want to observe, such as an indicator that watches one door. The owner
 * creates the signal, usually with {@see Behaviour::createSignal()}, and calls
 * {@see Signal::dispatch()}; listeners subscribe with {@see Signal::add()}
 * or {@see Signal::once()}. For game-wide events where listeners do not know
 * the sender, use {@see Behaviour::emitEvent()} and
 * {@see Behaviour::onEvent()} instead.
 *
 * ```php
 * final class Door extends Behaviour
 * {
 *     public Signal $onOpened;
 *
 *     public function awake(): void
 *     {
 *         $this->onOpened = $this->createSignal();
 *     }
 *
 *     public function open(): void
 *     {
 *         $this->onOpened->dispatch($this);
 *     }
 * }
 *
 * // In a listener:
 * $this->subscription = $this->door?->onOpened->add(function (Door $door): void {
 *     $this->flashIndicator();
 * });
 * ```
 *
 * Keep the {@see SignalSubscription} that `add()` returns and call its
 * `dispose()` method, for example in `onDisable()`, to stop listening.
 */
final class Signal
{
    /**
     * @var array<int, array{listener: callable, once: bool}>
     */
    private array $listeners = [];
    private int $nextListenerId = 1;

    /**
     * Adds a listener that is called every time the signal is dispatched.
     *
     * @param callable $listener The function to call. It receives the arguments passed to {@see Signal::dispatch()}.
     * @return SignalSubscription A handle whose `dispose()` method removes the listener.
     */
    public function add(callable $listener): SignalSubscription
    {
        return $this->register($listener, false);
    }

    /**
     * Adds a listener that is called every time the signal is dispatched.
     *
     * Does the same as {@see Signal::add()}.
     *
     * @param callable $listener The function to call. It receives the arguments passed to {@see Signal::dispatch()}.
     * @return SignalSubscription A handle whose `dispose()` method removes the listener.
     */
    public function addListener(callable $listener): SignalSubscription
    {
        return $this->add($listener);
    }

    /**
     * Adds a listener that is called the next time the signal is dispatched, then removed.
     *
     * @param callable $listener The function to call. It receives the arguments passed to {@see Signal::dispatch()}.
     * @return SignalSubscription A handle whose `dispose()` method removes the listener before it runs.
     */
    public function once(callable $listener): SignalSubscription
    {
        return $this->register($listener, true);
    }

    /**
     * Adds a listener that is called the next time the signal is dispatched, then removed.
     *
     * Does the same as {@see Signal::once()}.
     *
     * @param callable $listener The function to call. It receives the arguments passed to {@see Signal::dispatch()}.
     * @return SignalSubscription A handle whose `dispose()` method removes the listener before it runs.
     */
    public function onceListener(callable $listener): SignalSubscription
    {
        return $this->once($listener);
    }

    /**
     * Removes a listener.
     *
     * Pass the subscription that `add()` or `once()` returned, or the same
     * callable that was added. Passing a callable removes every registration
     * of that exact callable. A new closure with the same code is a different
     * callable, so keep the subscription instead when the listener is a
     * closure.
     *
     * @param SignalSubscription|callable $listener The subscription, or the callable that was added.
     */
    public function remove(SignalSubscription|callable $listener): void
    {
        if ($listener instanceof SignalSubscription) {
            $listener->dispose();
            return;
        }

        foreach ($this->listeners as $id => $entry) {
            if ($entry['listener'] === $listener) {
                unset($this->listeners[$id]);
            }
        }
    }

    /**
     * Removes a listener.
     *
     * Does the same as {@see Signal::remove()}.
     *
     * @param SignalSubscription|callable $listener The subscription, or the callable that was added.
     */
    public function removeListener(SignalSubscription|callable $listener): void
    {
        $this->remove($listener);
    }

    /**
     * Removes every listener.
     */
    public function clear(): void
    {
        $this->listeners = [];
    }

    /**
     * Checks whether the signal has at least one listener.
     */
    public function hasListeners(): bool
    {
        return $this->listeners !== [];
    }

    /**
     * Counts the signal's listeners.
     */
    public function count(): int
    {
        return \count($this->listeners);
    }

    /**
     * Calls every listener with the given arguments.
     *
     * Listeners run in the order they were added. A listener added with
     * `once()` is removed before it runs. A listener added during a dispatch
     * runs from the next dispatch on, and a listener removed during a
     * dispatch is not called if it has not run yet.
     *
     * @param mixed ...$arguments The values to pass to each listener.
     */
    public function dispatch(mixed ...$arguments): void
    {
        if ($this->listeners === []) {
            return;
        }

        foreach (\array_keys($this->listeners) as $listenerId) {
            if (!isset($this->listeners[$listenerId])) {
                continue;
            }

            $entry = $this->listeners[$listenerId];
            if ($entry['once']) {
                unset($this->listeners[$listenerId]);
            }

            ($entry['listener'])(...$arguments);
        }
    }

    /**
     * Calls every listener with the given arguments.
     *
     * Does the same as {@see Signal::dispatch()}.
     *
     * @param mixed ...$arguments The values to pass to each listener.
     */
    public function invoke(mixed ...$arguments): void
    {
        $this->dispatch(...$arguments);
    }

    private function register(callable $listener, bool $once): SignalSubscription
    {
        $listenerId = $this->nextListenerId++;
        $this->listeners[$listenerId] = [
            'listener' => $listener,
            'once' => $once,
        ];

        return new SignalSubscription(function () use ($listenerId): void {
            unset($this->listeners[$listenerId]);
        });
    }
}
