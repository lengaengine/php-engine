<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\SceneManagement\Scene;

final class Prefab
{
    private function __construct()
    {
    }

    public static function instantiate(string $assetPath, ?string $name = null): GameObject
    {
        $scene = Scene::getActive();
        if ($scene === null) {
            throw new \RuntimeException('Cannot instantiate a prefab without an active scene.');
        }

        return $scene->instantiatePrefab($assetPath, $name);
    }
}
