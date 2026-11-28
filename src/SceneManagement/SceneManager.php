<?php

declare(strict_types=1);

namespace Lenga\Engine\SceneManagement;

use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\InstantiateOptions;
use Lenga\Engine\Core\NativeEngine;
use LogicException;
use RuntimeException;

/**
 * Loads scenes and gives scripts access to the scene that is running.
 *
 * Create scenes in the editor, then open **Project > Build & Export** and add
 * them under **Scenes in Build**. A scene must be listed and enabled there
 * before a script can load it by name. Call these methods from a
 * {@see \Lenga\Engine\Core\Behaviour} attached to a GameObject in the running scene.
 *
 * Loading is deferred: the current scene keeps running until every script has
 * finished `update()` and `lateUpdate()` for the frame, then the new scene
 * replaces it before the frame is drawn.
 *
 * @example examples/SceneManagement/SceneManager.php Load the next level, reload on death and return to the menu
 */
final class SceneManager
{
    /**
     * Returns the scene that is running, or null when no scene is loaded.
     *
     * Use it to find GameObjects and canvases, or to read the scene's name.
     * Same as {@see Scene::getActive()}.
     */
    public static function getActiveScene(): ?Scene
    {
        return Scene::getActive();
    }

    /**
     * Requests a change to another scene, or a reload of the current one.
     *
     * Lenga checks the scene straight away and switches to it at the end of the
     * frame. Code after this call still runs in the current scene.
     *
     * ```php
     * SceneManager::loadScene('Level1');
     * SceneManager::loadScene('Assets/Scenes/Level1.scene.json');
     * ```
     *
     * @param string $sceneNameOrPath A scene name from Scenes in Build, such as `Level1`, or a project-relative scene path.
     * @throws RuntimeException When the scene cannot be found, is not enabled in Scenes in Build, or fails validation. Use {@see SceneManager::tryLoadScene()} to handle that case without an exception.
     */
    public static function loadScene(string $sceneNameOrPath): void
    {
        if (!self::tryLoadScene($sceneNameOrPath)) {
            throw new RuntimeException(
                "Failed to load scene '$sceneNameOrPath'. Make sure it exists and is enabled in Build Settings.",
            );
        }
    }

    /**
     * Requests a scene change and returns whether the request was accepted.
     *
     * Use it when a missing scene should be handled in code. When it returns
     * false, Lenga logs a warning that explains why.
     *
     * @param string $sceneNameOrPath A scene name from Scenes in Build, such as `Level1`, or a project-relative scene path.
     * @return bool True when the scene will load at the end of the frame; false when it cannot be found, is not enabled in Scenes in Build, or fails validation.
     */
    public static function tryLoadScene(string $sceneNameOrPath): bool
    {
        return NativeEngine::call('scene_load', $sceneNameOrPath);
    }

    /**
     * Requests a scene change by project-relative path.
     *
     * Works the same as {@see SceneManager::loadScene()}. Use it when you want
     * the code to show that the value is a path.
     *
     * @param string $scenePath Project-relative scene path, for example `Assets/Scenes/MainMenu.scene.json`.
     * @throws RuntimeException When the scene cannot be found, is not enabled in Scenes in Build, or fails validation.
     */
    public static function loadSceneByPath(string $scenePath): void
    {
        self::loadScene($scenePath);
    }

    /**
     * Creates an instance of a prefab asset in the active scene and returns its root GameObject.
     *
     * ```php
     * $coin = SceneManager::instantiatePrefab('Assets/Prefabs/Coin.prefab.json', 'Coin Pickup');
     * ```
     *
     * @param string $assetPath Project-relative path to the prefab, for example `Assets/Prefabs/Coin.prefab.json`.
     * @param string|InstantiateOptions|array<string, mixed>|null $options A name for the instance, an {@see InstantiateOptions} with a position, rotation or parent, an array with the same keys, or null to keep the prefab's name and placement.
     * @throws RuntimeException When no scene is running, or the prefab cannot be found or instantiated.
     */
    public static function instantiatePrefab(
        string $assetPath,
        string|InstantiateOptions|array|null $options = null,
    ): GameObject
    {
        $scene = self::getActiveScene();
        if ($scene === null) {
            throw new RuntimeException('Cannot instantiate a prefab without an active scene.');
        }

        return $scene->instantiatePrefab($assetPath, $options);
    }

    /**
     * Not supported: scripts cannot create scenes at runtime.
     *
     * Create scenes in the editor and load them with {@see SceneManager::loadScene()}.
     *
     * @param string $name The scene name.
     * @throws LogicException Always.
     */
    public static function createScene(string $name): Scene
    {
        throw new LogicException(
            "SceneManager::createScene('{$name}') is not supported by the embedded runtime yet.",
        );
    }

    /**
     * Not supported: scripts cannot switch the active scene object directly.
     *
     * Use {@see SceneManager::loadScene()} or {@see SceneManager::tryLoadScene()} to change scenes.
     *
     * @param Scene $scene The scene to make active.
     * @throws LogicException Always.
     */
    public static function setActiveScene(Scene $scene): void
    {
        throw new LogicException(
            "SceneManager::setActiveScene() is not supported by the embedded runtime yet.",
        );
    }
}
