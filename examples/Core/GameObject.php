<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\InstantiateOptions;
use Lenga\Engine\Core\Rigidbody2D;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Vector3;

/**
 * A turret that fires a projectile prefab at the nearest object tagged
 * `Enemy`, keeping the spawned projectiles together under one parent.
 */
final class Turret extends Behaviour
{
    // Drag a prefab asset from the Assets panel onto this field in the Inspector.
    public ?GameObject $projectilePrefab = null;

    #[Tooltip('Seconds between shots.')]
    #[Min(0.05)]
    public float $fireInterval = 0.5;

    #[Tooltip('Speed of each projectile, in world units per second.')]
    #[Min(0)]
    public float $projectileSpeed = 10.0;

    public string $targetTag = 'Enemy';

    private ?GameObject $projectileRoot = null;
    private float $nextShotTime = 0.0;

    public function start(): void
    {
        // Look objects up once and keep them, rather than searching every frame.
        $this->projectileRoot = GameObject::find('Projectiles') ?? GameObject::create('Projectiles');
    }

    public function update(): void
    {
        if ($this->projectilePrefab === null || Time::time() < $this->nextShotTime) {
            return;
        }

        $target = $this->findNearestTarget();
        if ($target === null) {
            return;
        }

        $this->nextShotTime = Time::time() + $this->fireInterval;
        $this->fireAt($target);
    }

    private function findNearestTarget(): ?GameObject
    {
        $nearest = null;
        $nearestDistance = INF;

        foreach (GameObject::findGameObjectsWithTag($this->targetTag) as $candidate) {
            // Skip targets that are switched off, or whose parent is.
            if (!$candidate->activeInHierarchy) {
                continue;
            }

            $distance = Vector3::distance($this->transform->position, $candidate->transform->position);
            if ($distance < $nearestDistance) {
                $nearest = $candidate;
                $nearestDistance = $distance;
            }
        }

        return $nearest;
    }

    private function fireAt(GameObject $target): void
    {
        // Spawn the prefab at the turret, parented under the Projectiles object.
        // The position stays in world space even though the copy has a parent.
        $projectile = GameObject::instantiate(
            $this->projectilePrefab,
            InstantiateOptions::at(
                $this->transform->position,
                parent: $this->projectileRoot,
                name: 'Projectile',
            ),
        );

        $direction = Vector3::difference($target->transform->position, $this->transform->position)->normalized;

        // getComponent() returns null when the prefab has no Rigidbody2D.
        $body = $projectile->getComponent(Rigidbody2D::class);
        if ($body !== null) {
            $body->velocity = Vector3::scaleNew($direction, $this->projectileSpeed);
        }
    }
}
