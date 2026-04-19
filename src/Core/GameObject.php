<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\SceneManagement\Scene;

final class GameObject
{
    public function __construct(
        string $name,
        ?Transform $transform = null,
        bool $activeSelf = true,
        ?int $instanceId = null,
        string $sceneObjectId = '',
        string $tag = 'Untagged',
        int $layer = 0,
    ) {
        $this->nameValue = $name;
        $this->instanceId = $instanceId;
        $this->sceneObjectIdValue = $sceneObjectId;
        $this->tagValue = $tag;
        $this->layerValue = $layer;
        $this->activeSelfValue = $activeSelf;
        $this->activeInHierarchyValue = $activeSelf;
        $this->transformValue = $transform ?? new Transform(null, null, null, null, null, $instanceId);
        $this->transformValue->__internalAttachGameObject($this, $instanceId);
    }

    private ?int $instanceId = null;
    private string $sceneObjectIdValue = '';
    private string $nameValue;
    private Transform $transformValue;
    private string $tagValue = 'Untagged';
    private int $layerValue = 0;
    private bool $activeSelfValue = true;
    private bool $activeInHierarchyValue = true;

    public string $name {
        get {
            if ($this->instanceId !== null) {
                $this->nameValue = \lenga_internal_game_object_get_name($this->instanceId);
            }

            return $this->nameValue;
        }

        set(string $value) {
            $this->nameValue = $value;

            if ($this->instanceId !== null) {
                \lenga_internal_game_object_set_name($this->instanceId, $value);
            }
        }
    }

    public Transform $transform {
        get {
            return $this->transformValue;
        }
    }

    public string $tag {
        get {
            if ($this->instanceId !== null) {
                $this->tagValue = \lenga_internal_game_object_get_tag($this->instanceId);
            }

            return $this->tagValue;
        }

        set(string $value) {
            $this->tagValue = $value;

            if ($this->instanceId !== null) {
                \lenga_internal_game_object_set_tag($this->instanceId, $value);
            }
        }
    }

    public string $sceneObjectId {
        get {
            return $this->sceneObjectIdValue;
        }
    }

    public int $layer {
        get {
            if ($this->instanceId !== null) {
                $this->layerValue = \lenga_internal_game_object_get_layer($this->instanceId);
            }

            return $this->layerValue;
        }

        set(int $value) {
            $this->layerValue = $value;

            if ($this->instanceId !== null) {
                \lenga_internal_game_object_set_layer($this->instanceId, $value);
            }
        }
    }

    public bool $activeSelf {
        get {
            if ($this->instanceId !== null) {
                $this->activeSelfValue = \lenga_internal_game_object_get_active_self($this->instanceId);
            }

            return $this->activeSelfValue;
        }
    }

    public bool $activeInHierarchy {
        get {
            if ($this->instanceId !== null) {
                $this->activeInHierarchyValue = \lenga_internal_game_object_get_active_in_hierarchy($this->instanceId);
            }

            return $this->activeInHierarchyValue;
        }
    }

    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function __serialize(): array
    {
        return [
            '__lengaRefKind' => 'GameObject',
            'sceneObjectId' => $this->sceneObjectIdValue,
            'instanceId' => $this->instanceId,
            'name' => $this->nameValue,
        ];
    }

    public function __unserialize(array $data): void
    {
        $resolved = self::fromSerializedReference($data);
        if ($resolved !== null) {
            $this->instanceId = $resolved->instanceId;
            $this->sceneObjectIdValue = $resolved->sceneObjectIdValue;
            $this->nameValue = $resolved->nameValue;
            $this->transformValue = $resolved->transformValue;
            $this->tagValue = $resolved->tagValue;
            $this->layerValue = $resolved->layerValue;
            $this->activeSelfValue = $resolved->activeSelfValue;
            $this->activeInHierarchyValue = $resolved->activeInHierarchyValue;
            $this->transformValue->__internalAttachGameObject($this, $this->instanceId);
            return;
        }

        $this->instanceId = isset($data['instanceId']) && \is_int($data['instanceId']) ? $data['instanceId'] : null;
        $this->sceneObjectIdValue = isset($data['sceneObjectId']) && \is_string($data['sceneObjectId'])
            ? $data['sceneObjectId']
            : '';
        $this->nameValue = isset($data['name']) && \is_string($data['name']) ? $data['name'] : 'GameObject';
        $this->tagValue = 'Untagged';
        $this->layerValue = 0;
        $this->activeSelfValue = true;
        $this->activeInHierarchyValue = true;
        $this->transformValue = new Transform(null, null, null, null, null, $this->instanceId);
        $this->transformValue->__internalAttachGameObject($this, $this->instanceId);
    }

