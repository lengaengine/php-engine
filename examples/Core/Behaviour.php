<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Enumerations\KeyCode;

/**
 * Moves its GameObject with the arrow keys in a 2D scene and returns it to
 * where it started when any script sends the `game.reset` event.
 */
final class ArrowKeyMover extends Behaviour
{
    // Public fields appear in the Inspector, so each GameObject can use its own speed.
    #[Tooltip('How far the object moves each second, in world units.')]
    #[Min(0)]
    public float $speed = 200.0;

    private Vector3 $startPosition;

    public function awake(): void
    {
        // awake() runs once, before any other lifecycle method.
        $this->startPosition = $this->transform->position->clone();
    }

    public function onEnable(): void
    {
        // The behaviour removes this listener when it is disabled, and
        // onEnable() adds it again when the behaviour is enabled.
        $this->onEvent('game.reset', function (): void {
            $this->transform->position = $this->startPosition->clone();
        });
    }

    public function update(): void
    {
        $direction = new Vector3(0.0, 0.0, 0.0);

        if (Input::getKey(KeyCode::LEFT_ARROW)) {
            $direction->x -= 1.0;
        }

        if (Input::getKey(KeyCode::RIGHT_ARROW)) {
            $direction->x += 1.0;
        }

        // In a 2D scene positive Y points down the screen.
        if (Input::getKey(KeyCode::UP_ARROW)) {
            $direction->y -= 1.0;
        }

        if (Input::getKey(KeyCode::DOWN_ARROW)) {
            $direction->y += 1.0;
        }

        // Scale by deltaTime so the speed is the same at any frame rate.
        // deltaTime is zero while the game is paused, so the object stops too.
        $step = $this->speed * Time::deltaTime();
        $this->transform->translate(
            new Vector3($direction->x * $step, $direction->y * $step, 0.0),
            false,
        );
    }
}
