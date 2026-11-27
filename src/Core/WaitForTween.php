<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Tweening\TweenHandle;

/**
 * Coroutine wait that resumes when a tween stops playing.
 *
 * Get one from {@see TweenHandle::wait()} and yield it from a coroutine started with
 * {@see Behaviour::startCoroutine()}. The coroutine continues once the tween completes,
 * is paused or cancelled, or no longer exists. A tween that is still in its start delay
 * counts as playing.
 *
 * ```php
 * private function runIntro(): \Generator
 * {
 *     $move = Tween::moveLocalTo($this->transform, new Vector3(0.0, 2.0, 0.0), 0.5);
 *     yield $move->wait();
 *
 *     $this->onIntroComplete();
 * }
 * ```
 *
 * Start several tweens before one `yield` to run them at the same time.
 */
final class WaitForTween extends YieldInstruction
{
    /**
     * Creates a wait for the given tween.
     *
     * {@see TweenHandle::wait()} returns the same wait and reads better in a coroutine.
     *
     * @param TweenHandle $handle Handle of the tween to wait for.
     */
    public function __construct(
        private readonly TweenHandle $handle,
    ) {
    }

    /**
     * Returns the handle of the tween this wait follows.
     */
    public function getHandle(): TweenHandle
    {
        return $this->handle;
    }

    /**
     * Returns `true` while the tween exists, is playing and has not completed.
     */
    public function keepWaiting(): bool
    {
        if (!$this->handle->exists()) {
            return false;
        }

        return $this->handle->isPlaying() && !$this->handle->isComplete();
    }
}
