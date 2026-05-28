<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

final class TweenOptions
{
    public function __construct(
        public float $delay = 0.0,
        public EasingFunction $easingFunction = EasingFunction::Linear,
        public bool $useUnscaledTime = false,
        public bool $relative = false,
    ) {
    }

    public static function make(): self
    {
        return new self();
    }

    public function delay(float $delay): self
    {
        $clone = clone $this;
        $clone->delay = max(0.0, $delay);
        return $clone;
    }

    public function ease(EasingFunction $easingFunction): self
    {
        $clone = clone $this;
        $clone->easingFunction = $easingFunction;
        return $clone;
    }

    public function unscaled(bool $useUnscaledTime = true): self
    {
        $clone = clone $this;
        $clone->useUnscaledTime = $useUnscaledTime;
        return $clone;
    }

    public function relative(bool $relative = true): self
    {
        $clone = clone $this;
        $clone->relative = $relative;
        return $clone;
    }
}
