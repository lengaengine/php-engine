<?php

declare(strict_types=1);

namespace Lenga\Engine\SceneManagement;

use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\InstantiateOptions;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\UI\Canvas;
use RuntimeException;

/**
 * The scene that is running, with methods to find and create its objects and canvases.
 *
 * Get it with {@see Scene::getActive()} or {@see SceneManager::getActiveScene()}.
 * Every method works on the active scene. To change scenes, use
 * {@see SceneManager::loadScene()}.
 *
 * ```php
 * $hud = Scene::getActive()?->findCanvas('HUD');
 * $scoreLabel = $hud?->findTextByName('Score Label');
 * ```
 */
final class Scene
{
    /**
     * @internal
     * @param string $name The scene's name, as shown in the editor and in Scenes in Build. Pass it to {@see SceneManager::loadScene()} to reload this scene.
     */
    public function __construct(private(set) readonly string $name)
    {
    }

    /**
     * @internal
     * @param array{name?: string} $data
     */
    public static function fromNativeData(array $data): self
    {
        return new self((string) ($data['name'] ?? 'Scene'));
    }

    /**
     * Returns the scene that is running, or null when no scene is loaded.
     */
    public static function getActive(): ?self
    {
        /** @var array{name?: string}|false $data */
        $data = NativeEngine::call('scene_get_active');

        return \is_array($data) ? self::fromNativeData($data) : null;
    }

    /**
     * Creates an empty GameObject with a Transform in the active scene.
     *
     * @param string $name The name of the new GameObject.
     * @throws RuntimeException When the GameObject cannot be created, for example when no scene is running.
     */
    public function createGameObject(string $name): GameObject
    {
        return GameObject::create($name);
    }

    /**
     * Creates an empty Canvas in the active scene.
     *
     * Add elements to it with {@see Canvas::createText()}, {@see Canvas::createImage()},
     * {@see Canvas::createButton()} and {@see Canvas::createSlider()}.
     *
     * @param string $name The name of the new Canvas.
     * @throws RuntimeException When the Canvas cannot be created.
     */
    public function createCanvas(string $name): Canvas
    {
        /** @var array{id?: int, name?: string}|false $data */
        $data = NativeEngine::call('scene_create_canvas', $name);
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to create Canvas '{$name}' in the active scene.");
        }

        return Canvas::fromNativeLookupData($data);
    }

    /**
     * Returns every Canvas in the active scene.
     *
     * @return list<Canvas> The canvases, or an empty list when no scene is running.
     */
    public function getCanvases(): array
    {
        /** @var list<array{id?: int, name?: string}>|false $data */
        $data = NativeEngine::call('scene_get_canvases');
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): Canvas => Canvas::fromNativeLookupData($item),
            $data,
        ));
    }

    /**
     * Returns the first Canvas with the given name, or null when there is none.
     *
     * Call it once, for example in `start()`, and keep the result: each call
     * looks through every canvas in the scene.
     *
     * @param string $name The Canvas name, as shown in the Hierarchy. The match is exact and case-sensitive.
     */
    public function findCanvas(string $name): ?Canvas
    {
        foreach ($this->getCanvases() as $canvas) {
            if ($canvas->name === $name) {
                return $canvas;
            }
        }

        return null;
    }

    /**
     * Creates a copy of a GameObject in the active scene and returns the copy.
     *
     * Works the same as {@see GameObject::instantiate()} with a GameObject source.
     *
     * @param GameObject $original The GameObject to copy, including its children and components.
     * @param string|InstantiateOptions|array<string, mixed>|null $options A name for the instance, an {@see InstantiateOptions} with a position, rotation or parent, an array with the same keys, or null to keep the source's name and placement.
     * @throws RuntimeException When the copy cannot be created.
     */
    public function instantiateGameObject(
        GameObject $original,
        string|InstantiateOptions|array|null $options = null,
    ): GameObject
    {
        $instance = GameObject::instantiate($original, $options);
        if (!$instance instanceof GameObject) {
            throw new RuntimeException('Scene::instantiateGameObject() did not return a GameObject.');
        }

        return $instance;
    }

    /**
     * Creates an instance of a prefab asset in the active scene and returns its root GameObject.
     *
     * @param string $assetPath Project-relative path to the prefab, for example `Assets/Prefabs/Coin.prefab.json`.
     * @param string|InstantiateOptions|array<string, mixed>|null $options A name for the instance, an {@see InstantiateOptions} with a position, rotation or parent, an array with the same keys, or null to keep the source's name and placement.
     * @throws RuntimeException When the prefab cannot be found or instantiated.
     */
    public function instantiatePrefab(
        string $assetPath,
        string|InstantiateOptions|array|null $options = null,
    ): GameObject
    {
        $resolvedOptions = self::normalizeInstantiateOptions($options);

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('scene_instantiate_prefab', $assetPath, $resolvedOptions->toNativeArray());
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to instantiate prefab '{$assetPath}'.");
        }

        return GameObject::fromNativeLookupData($data);
    }

    /**
     * Returns the first GameObject in the active scene with the given name, or null when there is none.
     *
     * Works the same as {@see GameObject::find()}.
     *
     * @param string $name The GameObject name, as shown in the Hierarchy.
     */
    public function findGameObject(string $name): ?GameObject
    {
        return GameObject::find($name);
    }

    private static function normalizeInstantiateOptions(string|InstantiateOptions|array|null $options): InstantiateOptions
    {
        if ($options instanceof InstantiateOptions) {
            return $options;
        }

        if (\is_array($options)) {
            return InstantiateOptions::fromArray($options);
        }

        return new InstantiateOptions(name: $options);
    }
}
