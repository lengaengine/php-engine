<?php

declare(strict_types=1);

namespace Lenga\Engine\SceneManagement;

use Lenga\Engine\Core\GameObject;

final class SceneManager
{
    public static function getActiveScene(): ?Scene
    {
        return Scene::getActive();
    }

    public static function loadScene(string $sceneNameOrPath): void
    {
        if (!self::tryLoadScene($sceneNameOrPath)) {
            throw new \RuntimeException(
                "Failed to load scene '{$sceneNameOrPath}'. Make sure it exists and is enabled in Build Settings.",
            );
        }
    }

    public static function tryLoadScene(string $sceneNameOrPath): bool
    {
        return \lenga_internal_scene_load($sceneNameOrPath);
    }

    public static function loadSceneByPath(string $scenePath): void
    {
        self::loadScene($scenePath);
    }

    public static function instantiatePrefab(string $assetPath, ?string $name = null): GameObject
    {
        $scene = self::getActiveScene();
        if ($scene === null) {
            throw new \RuntimeException('Cannot instantiate a prefab without an active scene.');
        }

        return $scene->instantiatePrefab($assetPath, $name);
    }

    public static function createScene(string $name): Scene
    {
        throw new \LogicException(
            "SceneManager::createScene('{$name}') is not supported by the embedded runtime yet.",
        );
    }

    public static function setActiveScene(Scene $scene): void
    {
        throw new \LogicException(
            "SceneManager::setActiveScene() is not supported by the embedded runtime yet.",
        );
    }
}
