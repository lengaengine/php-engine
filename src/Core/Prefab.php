<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\SceneManagement\Scene;
use RuntimeException;

final class Prefab
{
    private function __construct()
    {
    }

    /**
     * Instantiates a prefab asset into the active scene.
     *
     * The asset path should be the project-relative path shown in the Assets
     * panel, for example `Assets/Prefabs/Projectile.prefab.json`.
     */
    public static function instantiate(string $assetPath, ?string $name = null): GameObject
    {
        $scene = Scene::getActive();
        if ($scene === null) {
            throw new RuntimeException('Cannot instantiate a prefab without an active scene.');
        }

        return $scene->instantiatePrefab($assetPath, $name);
    }
}
