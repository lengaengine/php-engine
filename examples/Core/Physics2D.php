<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Debug;
use Lenga\Engine\Core\Physics2D;
use Lenga\Engine\Core\Vector3;

/**
 * Checks for ground below the GameObject and collects nearby pickups.
 *
 * Put level geometry on the ground layer and pickups, with trigger colliders
 * tagged `Pickup`, on the pickup layer.
 */
final class GroundAndPickupProbe extends Behaviour
{
    #[Tooltip('Layer number of the level geometry.')]
    public int $groundLayer = 3;

    #[Tooltip('Layer number of collectable pickups.')]
    public int $pickupLayer = 4;

    #[Tooltip('How far below the object to look for ground, in world units.')]
    #[Min(0)]
    public float $groundProbeDistance = 20.0;

    #[Tooltip('How close a pickup must be to be collected, in world units.')]
    #[Min(0)]
    public float $pickupRadius = 24.0;

    public bool $isOnGround = false;

    public function fixedUpdate(): void
    {
        $position = $this->transform->position;

        // Positive Y points down in 2D scenes. Ignore triggers so pickups do not count as ground.
        $groundHit = Physics2D::raycast(
            $position,
            new Vector3(0.0, 1.0, 0.0),
            $this->groundProbeDistance,
            false,
            Physics2D::layerMask($this->groundLayer),
        );
        $this->isOnGround = $groundHit !== null;

        // Pickups use trigger colliders, so include triggers here.
        $nearby = Physics2D::overlapCircleAll(
            $position,
            $this->pickupRadius,
            true,
            Physics2D::layerMask($this->pickupLayer),
        );

        foreach ($nearby as $collider) {
            $pickup = $collider->gameObject;
            if (!$pickup->compareTag('Pickup')) {
                continue;
            }

            Debug::log('Collected ' . $pickup->name);
            $pickup->destroy();
        }
    }
}
