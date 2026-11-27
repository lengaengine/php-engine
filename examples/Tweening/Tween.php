<?php

declare(strict_types=1);

namespace Game\Scripts;

use Generator;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Collision2D;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Tweening\EasingFunction;
use Lenga\Engine\Tweening\Tween;
use Lenga\Engine\Tweening\TweenHandle;
use Lenga\Engine\Tweening\TweenOptions;
use Lenga\Engine\UI\Text;

/**
 * A coin that floats up into place when the level starts, then pops and
 * counts the score up on the HUD when the player touches it.
 */
final class CoinPickup extends Behaviour
{
    // Assign the HUD's score Text in the Inspector.
    #[Tooltip('HUD text that shows the score.')]
    public ?Text $scoreLabel = null;

    #[Tooltip('Points this coin is worth.')]
    public int $points = 10;

    private static int $score = 0;
    private ?TweenHandle $float = null;
    private bool $collected = false;

    public function start(): void
    {
        // relative() makes the target an offset, so the coin rises 0.25 units
        // from wherever it was placed in the scene.
        $this->float = Tween::moveLocalTo(
            $this->transform,
            new Vector3(0.0, 0.25, 0.0),
            0.6,
            TweenOptions::make()
                ->relative()
                ->ease(EasingFunction::EaseInOutSine),
        );
    }

    public function onTriggerEnter2D(Collision2D $collision): void
    {
        if ($this->collected || $collision->otherGameObject?->compareTag('Player') !== true) {
            return;
        }

        $this->collected = true;
        $this->startCoroutine($this->collect());
    }

    private function collect(): Generator
    {
        // Stop the float-up first so only one tween moves the coin.
        $this->float?->cancel();

        // Two tweens started together run at the same time.
        Tween::moveLocalTo(
            $this->transform,
            new Vector3(0.0, 0.5, 0.0),
            0.15,
            TweenOptions::make()->relative()->ease(EasingFunction::EaseOutQuad),
        );
        $pop = Tween::scaleTo(
            $this->transform,
            new Vector3(1.4, 1.4, 1.0),
            0.15,
            TweenOptions::make()->ease(EasingFunction::EaseOutCubic),
        );

        // wait() pauses this coroutine until the pop stops playing.
        yield $pop->wait();

        $shrink = Tween::scaleTo(
            $this->transform,
            new Vector3(0.0, 0.0, 1.0),
            0.12,
            TweenOptions::make()->ease(EasingFunction::EaseInQuad),
        );

        // The label counts from the number it shows now up to the new score.
        self::$score += $this->points;
        if ($this->scoreLabel !== null) {
            Tween::uiTextNumberTo(
                $this->scoreLabel,
                self::$score,
                0.4,
                TweenOptions::make()->ease(EasingFunction::EaseOutQuad),
                prefix: 'Score: ',
            );
        }

        yield $shrink->wait();

        $this->gameObject->setActive(false);
    }
}
