<?php

declare(strict_types=1);

namespace Lenga\Engine\SceneManagement;

use Lenga\Engine\Core\GameObject;
use Lenga\Engine\UI\Canvas;

final class Scene
{
    public function __construct(private readonly string $nameValue)
    {
    }

    public string $name {
        get {
            return $this->nameValue;
        }
    }

    /**
     * @param array{name?: string} $data
     */
    public static function fromNativeData(array $data): self
    {
        return new self((string) ($data['name'] ?? 'Scene'));
    }

    public static function getActive(): ?self
    {
        /** @var array{name?: string}|false $data */
        $data = \lenga_internal_scene_get_active();

        return \is_array($data) ? self::fromNativeData($data) : null;
    }

    public function createGameObject(string $name): GameObject
    {
        return GameObject::create($name);
    }

    public function createCanvas(string $name): Canvas
    {
        /** @var array{id?: int, name?: string}|false $data */
        $data = \lenga_internal_scene_create_canvas($name);
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to create Canvas '{$name}' in the active scene.");
        }

        return Canvas::fromNativeLookupData($data);
    }

    /**
     * @return list<Canvas>
     */
    public function getCanvases(): array
    {
        /** @var list<array{id?: int, name?: string}>|false $data */
        $data = \lenga_internal_scene_get_canvases();
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): Canvas => Canvas::fromNativeLookupData($item),
            $data,
        ));
    }

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
     * @return list<BackdropLayer>
     */
    public function getBackdropLayers(): array
    {
        /** @var list<array{index?: int}>|false $data */
        $data = \lenga_internal_scene_get_backdrop_layers();
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): BackdropLayer => BackdropLayer::fromNativeData($item),
            $data,
        ));
    }

    public function getBackdropLayer(int $index): ?BackdropLayer
    {
        /** @var array{index?: int}|false $data */
        $data = \lenga_internal_scene_get_backdrop_layer_state($index);

        return \is_array($data) ? BackdropLayer::fromNativeData($data) : null;
    }

    public function getBackdropLayerCount(): int
    {
        return \count($this->getBackdropLayers());
    }

    public function instantiateGameObject(GameObject $original, ?string $name = null): GameObject
    {
        return GameObject::instantiate($original, $name);
    }

    public function instantiatePrefab(string $assetPath, ?string $name = null): GameObject
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_scene_instantiate_prefab($assetPath, $name);
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to instantiate prefab '{$assetPath}'.");
        }

        return GameObject::fromNativeLookupData($data);
    }

    public function findGameObject(string $name): ?GameObject
    {
        return GameObject::find($name);
    }
}
