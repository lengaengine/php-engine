<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Debug;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\Rigidbody2D;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\ForceMode2D;
use Lenga\Engine\Enumerations\RigidbodyInterpolation;

/**
 * Runs left and right and jumps with a Rigidbody2D.
 *
 * Add it to a GameObject that has a Rigidbody2D and a BoxCollider2D.
 */
final class PlatformerMovement extends Behaviour
{
    #[Tooltip('Running speed, in world units per second.')]
    #[Min(0)]
    public float $runSpeed = 160.0;

    #[Tooltip('Strength of the jump impulse.')]
    #[Min(0)]
    public float $jumpImpulse = 420.0;

    private ?Rigidbody2D $body = null;
    private float $moveInput = 0.0;
    private bool $jumpRequested = false;

    public function start(): void
    {
        $this->body = $this->gameObject->getComponent(Rigidbody2D::class);
        if ($this->body === null) {
            Debug::warn('PlatformerMovement needs a Rigidbody2D on the same GameObject.');
            return;
        }

        // Keep the character upright and smooth its motion between physics steps.
        $this->body->freezeRotation = true;
        $this->body->interpolation = RigidbodyInterpolation::Interpolate;
    }

    public function update(): void
    {
        // Read input every frame so no key press is missed.
        $this->moveInput = Input::getAxis('Horizontal');
        if (Input::getButtonDown('Jump')) {
            $this->jumpRequested = true;
        }
    }

    public function fixedUpdate(): void
    {
        // Change the body in fixedUpdate(), which runs once per physics step.
        if ($this->body === null) {
            return;
        }

        // Velocity is a copy: change it, then assign it back.
        $velocity = $this->body->velocity->clone();
        $velocity->x = $this->moveInput * $this->runSpeed;
        $this->body->velocity = $velocity;

        if ($this->jumpRequested && $this->body->isGrounded()) {
            // Positive Y points down in 2D scenes, so a jump pushes toward negative Y.
            $this->body->addForce(new Vector2(0.0, -$this->jumpImpulse), ForceMode2D::Impulse);
        }

        $this->jumpRequested = false;
    }
}
