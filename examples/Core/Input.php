<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\RequireComponent;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Debug;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\Rigidbody2D;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Enumerations\GamepadButton;
use Lenga\Engine\Enumerations\KeyCode;

/**
 * Runs left and right with the Horizontal axis and jumps with the Jump
 * button, using a Rigidbody2D so the character collides with the level.
 */
#[RequireComponent(Rigidbody2D::class)]
final class PlayerMovement extends Behaviour
{
    #[Tooltip('Running speed, in world units per second.')]
    #[Min(0)]
    public float $runSpeed = 6.0;

    #[Tooltip('Upward speed at the start of a jump, in world units per second.')]
    #[Min(0)]
    public float $jumpSpeed = 12.0;

    private ?Rigidbody2D $body = null;
    private float $moveInput = 0.0;
    private bool $jumpRequested = false;

    public function awake(): void
    {
        $this->body = $this->gameObject->getComponent(Rigidbody2D::class);
    }

    public function update(): void
    {
        // Read input in update(): it runs every frame, so no key press is missed.
        // Horizontal and Jump are axes from Project Settings > Input, so the
        // same code works for the keyboard and for a gamepad.
        $this->moveInput = Input::getAxis('Horizontal');

        // getButtonDown() is true only on the frame the button goes down,
        // so holding Jump does not jump again.
        if (Input::getButtonDown('Jump')) {
            $this->jumpRequested = true;
        }

        // Keys can also be read directly, here with a KeyCode case.
        if (Input::getKeyDown(KeyCode::ESCAPE)
            || Input::isGamepadButtonPressed(0, GamepadButton::MIDDLE_RIGHT)) {
            Debug::log('Pause requested');
        }
    }

    public function fixedUpdate(): void
    {
        // Apply movement in fixedUpdate(), which runs in step with physics.
        if ($this->body === null) {
            return;
        }

        $velocity = $this->body->velocity->clone();
        $velocity->x = $this->moveInput * $this->runSpeed;

        if ($this->jumpRequested && $this->body->isGrounded()) {
            // In a 2D scene positive Y points down the screen, so up is negative.
            $velocity->y = -$this->jumpSpeed;
        }

        $this->jumpRequested = false;
        $this->body->velocity = $velocity;
    }
}