    public function setActive(bool $value): void
    {
        if ($this->instanceId !== null) {
            \lenga_internal_game_object_set_active_by_id($this->instanceId, $value);
            $this->activeSelfValue = \lenga_internal_game_object_get_active_self($this->instanceId);
            $this->activeInHierarchyValue = \lenga_internal_game_object_get_active_in_hierarchy($this->instanceId);
            return;
        }

        if ($this->activeSelfValue === $value) {
            return;
        }

        $this->activeSelfValue = $value;
        $this->activeInHierarchyValue = $value;
        \lenga_internal_game_object_set_active($this->nameValue, $value);
    }

    public function isActiveSelf(): bool
    {
        return $this->activeSelf;
    }

    public function isActiveInHierarchy(): bool
    {
        return $this->activeInHierarchy;
    }

    public function compareTag(string $tag): bool
    {
        return $this->tag === $tag;
    }

    public function getScene(): ?Scene
    {
        if ($this->instanceId === null) {
            return Scene::getActive();
        }

        /** @var array{name?: string}|false $data */
        $data = \lenga_internal_game_object_get_scene_by_id($this->instanceId);

        return \is_array($data) ? Scene::fromNativeData($data) : null;
    }

    public function getParent(): ?self
    {
        if ($this->instanceId === null) {
            return null;
        }

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_game_object_get_parent_by_id($this->instanceId);

        return \is_array($data) ? self::fromNativeLookupData($data) : null;
    }

