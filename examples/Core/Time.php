<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Core\WaitForSecondsRealtime;
use Lenga\Engine\Enumerations\KeyCode;
use Lenga\Engine\UI\Canvas;

/**
 * Moves a turret along the X axis, fires on a cooldown, pauses gameplay with
 * Escape and freezes the action for a moment when it fires.
 */
final class TurretTimer extends Behaviour
{
    #[Tooltip('How far the turret slides each second, in world units.')]
    #[Min(0)]
    public float $speed = 120.0;

    #[Tooltip('Seconds of gameplay time between shots.')]
    #[Min(0)]
    public float $fireCooldown = 0.5;

    #[Tooltip('Real seconds the action freezes after each shot.')]
    #[Min(0)]
    public float $hitStopDuration = 0.06;

    public ?Canvas $pauseMenu = null;

    private float $nextShotAt = 0.0;
    private float $menuPulse = 0.0;

    public function update(): void
    {
        // Pause input is read in update(), which keeps running while gameplay is paused.
        if (Input::isKeyPressed(KeyCode::ESCAPE)) {
            Time::toggleGameplayPause();

            if ($this->pauseMenu !== null) {
                $this->pauseMenu->enabled = Time::isGameplayPaused();
            }
        }

        if (Time::isGameplayPaused()) {
            // Unscaled time keeps moving during a pause, so the menu can still animate.
            $this->menuPulse += Time::unscaledDeltaTime() * 2.0;
            return;
        }

        // deltaTime follows the time scale, so movement also slows during hit stop.
        $direction = Input::getAxis('Horizontal');
        $this->transform->translate(
            new Vector3($direction * $this->speed * Time::deltaTime(), 0.0, 0.0),
            false,
        );

        // Time::time() stops while gameplay is paused, so the cooldown does too.
        if (Input::getKey(KeyCode::SPACE) && Time::time() >= $this->nextShotAt) {
            $this->nextShotAt = Time::time() + $this->fireCooldown;
            $this->fire();
            $this->startCoroutine($this->hitStop());
        }
    }

    private function hitStop(): \Generator
    {
        $previousScale = Time::timeScale();
        Time::setTimeScale(0.0);

        // A real-time wait still finishes while the time scale is zero.
        yield new WaitForSecondsRealtime($this->hitStopDuration);

        Time::setTimeScale($previousScale);
    }

    private function fire(): void
    {
        // Spawn a projectile here.
    }
}
