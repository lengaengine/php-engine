<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class SignalSubscription
{
    private ?\Closure $disposeCallback;
    private bool $disposed = false;

    public function __construct(\Closure $disposeCallback)
    {
        $this->disposeCallback = $disposeCallback;
    }

    public function dispose(): void
    {
        if ($this->disposed) {
            return;
        }

        $this->disposed = true;
        $disposeCallback = $this->disposeCallback;
        $this->disposeCallback = null;

        if ($disposeCallback instanceof \Closure) {
            $disposeCallback();
        }
    }

    public function isDisposed(): bool
    {
        return $this->disposed;
    }
}
