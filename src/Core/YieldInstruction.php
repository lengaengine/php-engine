<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Base class for the values a coroutine yields to wait before it continues.
 *
 * A coroutine is a method that returns a `\Generator` and is started with
 * {@see Behaviour::startCoroutine()}. Each `yield` pauses the coroutine. When it
 * yields a YieldInstruction, Lenga asks the instruction each frame whether to keep
 * waiting and continues the coroutine once {@see YieldInstruction::keepWaiting()}
 * returns `false`. Yielding any other value, such as `null`, resumes the coroutine
 * after the next `update()` call.
 *
 * Lenga includes {@see WaitForSeconds}, {@see WaitForSecondsRealtime},
 * {@see WaitUntil}, {@see WaitWhile}, {@see WaitForFixedUpdate} and
 * {@see WaitForTween}. Extend this class to write your own wait condition:
 *
 * ```php
 * use Lenga\Engine\Core\SpriteAnimation;
 * use Lenga\Engine\Core\YieldInstruction;
 *
 * final class WaitForAnimationEnd extends YieldInstruction
 * {
 *     public function __construct(private readonly SpriteAnimation $animation)
 *     {
 *     }
 *
 *     public function keepWaiting(): bool
 *     {
 *         return $this->animation->isPlaying();
 *     }
 * }
 *
 * // Inside a coroutine:
 * yield new WaitForAnimationEnd($this->animation);
 * ```
 */
abstract class YieldInstruction
{
    /**
     * Resume phase that checks the instruction after each `update()` call.
     */
    public const string PHASE_UPDATE = 'update';

    /**
     * Resume phase that checks the instruction after each `fixedUpdate()` call.
     */
    public const string PHASE_FIXED_UPDATE = 'fixedUpdate';

    /**
     * Returns the frame step in which Lenga checks this instruction and resumes the coroutine.
     *
     * Lenga calls {@see YieldInstruction::keepWaiting()} only during this phase, and the
     * coroutine continues in it. Override it and return
     * {@see YieldInstruction::PHASE_FIXED_UPDATE} for a wait that should line up with
     * physics steps.
     *
     * @return string {@see YieldInstruction::PHASE_UPDATE} or {@see YieldInstruction::PHASE_FIXED_UPDATE}.
     *     Defaults to {@see YieldInstruction::PHASE_UPDATE}.
     */
    public function resumePhase(): string
    {
        return self::PHASE_UPDATE;
    }

    /**
     * Returns whether the coroutine should stay paused.
     *
     * Lenga calls this after each `update()` call, or after each `fixedUpdate()` call
     * when {@see YieldInstruction::resumePhase()} returns
     * {@see YieldInstruction::PHASE_FIXED_UPDATE}. The first check happens at the next
     * such call after the `yield`. Return `false` to let the coroutine continue.
     *
     * @return bool `true` to keep waiting, `false` to resume the coroutine.
     */
    abstract public function keepWaiting(): bool;
}
