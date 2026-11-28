<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Closure;

/**
 * Handle to one listener registered on a {@see Signal} or on the {@see EventBus}.
 *
 * {@see Signal::add()}, {@see Signal::once()}, {@see EventBus::on()} and the
 * Behaviour event helpers return a subscription. Keep it while the listener
 * should run and call {@see SignalSubscription::dispose()} to stop listening,
 * for example in `onDisable()`. Subscriptions created through a Behaviour's own
 * event helpers are released for you when the Behaviour is disabled or destroyed.
 *
 * ```php
 * private ?SignalSubscription $subscription = null;
 *
 * public function onEnable(): void
 * {
 *     $this->subscription = $this->door?->onOpened->add(function (Door $door): void {
 *         $this->flashIndicator();
 *     });
 * }
 *
 * public function onDisable(): void
 * {
 *     $this->subscription?->dispose();
 *     $this->subscription = null;
 * }
 * ```
 */
final class SignalSubscription
{
    private ?Closure $disposeCallback;
    private bool $disposed = false;

    /**
     * Creates a subscription that runs a callback when it is disposed.
     *
     * @internal Signals and the event bus create subscriptions. Scripts receive them from
     *     {@see Signal::add()}, {@see EventBus::on()} and related methods.
     *
     * @param Closure $disposeCallback Callback that removes the listener. It runs once, on the
     *     first call to {@see SignalSubscription::dispose()}.
     */
    public function __construct(Closure $disposeCallback)
    {
        $this->disposeCallback = $disposeCallback;
    }

    /**
     * Removes the listener so it no longer receives dispatches.
     *
     * Calling it again after the first call does nothing.
     */
    public function dispose(): void
    {
        if ($this->disposed) {
            return;
        }

        $this->disposed = true;
        $disposeCallback = $this->disposeCallback;
        $this->disposeCallback = null;

        if ($disposeCallback instanceof Closure) {
            $disposeCallback();
        }
    }

    /**
     * Returns whether {@see SignalSubscription::dispose()} has been called on this subscription.
     *
     * A listener registered with `once()` removes itself after its first dispatch without
     * marking its subscription disposed, so this method can return `false` for a one-shot
     * listener that has already run.
     */
    public function isDisposed(): bool
    {
        return $this->disposed;
    }
}
