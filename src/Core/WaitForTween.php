<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Tweening\TweenHandle;

final class WaitForTween extends YieldInstruction
{
    public function __construct(
        private readonly TweenHandle $handle,
    ) {
    }

    public function getHandle(): TweenHandle
    {
        return $this->handle;
    }

    public function keepWaiting(): bool
    {
        if (!$this->handle->exists()) {
            return false;
        }

        return $this->handle->isPlaying() && !$this->handle->isComplete();
    }
}
