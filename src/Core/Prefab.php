<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\SceneManagement\Scene;
use RuntimeException;

/**
 * Creates GameObjects from prefab assets while the game runs.
 *
 * A prefab is a saved GameObject, with its components and children, that you
 * can create copies of. Use it to spawn enemies, projectiles and pickups.
 */
final class Prefab
{
    private function __construct()
    {
    }

    /**
     * Creates an instance of a prefab asset in the active scene and returns its root GameObject.
     *
     * Pass a string to name the instance, or {@see InstantiateOptions} to
     * place it at a position, give it a rotation or a parent:
     *
     * ```php
     * $bullet = Prefab::instantiate(
     *     'Assets/Prefabs/Bullet.prefab.json',
     *     InstantiateOptions::at($this->transform->position),
     * );
     * ```
     *
     * Does the same as {@see Scene::instantiatePrefab()} on the active scene.
     *
     * @param string $assetPath The project-relative path shown in the Assets panel, for example `Assets/Prefabs/Projectile.prefab.json`.
     * @param string|InstantiateOptions|array<string, mixed>|null $options A name for the instance, an {@see InstantiateOptions}, an array with the same keys as {@see InstantiateOptions::fromArray()}, or null to keep the prefab's name and placement.
     * @throws RuntimeException When no scene is active, or the prefab cannot be found or instantiated.
     */
    public static function instantiate(
        string $assetPath,
        string|InstantiateOptions|array|null $options = null,
    ): GameObject
    {
        $scene = Scene::getActive();
        if ($scene === null) {
            throw new RuntimeException('Cannot instantiate a prefab without an active scene.');
        }

        return $scene->instantiatePrefab($assetPath, $options);
    }
}
