<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Transform;
use Lenga\Engine\Core\Vector3;

/**
 * Flies a drone back and forth between two world positions, turns it to face
 * where it is going, and spins its propeller child.
 */
final class DronePatrol extends Behaviour
{
    #[Tooltip('First patrol point in world space. Empty means the starting position.')]
    public ?Vector3 $pointA = null;

    #[Tooltip('Second patrol point in world space.')]
    public ?Vector3 $pointB = null;

    #[Tooltip('Flight speed in world units per second.')]
    #[Min(0)]
    public float $speed = 3.0;

    #[Tooltip('Propeller spin in degrees per second.')]
    public float $propellerSpeed = 720.0;

    private ?Transform $propeller = null;
    private bool $headingToB = true;

    public function start(): void
    {
        // Property reads return copies, so clone() is not needed to keep this value.
        $this->pointA ??= $this->transform->position;
        $this->pointB ??= Vector3::sum($this->pointA, new Vector3(5.0, 0.0, 0.0));

        // find() follows child names, one level per path segment.
        $this->propeller = $this->transform->find('Body/Propeller');
    }

    public function update(): void
    {
        $target = $this->headingToB ? $this->pointB : $this->pointA;
        if ($target === null) {
            return;
        }

        // Assign a whole new Vector3. Changing $this->transform->position->x would not move the drone.
        $this->transform->position = Vector3::moveTowards(
            $this->transform->position,
            $target,
            $this->speed * Time::deltaTime(),
        );

        if (Vector3::distance($this->transform->position, $target) > 0.01) {
            // lookAt() turns the drone so its forward (-Z) axis points at the target.
            $this->transform->lookAt($target);
        } else {
            $this->headingToB = !$this->headingToB;
        }

        // Rotating the child around its own Y axis leaves the drone's rotation alone.
        $this->propeller?->rotate(0.0, $this->propellerSpeed * Time::deltaTime(), 0.0);
    }
}
