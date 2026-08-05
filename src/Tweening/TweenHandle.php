<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

use Lenga\Engine\Core\WaitForTween;
use Lenga\Engine\Internal\Bindings;

final class TweenHandle
{
    public function __construct(
        private readonly int $id,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function pause(): bool
    {
        return Bindings::tweenPause($this->id);
    }

    public function resume(): bool
    {
        return Bindings::tweenResume($this->id);
    }

    public function cancel(): bool
    {
        return Bindings::tweenCancel($this->id);
    }

    public function isComplete(): bool
    {
        return Bindings::tweenIsComplete($this->id);
    }

    public function isPlaying(): bool
    {
        return Bindings::tweenIsPlaying($this->id);
    }

    public function exists(): bool
    {
        return Bindings::tweenExists($this->id);
    }

    public function wait(): WaitForTween
    {
        return new WaitForTween($this);
    }
}
