<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Signal
{
    /**
     * @var array<int, array{listener: callable, once: bool}>
     */
    private array $listeners = [];
    private int $nextListenerId = 1;

    public function add(callable $listener): SignalSubscription
    {
        return $this->register($listener, false);
    }

    public function addListener(callable $listener): SignalSubscription
    {
        return $this->add($listener);
    }

    public function once(callable $listener): SignalSubscription
    {
        return $this->register($listener, true);
    }

    public function onceListener(callable $listener): SignalSubscription
    {
        return $this->once($listener);
    }

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

    public function removeListener(SignalSubscription|callable $listener): void
    {
        $this->remove($listener);
    }

    public function clear(): void
    {
        $this->listeners = [];
    }

    public function hasListeners(): bool
    {
        return $this->listeners !== [];
    }

    public function count(): int
    {
        return \count($this->listeners);
    }

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
