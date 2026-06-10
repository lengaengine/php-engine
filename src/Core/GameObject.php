<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Closure;
use InvalidArgumentException;
use Lenga\Engine\Interfaces\ComponentInterface;
use Lenga\Engine\Interfaces\RendererInterface;
use Lenga\Engine\SceneManagement\Scene;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use function array_map;
use function array_values;
use function class_exists;
use function count;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function is_subclass_of;
use function interface_exists;
use function ltrim;

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
        $this->attachTransformToSelf($instanceId);
    }

    private function attachTransformToSelf(?int $gameObjectId = null): void
    {
        $gameObject = $this;
        $bound = Closure::bind(
            function () use ($gameObject, $gameObjectId): void {
                $this->gameObjectValue = $gameObject;
                $this->gameObjectId = $gameObjectId ?? $gameObject->getInstanceId();
            },
            $this->transformValue,
            Transform::class,
        );

        if ($bound === null) {
            throw new RuntimeException('Failed to bind Transform to GameObject.');
        }

        $bound();
    }

    private ?int $instanceId = null;
    private string $sceneObjectIdValue = '';
    private string $nameValue;
    private Transform $transformValue;
    private string $tagValue = 'Untagged';
    private int $layerValue = 0;
    private bool $activeSelfValue = true;
    private bool $activeInHierarchyValue = true;
    /**
     * @var array<string, class-string<Component>>
     */
    private static array $registeredComponentWrapperClasses = [];
    /**
     * @var array<class-string<Component>, string>
     */
    private static array $registeredComponentNativeTypesByClass = [];

    public string $name {
        get {
            if ($this->instanceId !== null) {
                $this->nameValue = NativeEngine::call('game_object_get_name', $this->instanceId);
            }

            return $this->nameValue;
        }

        set(string $value) {
            $this->nameValue = $value;

            if ($this->instanceId !== null) {
                NativeEngine::call('game_object_set_name', $this->instanceId, $value);
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
                $this->tagValue = NativeEngine::call('game_object_get_tag', $this->instanceId);
            }

            return $this->tagValue;
        }

        set(string $value) {
            $this->tagValue = $value;

            if ($this->instanceId !== null) {
                NativeEngine::call('game_object_set_tag', $this->instanceId, $value);
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
                $this->layerValue = NativeEngine::call('game_object_get_layer', $this->instanceId);
            }

            return $this->layerValue;
        }

        set(int $value) {
            $this->layerValue = $value;

            if ($this->instanceId !== null) {
                NativeEngine::call('game_object_set_layer', $this->instanceId, $value);
            }
        }
    }

    public bool $activeSelf {
        get {
            if ($this->instanceId !== null) {
                $this->activeSelfValue = NativeEngine::call('game_object_get_active_self', $this->instanceId);
            }

            return $this->activeSelfValue;
        }
    }

    public bool $activeInHierarchy {
        get {
            if ($this->instanceId !== null) {
                $this->activeInHierarchyValue = NativeEngine::call('game_object_get_active_in_hierarchy', $this->instanceId);
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
            $this->attachTransformToSelf($this->instanceId);
            return;
        }

        $this->instanceId = isset($data['instanceId']) && is_int($data['instanceId']) ? $data['instanceId'] : null;
        $this->sceneObjectIdValue = isset($data['sceneObjectId']) && is_string($data['sceneObjectId'])
            ? $data['sceneObjectId']
            : '';
        $this->nameValue = isset($data['name']) && is_string($data['name']) ? $data['name'] : 'GameObject';
        $this->tagValue = 'Untagged';
        $this->layerValue = 0;
        $this->activeSelfValue = true;
        $this->activeInHierarchyValue = true;
        $this->transformValue = new Transform(null, null, null, null, null, $this->instanceId);
        $this->attachTransformToSelf($this->instanceId);
    }

    public function setActive(bool $value): void
    {
        if ($this->instanceId !== null) {
            NativeEngine::call('game_object_set_active_by_id', $this->instanceId, $value);
            $this->activeSelfValue = NativeEngine::call('game_object_get_active_self', $this->instanceId);
            $this->activeInHierarchyValue = NativeEngine::call('game_object_get_active_in_hierarchy', $this->instanceId);
            return;
        }

        if ($this->activeSelfValue === $value) {
            return;
        }

        $this->activeSelfValue = $value;
        $this->activeInHierarchyValue = $value;
        NativeEngine::call('game_object_set_active', $this->nameValue, $value);
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
        $data = NativeEngine::call('game_object_get_scene_by_id', $this->instanceId);

        return is_array($data) ? Scene::fromNativeData($data) : null;
    }

    public function getParent(): ?self
    {
        if ($this->instanceId === null) {
            return null;
        }

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('game_object_get_parent_by_id', $this->instanceId);

        return is_array($data) ? self::fromNativeLookupData($data) : null;
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
        $data = NativeEngine::call('game_object_get_children_by_id', $this->instanceId);
        if (!is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $child): GameObject => self::fromNativeLookupData($child),
            $data,
        ));
    }

    public function childCount(): int
    {
        return count($this->getChildren());
    }

    public function setParent(?self $parent, bool $worldPositionStays = true): bool
    {
        if ($this->instanceId === null) {
            return false;
        }

        return NativeEngine::call('game_object_set_parent_by_id',
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

        NativeEngine::call('game_object_destroy_by_id', $this->instanceId);
    }

    public function hasComponent(string $type): bool
    {
        if ($this->instanceId === null) {
            return $this->getComponent($type) !== null;
        }

        $descriptor = self::normalizeComponentSpecifier($type);

        return NativeEngine::call('game_object_has_component_by_id',
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );
    }

    /**
     * Attempts to resolve a component attached to this object.
     *
     * Pass a concrete component class, such as `Rigidbody2D::class`, for IDEs
     * and static analysers to infer the specific component wrapper type.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @param TComponent|null $component
     * @param-out TComponent|null $component
     */
    public function tryGetComponent(string $type, ?object &$component = null): bool
    {
        $component = $this->getComponent($type);

        return $component !== null;
    }

    /**
     * Resolves the first component of the requested type attached to this object.
     *
     * Prefer passing concrete class names, for example `Rigidbody2D::class`, so
     * editor tooling can infer `Rigidbody2D|null` instead of an implementation
     * detail union. Native component names such as `"Rigidbody2D"` remain
     * supported for dynamic runtime code.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent|null
     */
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

        $nativeResult = NativeEngine::call('game_object_get_component_by_id',
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );

        return $this->wrapNativeComponentResult($nativeResult);
    }

    /**
     * Resolves all matching components attached to this object.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type
     * @return ($type is null ? list<object> : list<TComponent>)
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
                NativeEngine::call('game_object_get_components_by_id', $this->instanceId, null, null),
            );
        }

        $descriptor = self::normalizeComponentSpecifier($type);

        return $this->wrapNativeComponentResults(
            NativeEngine::call('game_object_get_components_by_id',
                $this->instanceId,
                $descriptor['nativeType'],
                $descriptor['scriptClass'],
            ),
        );
    }

    /**
     * Resolves matching components on this object and its descendants.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type
     * @return ($type is null ? list<object> : list<TComponent>)
     */
    public function getComponentsInChildren(?string $type = null, bool $includeInactive = false): array
    {
        $results = [];
        $this->collectComponentsInChildren($type, $includeInactive, $results);

        return $results;
    }

    /**
     * Resolves the first matching component on this object or its descendants.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent|null
     */
    public function getComponentInChildren(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInChildren($type, $includeInactive);

        return $components[0] ?? null;
    }

    /**
     * Resolves matching components on this object and its ancestors.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type
     * @return ($type is null ? list<object> : list<TComponent>)
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

    /**
     * Resolves the first matching component on this object or its ancestors.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent|null
     */
    public function getComponentInParent(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInParent($type, $includeInactive);

        return $components[0] ?? null;
    }

    /**
     * Adds a component to this object and returns the concrete wrapper.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent
     */
    public function addComponent(string $type): object
    {
        if ($this->instanceId === null) {
            throw new RuntimeException('Cannot add components to a detached GameObject proxy.');
        }

        $descriptor = self::normalizeComponentSpecifier($type);
        if ($descriptor['nativeType'] === 'Behaviour' && $descriptor['scriptClass'] === null) {
            throw new InvalidArgumentException(
                'Adding a Behaviour requires a concrete script class, for example PlayerController::class.',
            );
        }

        $nativeResult = NativeEngine::call('game_object_add_component_by_id',
            $this->instanceId,
            $descriptor['nativeType'],
            $descriptor['scriptClass'],
        );

        $wrapped = $this->wrapNativeComponentResult($nativeResult);
        if ($wrapped === null) {
            throw new RuntimeException("Failed to add component '{$type}' to '{$this->name}'.");
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
        $data = NativeEngine::call('game_object_find_by_name', $name);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function findBySceneId(string $sceneObjectId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = NativeEngine::call('game_object_find_by_scene_id', $sceneObjectId);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function lookupByInstanceId(int $instanceId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = NativeEngine::call('game_object_lookup_by_id', $instanceId);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    public static function findWithTag(string $tag): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('game_object_find_with_tag', $tag);
        if (!is_array($data)) {
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
        $results = NativeEngine::call('game_object_find_game_objects_with_tag', $tag);
        if (!is_array($results)) {
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
        $data = NativeEngine::call('scene_create_game_object', $name);
        if (!is_array($data)) {
            throw new RuntimeException("Failed to create GameObject '{$name}' in the active scene.");
        }

        return self::fromNativeLookupData($data);
    }

    public static function instantiate(self $original, ?string $name = null): self
    {
        $instanceId = $original->instanceId;
        if ($instanceId === null) {
            throw new RuntimeException('Cannot instantiate a detached GameObject proxy.');
        }

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('game_object_instantiate_by_id', $instanceId, $name);
        if (!is_array($data)) {
            throw new RuntimeException("Failed to instantiate GameObject '{$original->name}'.");
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * @param array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null} $data
     */
    public static function fromNativeLookupData(array $data, ?Transform $transform = null): self
    {
        $transformId = isset($data['transformId']) && is_int($data['transformId'])
            ? $data['transformId']
            : null;

        $instanceId = isset($data['id']) && is_int($data['id'])
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
        $sceneObjectId = isset($data['sceneObjectId']) && is_string($data['sceneObjectId'])
            ? $data['sceneObjectId']
            : '';
        if ($sceneObjectId !== '') {
            $gameObject = self::findBySceneId($sceneObjectId);
            if ($gameObject !== null) {
                return $gameObject;
            }
        }

        $instanceId = isset($data['instanceId']) && is_int($data['instanceId'])
            ? $data['instanceId']
            : null;
        if ($instanceId !== null) {
            return self::lookupByInstanceId($instanceId);
        }

        return null;
    }

    /**
     * Registers a PHP wrapper class for a native component type.
     *
     * Most engine components are discovered by convention from their native
     * type, for example `Rigidbody2D` maps to `Lenga\Engine\Core\Rigidbody2D`.
     * This hook keeps non-conventional wrapper namespaces dynamic without
     * adding more branches to GameObject itself.
     *
     * @param class-string<Component> $componentClass
     */
    public static function registerComponentWrapper(string $nativeType, string $componentClass): void
    {
        $nativeType = \trim($nativeType);
        $componentClass = ltrim($componentClass, '\\');

        if ($nativeType === '') {
            throw new InvalidArgumentException('Component native type cannot be empty.');
        }

        if (
            !class_exists($componentClass) ||
            !is_subclass_of($componentClass, Component::class)
        ) {
            throw new InvalidArgumentException(
                "Component wrapper '{$componentClass}' must be a concrete subclass of Component.",
            );
        }

        try {
            $reflection = new ReflectionClass($componentClass);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException(
                "Component wrapper '{$componentClass}' could not be reflected.",
                previous: $exception,
            );
        }

        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException(
                "Component wrapper '{$componentClass}' must be concrete.",
            );
        }

        self::$registeredComponentWrapperClasses[$nativeType] = $componentClass;
        self::$registeredComponentNativeTypesByClass[$componentClass] = $nativeType;
    }

    public static function wrapNativeComponentLookupData(mixed $nativeResult): object|null
    {
        if ($nativeResult === false || $nativeResult === null) {
            return null;
        }

        if (is_object($nativeResult)) {
            return $nativeResult;
        }

        if (!is_array($nativeResult)) {
            return null;
        }

        $gameObject = null;
        if (is_array($nativeResult['gameObject'] ?? null)) {
            $gameObject = self::fromNativeLookupData($nativeResult['gameObject']);
        } elseif (isset($nativeResult['gameObjectId']) && is_int($nativeResult['gameObjectId'])) {
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
        if (!is_array($nativeResult)) {
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
            Component::class, ComponentInterface::class, 'Component' => ['nativeType' => 'Component', 'scriptClass' => null],
            Renderer::class, RendererInterface::class, 'Renderer' => ['nativeType' => 'Renderer', 'scriptClass' => null],
            Behaviour::class, 'Behaviour' => ['nativeType' => 'Behaviour', 'scriptClass' => null],
            default => self::normalizeDynamicComponentSpecifier($type),
        };
    }

    /**
     * @return array{nativeType: string, scriptClass: ?string}
     */
    private static function normalizeDynamicComponentSpecifier(string $type): array
    {
        if (class_exists($type) && is_subclass_of($type, Behaviour::class)) {
            return ['nativeType' => 'Behaviour', 'scriptClass' => $type];
        }

        if (class_exists($type) && is_subclass_of($type, Component::class)) {
            $nativeType = self::$registeredComponentNativeTypesByClass[$type]
                ?? self::resolveNativeComponentTypeFromClass($type);
            if ($nativeType !== null) {
                return ['nativeType' => $nativeType, 'scriptClass' => null];
            }
        }

        if (class_exists($type) && is_subclass_of($type, Renderer::class)) {
            return ['nativeType' => 'Renderer', 'scriptClass' => null];
        }

        if (class_exists($type) && is_subclass_of($type, Component::class)) {
            return ['nativeType' => 'Component', 'scriptClass' => null];
        }

        if (interface_exists($type)) {
            if (is_subclass_of($type, RendererInterface::class)) {
                return ['nativeType' => 'Renderer', 'scriptClass' => null];
            }

            if (is_subclass_of($type, ComponentInterface::class)) {
                return ['nativeType' => 'Component', 'scriptClass' => null];
            }
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
        if (!is_array($nativeResult)) {
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

        if (is_object($nativeResult)) {
            return $nativeResult;
        }

        if (!is_array($nativeResult)) {
            return null;
        }

        $componentId = isset($nativeResult['id']) && is_int($nativeResult['id'])
            ? $nativeResult['id']
            : null;
        $componentType = (string) ($nativeResult['type'] ?? 'Component');

        if ($componentType === 'Transform') {
            $transformId = isset($nativeResult['transformId']) && is_int($nativeResult['transformId'])
                ? $nativeResult['transformId']
                : null;
            $gameObjectId = isset($nativeResult['gameObjectId']) && is_int($nativeResult['gameObjectId'])
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

        $component = $this->createComponentWrapper($componentType, $componentId)
            ?? new NativeComponent($this, $componentId, $componentType);

        if (
            isset($nativeResult['sceneComponentId']) &&
            is_string($nativeResult['sceneComponentId']) &&
            $component instanceof Component
        ) {
            self::attachSceneComponentId($component, $nativeResult['sceneComponentId']);
        }

        return $component;
    }

    /**
     * @param class-string $type
     */
    private static function resolveNativeComponentTypeFromClass(string $type): ?string
    {
        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException) {
            return null;
        }

        if ($reflection->isAbstract()) {
            return null;
        }

        $nativeTypeConstant = $reflection->getReflectionConstant('NATIVE_TYPE');
        if ($nativeTypeConstant !== false && $nativeTypeConstant->isPublic()) {
            $nativeType = $nativeTypeConstant->getValue();
            if (is_string($nativeType) && $nativeType !== '') {
                return $nativeType;
            }
        }

        $namespace = $reflection->getNamespaceName();
        if ($namespace === __NAMESPACE__ || $namespace === 'Lenga\\Engine\\Audio') {
            return $reflection->getShortName();
        }

        return null;
    }

    private function createComponentWrapper(string $componentType, int $componentId): ?Component
    {
        $componentClass = $this->resolveComponentWrapperClass($componentType);
        if ($componentClass === null) {
            return null;
        }

        return $this->instantiateComponentWrapper($componentClass, $componentId, $componentType);
    }

    /**
     * @return class-string<Component>|null
     */
    private static function resolveComponentWrapperClass(string $componentType): ?string
    {
        if (isset(self::$registeredComponentWrapperClasses[$componentType])) {
            return self::$registeredComponentWrapperClasses[$componentType];
        }

        $candidateClasses = [
            __NAMESPACE__ . '\\' . $componentType,
            'Lenga\\Engine\\Audio\\' . $componentType,
        ];

        foreach ($candidateClasses as $candidateClass) {
            if (!class_exists($candidateClass) || !is_subclass_of($candidateClass, Component::class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($candidateClass);
            } catch (ReflectionException) {
                continue;
            }

            if (!$reflection->isAbstract()) {
                return $candidateClass;
            }
        }

        return null;
    }

    /**
     * @param class-string<Component> $componentClass
     */
    private function instantiateComponentWrapper(string $componentClass, int $componentId, string $componentType): ?Component
    {
        try {
            $reflection = new ReflectionClass($componentClass);
        } catch (ReflectionException) {
            return null;
        }

        $constructor = $reflection->getConstructor();
        $requiredParameters = $constructor?->getNumberOfRequiredParameters() ?? 0;
        $totalParameters = $constructor?->getNumberOfParameters() ?? 0;

        if ($requiredParameters <= 2 && $totalParameters >= 2) {
            return new $componentClass($this, $componentId);
        }

        if ($requiredParameters <= 3 && $totalParameters >= 3) {
            return new $componentClass($this, $componentId, $componentType);
        }

        return null;
    }

    private static function attachSceneComponentId(Component $component, string $sceneComponentId): void
    {
        $bound = Closure::bind(
            function () use ($sceneComponentId): void {
                $this->sceneComponentId = $sceneComponentId;
            },
            $component,
            Component::class,
        );

        if ($bound === null) {
            throw new RuntimeException('Failed to bind scene component id.');
        }

        $bound();
    }
}
