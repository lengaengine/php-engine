<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

use Lenga\Engine\Core\WaitForTween;
use Lenga\Engine\Internal\Bindings;

/**
 * Controls and inspects one running tween.
 *
 * Every {@see Tween} method returns a handle. Keep it when gameplay may need to
 * pause, resume, cancel or wait for the tween; ignore it for fire-and-forget
 * effects. A common pattern cancels the previous tween before starting the next
 * one, so only one tween writes to a value at a time:
 *
 * ```php
 * $this->currentMove?->cancel();
 * $this->currentMove = Tween::moveTo($this->transform, $target, 0.18);
 * ```
 *
 * A tween is cancelled automatically when its target is destroyed. When a
 * tween could not be created, for example because the UI element does not
 * have the requested property, Lenga logs a warning and the handle refers to
 * no tween: {@see TweenHandle::exists()} returns false.
 */
final class TweenHandle
{
    /**
     * @internal
     * @param int $id The tween ID.
     */
    public function __construct(
        private readonly int $id,
    ) {
    }

    /**
     * Returns the ID of the tween this handle controls.
     *
     * Two handles with the same ID control the same tween. The ID is 0 when the tween could not be created.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Pauses the tween where it is until {@see TweenHandle::resume()} is called.
     *
     * This affects only this tween. Game pause stops every tween that uses scaled time.
     *
     * @return bool True when the tween was paused; false when it has already completed, been cancelled, or does not exist.
     */
    public function pause(): bool
    {
        return Bindings::tweenPause($this->id);
    }

    /**
     * Continues a tween paused with {@see TweenHandle::pause()}.
     *
     * @return bool True when the tween was paused and is now playing again; false otherwise.
     */
    public function resume(): bool
    {
        return Bindings::tweenResume($this->id);
    }

    /**
     * Stops the tween and leaves the value where it is.
     *
     * A cancelled tween never reports {@see TweenHandle::isComplete()} as true.
     *
     * @return bool True when the tween existed; false when it does not exist.
     */
    public function cancel(): bool
    {
        return Bindings::tweenCancel($this->id);
    }

    /**
     * Returns whether the tween reached its end value.
     *
     * Returns false while the tween is waiting, playing or paused, and after it was cancelled.
     */
    public function isComplete(): bool
    {
        return Bindings::tweenIsComplete($this->id);
    }

    /**
     * Returns whether the tween is waiting out its delay or moving.
     *
     * Returns false when the tween is paused, completed or cancelled.
     */
    public function isPlaying(): bool
    {
        return Bindings::tweenIsPlaying($this->id);
    }

    /**
     * Returns whether Lenga still tracks this tween.
     *
     * Returns false when the tween could not be created.
     */
    public function exists(): bool
    {
        return Bindings::tweenExists($this->id);
    }

    /**
     * Returns a yield instruction that pauses a coroutine until the tween stops playing.
     *
     * The coroutine continues when the tween completes, is cancelled, is paused
     * or no longer exists, so it never waits forever. Check
     * {@see TweenHandle::isComplete()} afterwards to tell a finished tween from
     * an interrupted one.
     *
     * ```php
     * yield $move->wait();
     * if ($move->isComplete()) {
     *     $this->onArrived();
     * }
     * ```
     */
    public function wait(): WaitForTween
    {
        return new WaitForTween($this);
    }
}