    /**
     * @return list<GameObject>
     */
    public function getChildren(): array
    {
        if ($this->instanceId === null) {
            return [];
        }

        /** @var list<array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}>|false $data */
        $data = \lenga_internal_game_object_get_children_by_id($this->instanceId);
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $child): GameObject => self::fromNativeLookupData($child),
            $data,
        ));
    }

    public function childCount(): int
    {
        return \count($this->getChildren());
    }

    public function setParent(?self $parent, bool $worldPositionStays = true): bool
    {
        if ($this->instanceId === null) {
            return false;
        }

        return \lenga_internal_game_object_set_parent_by_id(
            $this->instanceId,
            $parent?->instanceId,
            $worldPositionStays,
        );
    }

    public function destroy(): void
    {
        if ($this->instanceId === null) {
            return;
        }

        \lenga_internal_game_object_destroy_by_id($this->instanceId);
    }

    public function hasComponent(string $type): bool
    {
        if ($this->instanceId === null) {
            return $this->getComponent($type) !== null;
        }

        $descriptor = self::normalizeComponentSpecifier($type);

        return \lenga_internal_game_object_has_component_by_id(
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );
    }

    public function tryGetComponent(string $type, ?object &$component = null): bool
    {
        $component = $this->getComponent($type);

        return $component !== null;
    }

    public function getComponent(string $type): object|null
    {
        if ($this->instanceId === null) {
            $descriptor = self::normalizeComponentSpecifier($type);
            if ($descriptor['nativeType'] === 'Transform') {
                return $this->transformValue;
            }

            return null;
        }

        $descriptor = self::normalizeComponentSpecifier($type);

        $nativeResult = \lenga_internal_game_object_get_component_by_id(
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );

        return $this->wrapNativeComponentResult($nativeResult);
    }

    /**
     * @return list<object>
     */
    public function getComponents(?string $type = null): array
    {
        if ($this->instanceId === null) {
            if ($type === null) {
                return [$this->transformValue];
            }

            $component = $this->getComponent($type);
            return $component !== null ? [$component] : [];
        }

        if ($type === null) {
            return $this->wrapNativeComponentResults(
                \lenga_internal_game_object_get_components_by_id($this->instanceId, null, null),
            );
        }

        $descriptor = self::normalizeComponentSpecifier($type);

        return $this->wrapNativeComponentResults(
            \lenga_internal_game_object_get_components_by_id(
                $this->instanceId,
                $descriptor['nativeType'],
                $descriptor['scriptClass'],
            ),
        );
    }

    /**
     * @return list<object>
     */
    public function getComponentsInChildren(?string $type = null, bool $includeInactive = false): array
    {
        $results = [];
        $this->collectComponentsInChildren($type, $includeInactive, $results);

        return $results;
    }

    public function getComponentInChildren(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInChildren($type, $includeInactive);

        return $components[0] ?? null;
    }

    /**
     * @return list<object>
     */
    public function getComponentsInParent(?string $type = null, bool $includeInactive = false): array
    {
        $results = [];
        $current = $this;

        while ($current !== null) {
            if ($includeInactive || $current->activeInHierarchy) {
                foreach ($current->getComponents($type) as $component) {
                    $results[] = $component;
                }
            }

            $current = $current->getParent();
        }

        return $results;
    }

    public function getComponentInParent(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInParent($type, $includeInactive);

        return $components[0] ?? null;
    }

    public function addComponent(string $type): object
    {
        if ($this->instanceId === null) {
            throw new \RuntimeException('Cannot add components to a detached GameObject proxy.');
        }

        $descriptor = self::normalizeComponentSpecifier($type);
        if ($descriptor['nativeType'] === 'Behaviour' && $descriptor['scriptClass'] === null) {
            throw new \InvalidArgumentException(
                'Adding a Behaviour requires a concrete script class, for example PlayerController::class.',
            );
        }

        $nativeResult = \lenga_internal_game_object_add_component_by_id(
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );

        $wrapped = $this->wrapNativeComponentResult($nativeResult);
        if ($wrapped === null) {
            throw new \RuntimeException("Failed to add component '{$type}' to '{$this->name}'.");
        }

        return $wrapped;
    }

    public function clone(?string $name = null): self
    {
        return self::instantiate($this, $name);
    }

    public static function find(string $name): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_game_object_find_by_name($name);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function findBySceneId(string $sceneObjectId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = \lenga_internal_game_object_find_by_scene_id($sceneObjectId);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function lookupByInstanceId(int $instanceId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = \lenga_internal_game_object_lookup_by_id($instanceId);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function findWithTag(string $tag): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_game_object_find_with_tag($tag);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * @return list<GameObject>
     */
    public static function findGameObjectsWithTag(string $tag): array
    {
        /** @var list<array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}>|false $results */
        $results = \lenga_internal_game_object_find_game_objects_with_tag($tag);
        if (!\is_array($results)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $result): GameObject => self::fromNativeLookupData($result),
            $results,
        ));
    }

    public static function create(string $name): self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_scene_create_game_object($name);
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to create GameObject '{$name}' in the active scene.");
        }

        return self::fromNativeLookupData($data);
    }

    public static function instantiate(self $original, ?string $name = null): self
    {
        $instanceId = $original->instanceId;
        if ($instanceId === null) {
            throw new \RuntimeException('Cannot instantiate a detached GameObject proxy.');
        }

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = \lenga_internal_game_object_instantiate_by_id($instanceId, $name);
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to instantiate GameObject '{$original->name}'.");
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * @param array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null} $data
     */
    public static function fromNativeLookupData(array $data, ?Transform $transform = null): self
    {
        $transformId = isset($data['transformId']) && \is_int($data['transformId'])
            ? $data['transformId']
            : null;

        $instanceId = isset($data['id']) && \is_int($data['id'])
            ? $data['id']
            : null;

        $gameObject = new self(
            (string) ($data['name'] ?? 'GameObject'),
            $transform ?? new Transform(null, null, null, null, $transformId, $instanceId),
            (bool) ($data['activeSelf'] ?? true),
            $instanceId,
            (string) ($data['sceneObjectId'] ?? ''),
            (string) ($data['tag'] ?? 'Untagged'),
            (int) ($data['layer'] ?? 0),
        );
        $gameObject->activeInHierarchyValue = (bool) ($data['activeInHierarchy'] ?? $gameObject->activeSelfValue);

        return $gameObject;
    }

    public static function fromSerializedReference(array $data): ?self
    {
        $sceneObjectId = isset($data['sceneObjectId']) && \is_string($data['sceneObjectId'])
            ? $data['sceneObjectId']
            : '';
        if ($sceneObjectId !== '') {
            $gameObject = self::findBySceneId($sceneObjectId);
            if ($gameObject !== null) {
                return $gameObject;
            }
        }

        $instanceId = isset($data['instanceId']) && \is_int($data['instanceId'])
            ? $data['instanceId']
            : null;
        if ($instanceId !== null) {
            return self::lookupByInstanceId($instanceId);
        }

        return null;
    }

    public static function wrapNativeComponentLookupData(mixed $nativeResult): object|null
    {
        if ($nativeResult === false || $nativeResult === null) {
            return null;
        }

        if (\is_object($nativeResult)) {
            return $nativeResult;
        }

        if (!\is_array($nativeResult)) {
            return null;
        }

        $gameObject = null;
        if (\is_array($nativeResult['gameObject'] ?? null)) {
            $gameObject = self::fromNativeLookupData($nativeResult['gameObject']);
        } elseif (isset($nativeResult['gameObjectId']) && \is_int($nativeResult['gameObjectId'])) {
            $gameObject = self::lookupByInstanceId($nativeResult['gameObjectId']);
        }

        if (!$gameObject instanceof self) {
            return null;
        }

        return $gameObject->wrapNativeComponentResult($nativeResult);
    }

    /**
     * @return list<object>
     */
    public static function wrapNativeComponentLookupResults(mixed $nativeResult): array
    {
        if (!\is_array($nativeResult)) {
            return [];
        }

        $components = [];
        foreach ($nativeResult as $componentResult) {
            $component = self::wrapNativeComponentLookupData($componentResult);
            if ($component !== null) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * @return array{nativeType: string, scriptClass: ?string}
     */
    private static function normalizeComponentSpecifier(string $type): array
    {
        $type = ltrim($type, '\\');

        return match ($type) {
            Transform::class, 'Transform' => ['nativeType' => 'Transform', 'scriptClass' => null],
            Camera::class, 'Camera' => ['nativeType' => 'Camera', 'scriptClass' => null],
            Rigidbody2D::class, 'Rigidbody2D' => ['nativeType' => 'Rigidbody2D', 'scriptClass' => null],
            PlatformEffector2D::class, 'PlatformEffector2D' => ['nativeType' => 'PlatformEffector2D', 'scriptClass' => null],
            AreaEffector2D::class, 'AreaEffector2D' => ['nativeType' => 'AreaEffector2D', 'scriptClass' => null],
            PointEffector2D::class, 'PointEffector2D' => ['nativeType' => 'PointEffector2D', 'scriptClass' => null],
            SurfaceEffector2D::class, 'SurfaceEffector2D' => ['nativeType' => 'SurfaceEffector2D', 'scriptClass' => null],
            BuoyancyEffector2D::class, 'BuoyancyEffector2D' => ['nativeType' => 'BuoyancyEffector2D', 'scriptClass' => null],
            BoxCollider2D::class, 'BoxCollider2D' => ['nativeType' => 'BoxCollider2D', 'scriptClass' => null],
            CircleCollider2D::class, 'CircleCollider2D' => ['nativeType' => 'CircleCollider2D', 'scriptClass' => null],
            BoxCollider3D::class, 'BoxCollider3D' => ['nativeType' => 'BoxCollider3D', 'scriptClass' => null],
            CapsuleCollider3D::class, 'CapsuleCollider3D' => ['nativeType' => 'CapsuleCollider3D', 'scriptClass' => null],
            CharacterController::class, 'CharacterController' => ['nativeType' => 'CharacterController', 'scriptClass' => null],
            SphereCollider3D::class, 'SphereCollider3D' => ['nativeType' => 'SphereCollider3D', 'scriptClass' => null],
            AudioSource::class, 'AudioSource' => ['nativeType' => 'AudioSource', 'scriptClass' => null],
            SpriteAnimation::class, 'SpriteAnimation' => ['nativeType' => 'SpriteAnimation', 'scriptClass' => null],
            RectangleRenderer::class, 'RectangleRenderer' => ['nativeType' => 'RectangleRenderer', 'scriptClass' => null],
            SpriteRenderer::class, 'SpriteRenderer' => ['nativeType' => 'SpriteRenderer', 'scriptClass' => null],
            ParticleSystem::class, 'ParticleSystem' => ['nativeType' => 'ParticleSystem', 'scriptClass' => null],
            CubeRenderer::class, 'CubeRenderer' => ['nativeType' => 'CubeRenderer', 'scriptClass' => null],
            SphereRenderer::class, 'SphereRenderer' => ['nativeType' => 'SphereRenderer', 'scriptClass' => null],
            CylinderRenderer::class, 'CylinderRenderer' => ['nativeType' => 'CylinderRenderer', 'scriptClass' => null],
            PlaneRenderer::class, 'PlaneRenderer' => ['nativeType' => 'PlaneRenderer', 'scriptClass' => null],
            MeshRenderer::class, 'MeshRenderer' => ['nativeType' => 'MeshRenderer', 'scriptClass' => null],
            ModelRenderer::class, 'ModelRenderer' => ['nativeType' => 'ModelRenderer', 'scriptClass' => null],
            Behaviour::class, 'Behaviour' => ['nativeType' => 'Behaviour', 'scriptClass' => null],
            default => self::normalizeDynamicComponentSpecifier($type),
        };
    }

    /**
     * @return array{nativeType: string, scriptClass: ?string}
     */
    private static function normalizeDynamicComponentSpecifier(string $type): array
    {
        if (\class_exists($type) && \is_subclass_of($type, Behaviour::class)) {
            return ['nativeType' => 'Behaviour', 'scriptClass' => $type];
        }

        return ['nativeType' => $type, 'scriptClass' => null];
    }

    /**
     * @param array<int, object> $results
     */
    private function collectComponentsInChildren(?string $type, bool $includeInactive, array &$results): void
    {
        if (!$includeInactive && !$this->activeInHierarchy) {
            return;
        }

        foreach ($this->getComponents($type) as $component) {
            $results[] = $component;
        }

        foreach ($this->getChildren() as $child) {
            $child->collectComponentsInChildren($type, $includeInactive, $results);
        }
    }

    /**
     * @return list<object>
     */
    private function wrapNativeComponentResults(mixed $nativeResult): array
    {
        if (!\is_array($nativeResult)) {
            return [];
        }

        $components = [];
        foreach ($nativeResult as $componentResult) {
            $component = $this->wrapNativeComponentResult($componentResult);
            if ($component !== null) {
                $components[] = $component;
            }
        }

        return $components;
    }

    private function wrapNativeComponentResult(mixed $nativeResult): object|null
    {
        if ($nativeResult === false || $nativeResult === null) {
            return null;
        }

        if (\is_object($nativeResult)) {
            return $nativeResult;
        }

        if (!\is_array($nativeResult)) {
            return null;
        }

        $componentId = isset($nativeResult['id']) && \is_int($nativeResult['id'])
            ? $nativeResult['id']
            : null;
        $componentType = (string) ($nativeResult['type'] ?? 'Component');

        if ($componentType === 'Transform') {
            $transformId = isset($nativeResult['transformId']) && \is_int($nativeResult['transformId'])
                ? $nativeResult['transformId']
                : null;
            $gameObjectId = isset($nativeResult['gameObjectId']) && \is_int($nativeResult['gameObjectId'])
                ? $nativeResult['gameObjectId']
                : $this->instanceId;

            if (
                $this->instanceId !== null
                && $gameObjectId === $this->instanceId
                && $this->transformValue->getNativeId() === $transformId
            ) {
                return $this->transformValue;
            }

            return new Transform(null, null, null, null, $transformId, $gameObjectId);
        }

        if ($componentId === null) {
            return null;
        }

        $component = match ($componentType) {
            'Camera' => new Camera($this, $componentId),
            'Rigidbody2D' => new Rigidbody2D($this, $componentId),
            'PlatformEffector2D' => new PlatformEffector2D($this, $componentId),
            'AreaEffector2D' => new AreaEffector2D($this, $componentId),
            'PointEffector2D' => new PointEffector2D($this, $componentId),
            'SurfaceEffector2D' => new SurfaceEffector2D($this, $componentId),
            'BuoyancyEffector2D' => new BuoyancyEffector2D($this, $componentId),
            'BoxCollider2D' => new BoxCollider2D($this, $componentId),
            'CircleCollider2D' => new CircleCollider2D($this, $componentId),
            'BoxCollider3D' => new BoxCollider3D($this, $componentId),
            'CapsuleCollider3D' => new CapsuleCollider3D($this, $componentId),
            'CharacterController' => new CharacterController($this, $componentId),
            'SphereCollider3D' => new SphereCollider3D($this, $componentId),
            'AudioSource' => new AudioSource($this, $componentId),
            'DirectionalLight' => new DirectionalLight($this, $componentId),
            'PointLight' => new PointLight($this, $componentId),
            'SpriteAnimation' => new SpriteAnimation($this, $componentId),
            'RectangleRenderer' => new RectangleRenderer($this, $componentId),
            'SpriteRenderer' => new SpriteRenderer($this, $componentId),
            'ParticleSystem' => new ParticleSystem($this, $componentId),
            'CubeRenderer' => new CubeRenderer($this, $componentId),
            'SphereRenderer' => new SphereRenderer($this, $componentId),
            'CylinderRenderer' => new CylinderRenderer($this, $componentId),
            'PlaneRenderer' => new PlaneRenderer($this, $componentId),
            'MeshRenderer' => new MeshRenderer($this, $componentId),
            'ModelRenderer' => new ModelRenderer($this, $componentId),
            default => new NativeComponent($this, $componentId, $componentType),
        };

        if (
            isset($nativeResult['sceneComponentId']) &&
            \is_string($nativeResult['sceneComponentId']) &&
            \method_exists($component, '__internalAttachSceneComponentId')
        ) {
            $component->__internalAttachSceneComponentId($nativeResult['sceneComponentId']);
        }

        return $component;
    }
}
