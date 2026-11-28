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

/**
 * An object in a scene: a player, a camera, a light, a wall or an empty group.
 *
 * Every GameObject has a {@see Transform} that places it in the world, and
 * holds components that give it behaviour, such as a {@see SpriteRenderer}, a
 * {@see Rigidbody2D} or your own {@see Behaviour} scripts. GameObjects can be
 * nested: moving a parent moves its children with it, while each child keeps
 * its own local position, rotation and scale relative to the parent.
 *
 * Inside a Behaviour, `$this->gameObject` is the object the script is
 * attached to. Use {@see GameObject::find()} and the other static methods to
 * reach other objects, {@see GameObject::create()} to add an empty object, and
 * {@see GameObject::instantiate()} to copy an object or spawn a prefab.
 *
 * A GameObject field on a Behaviour can also hold a prefab asset assigned in
 * the Inspector. Such a prefab reference has a
 * {@see GameObject::$prefabAssetPath} and no place in the scene; pass it to
 * {@see GameObject::instantiate()} to spawn it.
 *
 * @example examples/Core/GameObject.php Spawn projectiles from a prefab and find targets by tag
 */
final class GameObject
{
    /**
     * Creates a GameObject value that is not part of any scene.
     *
     * @internal The engine creates GameObjects. Scripts add objects to the
     *     scene with {@see GameObject::create()} or
     *     {@see GameObject::instantiate()}, and get existing ones with
     *     {@see GameObject::find()} and the related methods.
     */
    public function __construct(
        string $name,
        ?Transform $transform = null,
        bool $activeSelf = true,
        ?int $instanceId = null,
        string $sceneObjectId = '',
        string $tag = 'Untagged',
        int $layer = 0,
        string $prefabAssetPath = '',
    ) {
        $this->nameValue = $name;
        $this->instanceId = $instanceId;
        $this->sceneObjectIdValue = $sceneObjectId;
        $this->prefabAssetPathValue = $prefabAssetPath;
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
    private string $prefabAssetPathValue = '';
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

    /**
     * The name of this GameObject, as shown in the Hierarchy.
     *
     * Names do not have to be unique. Assigning a name renames the object in
     * the scene.
     */
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

    /**
     * The Transform that holds this GameObject's position, rotation, scale and
     * parent.
     */
    public Transform $transform {
        get {
            return $this->transformValue;
        }
    }

    /**
     * The tag of this GameObject, a label used to find and compare objects,
     * such as `Player` or `Enemy`.
     *
     * Use {@see GameObject::compareTag()} to check it and
     * {@see GameObject::findGameObjectsWithTag()} to find objects by tag.
     * Defaults to `Untagged`.
     */
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

    /**
     * The id this GameObject has in its scene file, such as `main-camera`.
     *
     * The id stays the same each time the scene loads, so it can identify an
     * object across runs. Find an object by it with
     * {@see GameObject::findBySceneId()}. Empty when the object has no scene
     * id. Read-only.
     */
    public string $sceneObjectId {
        get {
            return $this->sceneObjectIdValue;
        }
    }

    /**
     * The project-relative prefab asset path when this GameObject is a prefab
     * reference, such as one assigned to a GameObject field in the Inspector.
     *
     * Objects in the scene return an empty string.
     */
    public string $prefabAssetPath {
        get {
            return $this->prefabAssetPathValue;
        }
    }

    /**
     * The layer number of this GameObject, from 0 to 31.
     *
     * Physics queries and other systems filter objects with a layer mask that
     * includes or leaves out each layer; build one with
     * {@see Physics2D::layerMask()}. Defaults to `0`.
     */
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

    /**
     * Whether this GameObject itself is switched on, ignoring its parents.
     *
     * An object can be active itself and still be inactive in the scene when
     * a parent is switched off; read {@see GameObject::$activeInHierarchy} for
     * that. Read-only; change it with {@see GameObject::setActive()}.
     */
    public bool $activeSelf {
        get {
            if ($this->instanceId !== null) {
                $this->activeSelfValue = NativeEngine::call('game_object_get_active_self', $this->instanceId);
            }

            return $this->activeSelfValue;
        }
    }

    /**
     * Whether this GameObject is active in the scene: it and all of its
     * parents are switched on.
     *
     * Only objects active in the hierarchy are updated and drawn. Read-only.
     */
    public bool $activeInHierarchy {
        get {
            if ($this->instanceId !== null) {
                $this->activeInHierarchyValue = NativeEngine::call('game_object_get_active_in_hierarchy', $this->instanceId);
            }

            return $this->activeInHierarchyValue;
        }
    }

    /**
     * Returns the id the engine gives this GameObject while the scene runs.
     *
     * The id is unique among the objects in play, which makes it a useful key
     * for remembering objects during a session. Use {@see GameObject::$sceneObjectId}
     * for an id that stays the same across runs.
     *
     * @return int|null The id, or `null` for a prefab reference or another object that is not in the scene.
     */
    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function __serialize(): array
    {
        if ($this->prefabAssetPathValue !== '') {
            return [
                '__lengaRefKind' => 'PrefabAsset',
                'assetPath' => $this->prefabAssetPathValue,
                'name' => $this->nameValue,
            ];
        }

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
            $this->prefabAssetPathValue = $resolved->prefabAssetPathValue;
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
        $this->prefabAssetPathValue = isset($data['assetPath']) && is_string($data['assetPath'])
            ? $data['assetPath']
            : '';
        $this->nameValue = isset($data['name']) && is_string($data['name']) ? $data['name'] : 'GameObject';
        $this->tagValue = 'Untagged';
        $this->layerValue = 0;
        $this->activeSelfValue = true;
        $this->activeInHierarchyValue = true;
        $this->transformValue = new Transform(null, null, null, null, null, $this->instanceId);
        $this->attachTransformToSelf($this->instanceId);
    }

    /**
     * Switches this GameObject on or off.
     *
     * An inactive object and its children stop updating and drawing until the
     * object is switched on again. Its components and settings are kept.
     *
     * @param bool $value `true` to switch the object on, `false` to switch it off.
     */
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
        if ($this->prefabAssetPathValue !== '') {
            return;
        }

        NativeEngine::call('game_object_set_active', $this->nameValue, $value);
    }

    /**
     * Returns whether this GameObject itself is switched on. Same as reading
     * {@see GameObject::$activeSelf}.
     */
    public function isActiveSelf(): bool
    {
        return $this->activeSelf;
    }

    /**
     * Returns whether this GameObject and all of its parents are switched on.
     * Same as reading {@see GameObject::$activeInHierarchy}.
     */
    public function isActiveInHierarchy(): bool
    {
        return $this->activeInHierarchy;
    }

    /**
     * Returns whether this GameObject has the given tag.
     *
     * @param string $tag The tag to compare with, such as `Player`.
     */
    public function compareTag(string $tag): bool
    {
        return $this->tag === $tag;
    }

    /**
     * Returns the scene this GameObject belongs to.
     *
     * A prefab reference or another object that is not in a scene returns the
     * active scene.
     *
     * @return Scene|null The scene, or `null` when it cannot be found.
     */
    public function getScene(): ?Scene
    {
        if ($this->instanceId === null) {
            return Scene::getActive();
        }

        /** @var array{name?: string}|false $data */
        $data = NativeEngine::call('game_object_get_scene_by_id', $this->instanceId);

        return is_array($data) ? Scene::fromNativeData($data) : null;
    }

    /**
     * Returns the parent of this GameObject.
     *
     * @return GameObject|null The parent, or `null` when the object is at the scene root or not in the scene.
     */
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
     * Returns the direct children of this GameObject.
     *
     * Children of children are not included.
     *
     * @return list<GameObject> The children, or an empty list.
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

    /**
     * Returns how many direct children this GameObject has.
     */
    public function childCount(): int
    {
        return count($this->getChildren());
    }

    /**
     * Moves this GameObject under another GameObject, or back to the scene root.
     *
     * When `$worldPositionStays` is `true`, the object keeps its position,
     * rotation and scale in the world and its local transform is recalculated
     * for the new parent. When it is `false`, the object keeps its local
     * transform, so it moves with the new parent. Moving an object during a
     * frame callback does not run that callback for it a second time.
     *
     * @param GameObject|null $parent The new parent, or `null` to move the object to the scene root.
     * @param bool $worldPositionStays Whether to keep the object's world placement.
     * @return bool `true` when the object was moved; `false` when it is not in the scene, or the parent is in another scene or is being destroyed.
     */
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

    /**
     * Removes this GameObject and its children from the scene.
     *
     * The removal is queued: the current callback finishes, and the queued
     * objects receive no further update or physics callbacks. Lenga calls
     * {@see Behaviour::onDestroy()} on their behaviours during cleanup. A
     * destroy requested from `onDestroy()` goes into the next cleanup batch.
     * Calling it on a prefab reference does nothing.
     */
    public function destroy(): void
    {
        if ($this->instanceId === null) {
            return;
        }

        NativeEngine::call('game_object_destroy_by_id', $this->instanceId);
    }

    /**
     * Returns whether this GameObject has a component of the given type.
     *
     * @param string $type A component class, such as `Rigidbody2D::class`, or a component type name.
     */
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
     * Looks for a component of the given type and stores it in `$component`.
     *
     * Use it to get a component and check for it in one step:
     *
     * ```php
     * if ($this->gameObject->tryGetComponent(Rigidbody2D::class, $body)) {
     *     $body->addForce(new Vector2(0.0, 5.0));
     * }
     * ```
     *
     * Pass a component class, such as `Rigidbody2D::class`, so editors and
     * static analysers know the type of `$component`.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type A component class or a component type name.
     * @param TComponent|null $component Receives the component, or `null` when there is none.
     * @param-out TComponent|null $component
     * @return bool `true` when the component was found.
     */
    public function tryGetComponent(string $type, ?object &$component = null): bool
    {
        $component = $this->getComponent($type);

        return $component !== null;
    }

    /**
     * Returns the first component of the given type on this GameObject.
     *
     * Pass a component class, for example `Rigidbody2D::class`, so editors can
     * tell the result is a `Rigidbody2D|null`. A component type name such as
     * `"Rigidbody2D"` also works, for code that picks the type at run time.
     * Passing a base class, such as `Renderer::class` or `Behaviour::class`,
     * finds the first component of any subclass.
     *
     * ```php
     * $body = $this->gameObject->getComponent(Rigidbody2D::class);
     * ```
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type A component class or a component type name.
     * @return TComponent|null The component, or `null` when this GameObject has none of that type.
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
     * Returns every component of the given type on this GameObject.
     *
     * Pass a component class, such as `AudioSource::class`, for clear return
     * types and better editor support.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type A component class or a component type name, or `null` for every component.
     * @return ($type is null ? list<object> : list<TComponent>) The components in the order they are attached, or an empty list.
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
     * Returns every component of the given type on this GameObject and all of
     * its children, grandchildren and so on.
     *
     * Objects are searched depth first, starting with this one. Inactive
     * objects and everything under them are skipped unless
     * `$includeInactive` is `true`.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type A component class or a component type name, or `null` for every component.
     * @param bool $includeInactive Whether to search inactive objects too.
     * @return ($type is null ? list<object> : list<TComponent>) The components found, or an empty list.
     */
    public function getComponentsInChildren(?string $type = null, bool $includeInactive = false): array
    {
        $results = [];
        $this->collectComponentsInChildren($type, $includeInactive, $results);

        return $results;
    }

    /**
     * Returns the first component of the given type on this GameObject or any
     * object below it.
     *
     * The search order is the same as {@see GameObject::getComponentsInChildren()}.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type A component class or a component type name.
     * @param bool $includeInactive Whether to search inactive objects too.
     * @return TComponent|null The component, or `null` when none is found.
     */
    public function getComponentInChildren(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInChildren($type, $includeInactive);

        return $components[0] ?? null;
    }

    /**
     * Returns every component of the given type on this GameObject, its parent,
     * its parent's parent and so on up to the scene root.
     *
     * Results start with this object. Objects that are inactive in the
     * hierarchy are skipped unless `$includeInactive` is `true`; the search
     * still continues through them to their parents.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string|null $type A component class or a component type name, or `null` for every component.
     * @param bool $includeInactive Whether to include components on inactive objects.
     * @return ($type is null ? list<object> : list<TComponent>) The components found, or an empty list.
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
     * Returns the first component of the given type on this GameObject or one
     * of its parents, searching upwards from this object.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type A component class or a component type name.
     * @param bool $includeInactive Whether to include components on inactive objects.
     * @return TComponent|null The component, or `null` when none is found.
     */
    public function getComponentInParent(string $type, bool $includeInactive = false): object|null
    {
        $components = $this->getComponentsInParent($type, $includeInactive);

        return $components[0] ?? null;
    }

    /**
     * Adds a new component of the given type to this GameObject and returns it.
     *
     * ```php
     * $body = $this->gameObject->addComponent(Rigidbody2D::class);
     * $health = $this->gameObject->addComponent(Health::class);
     * ```
     *
     * To add one of your own scripts, pass its class, such as
     * `PlayerController::class`.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type A component class or a component type name.
     * @return TComponent The new component.
     * @throws RuntimeException When this GameObject is not in the scene, such as a prefab reference, or the component could not be added.
     * @throws InvalidArgumentException When `$type` is `Behaviour` itself instead of a script class.
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

    /**
     * Creates a copy of this GameObject, with its children and components, in
     * the scene.
     *
     * Same as `GameObject::instantiate($this, $name)`.
     *
     * @param string|null $name The name of the copy, or `null` to keep the engine's default name.
     * @return GameObject The copy.
     * @throws RuntimeException When the copy could not be made.
     */
    public function clone(?string $name = null): self
    {
        $clone = self::instantiate($this, $name);
        if (!$clone instanceof self) {
            throw new RuntimeException('GameObject clone did not return a GameObject.');
        }

        return $clone;
    }

    /**
     * Finds a GameObject in the active scene by name.
     *
     * When several objects share the name, one of them is returned. Look the
     * object up once, for example in {@see Behaviour::start()}, and keep the
     * result rather than calling this every frame.
     *
     * @param string $name The name to look for.
     * @return GameObject|null The object, or `null` when none has that name.
     */
    public static function find(string $name): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('game_object_find_by_name', $name);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * Finds a GameObject in the active scene by its scene id.
     *
     * @param string $sceneObjectId The id the object has in its scene file. See {@see GameObject::$sceneObjectId}.
     * @return GameObject|null The object, or `null` when none has that id.
     */
    public static function findBySceneId(string $sceneObjectId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = NativeEngine::call('game_object_find_by_scene_id', $sceneObjectId);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * Finds a GameObject in the active scene by the id returned from
     * {@see GameObject::getInstanceId()}.
     *
     * @param int $instanceId The id of the object.
     * @return GameObject|null The object, or `null` when no object has that id.
     */
    public static function lookupByInstanceId(int $instanceId): ?self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false $data */
        $data = NativeEngine::call('game_object_lookup_by_id', $instanceId);
        if (!is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * Finds one GameObject in the active scene that has the given tag.
     *
     * @param string $tag The tag to look for, such as `Player`.
     * @return GameObject|null The object, or `null` when none has that tag.
     */
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
     * Finds every GameObject in the active scene that has the given tag.
     *
     * @param string $tag The tag to look for, such as `Enemy`.
     * @return list<GameObject> The objects, or an empty list.
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

    /**
     * Creates an empty GameObject in the active scene.
     *
     * The new object has only a {@see Transform}. Add components to it with
     * {@see GameObject::addComponent()}.
     *
     * @param string $name The name of the new object.
     * @return GameObject The new object.
     * @throws RuntimeException When the object could not be created.
     */
    public static function create(string $name): self
    {
        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('scene_create_game_object', $name);
        if (!is_array($data)) {
            throw new RuntimeException("Failed to create GameObject '{$name}' in the active scene.");
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * Creates a copy of a GameObject, or spawns a prefab, in the scene.
     *
     * The source can be a GameObject in the scene, a prefab reference from a
     * GameObject field, or a component. For a component, Lenga copies the
     * GameObject it is on and returns the matching component of the copy.
     *
     * The copy is in the scene immediately. Its behaviours receive
     * {@see Behaviour::start()} before their first fixed, regular or late
     * update.
     *
     * The arguments after the source take several forms:
     *
     * ```php
     * $copy = GameObject::instantiate($source);
     * $copy = GameObject::instantiate($source, 'Display Name');
     * $copy = GameObject::instantiate($source, $position, $rotation);
     * $copy = GameObject::instantiate($source, $position, $rotation, $parent);
     * $copy = GameObject::instantiate($source, $parent, true); // keep world placement
     * $copy = GameObject::instantiate($source, InstantiateOptions::at($position, name: 'Hit Spark'));
     * ```
     *
     * Positions and rotations are in world space. A Vector3 rotation is read
     * as Euler angles in degrees.
     *
     * @param object $original The GameObject, prefab reference, Transform, component or Behaviour to copy.
     * @param string|Vector3|Transform|GameObject|InstantiateOptions|array<string, mixed>|null $positionOrParentOrName
     *     The name of the copy; its world position; its parent; an
     *     {@see InstantiateOptions}; or an array of options as
     *     {@see InstantiateOptions::fromArray()} reads them.
     * @param Vector3|Quaternion|bool|string|null $rotationOrWorldPositionStaysOrName
     *     After a position: the world rotation, or the name. After a parent:
     *     whether the copy keeps its world placement (default `false`), or the name.
     * @param Transform|GameObject|string|null $parentOrName After a position and rotation: the parent, or the name.
     * @param string|null $name The name of the copy. It overrides a name given in the earlier arguments.
     * @return object The copy: a GameObject for a GameObject or prefab source, otherwise the matching component of the copy.
     * @throws InvalidArgumentException When the source or the combination of arguments is not supported.
     * @throws RuntimeException When the source is not in the scene and is not a prefab reference, or the copy could not be made.
     */
    public static function instantiate(
        object $original,
        string|Vector3|Transform|GameObject|InstantiateOptions|array|null $positionOrParentOrName = null,
        Vector3|Quaternion|bool|string|null $rotationOrWorldPositionStaysOrName = null,
        Transform|GameObject|string|null $parentOrName = null,
        ?string $name = null,
    ): object {
        $options = self::resolveInstantiateOptions(
            $positionOrParentOrName,
            $rotationOrWorldPositionStaysOrName,
            $parentOrName,
            $name,
        );

        if ($original instanceof self) {
            return self::instantiateGameObjectSource($original, $options);
        }

        if ($original instanceof Transform || $original instanceof Component || $original instanceof Behaviour) {
            return self::instantiateComponentSource($original, $options);
        }

        throw new InvalidArgumentException(
            'GameObject::instantiate() expects a GameObject, Transform, Component, or Behaviour source.',
        );
    }

    private static function instantiateGameObjectSource(self $original, InstantiateOptions $options): self
    {
        $instanceId = $original->instanceId;
        if ($instanceId === null) {
            if ($original->prefabAssetPathValue !== '') {
                return Prefab::instantiate($original->prefabAssetPathValue, $options);
            }

            throw new RuntimeException('Cannot instantiate a detached GameObject proxy.');
        }

        /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
        $data = NativeEngine::call('game_object_instantiate_by_id', $instanceId, $options->toNativeArray());
        if (!is_array($data)) {
            throw new RuntimeException("Failed to instantiate GameObject '{$original->name}'.");
        }

        return self::fromNativeLookupData($data);
    }

    private static function instantiateComponentSource(
        Transform|Component|Behaviour $original,
        InstantiateOptions $options,
    ): object {
        $sourceGameObject = $original->gameObject;
        $instance = self::instantiateGameObjectSource($sourceGameObject, $options);
        $component = self::resolveInstantiatedComponent($original, $instance);
        if ($component === null) {
            throw new RuntimeException(
                "Failed to resolve instantiated component from cloned GameObject '{$instance->name}'.",
            );
        }

        return $component;
    }

    private static function resolveInstantiateOptions(
        mixed $positionOrParentOrName,
        mixed $rotationOrWorldPositionStaysOrName,
        mixed $parentOrName,
        ?string $name,
    ): InstantiateOptions {
        if ($positionOrParentOrName instanceof InstantiateOptions) {
            return $name !== null ? $positionOrParentOrName->withName($name) : $positionOrParentOrName;
        }

        if (is_array($positionOrParentOrName)) {
            $options = InstantiateOptions::fromArray($positionOrParentOrName);
            return $name !== null ? $options->withName($name) : $options;
        }

        if ($positionOrParentOrName instanceof Vector3) {
            $rotation = null;
            $resolvedName = $name;

            if ($rotationOrWorldPositionStaysOrName instanceof Vector3 || $rotationOrWorldPositionStaysOrName instanceof Quaternion) {
                $rotation = $rotationOrWorldPositionStaysOrName;
            } elseif (is_string($rotationOrWorldPositionStaysOrName)) {
                $resolvedName = $rotationOrWorldPositionStaysOrName;
            } elseif ($rotationOrWorldPositionStaysOrName !== null) {
                throw new InvalidArgumentException('Instantiate rotation must be a Vector3, Quaternion, string name, or null.');
            }

            $parent = null;
            if ($parentOrName instanceof Transform || $parentOrName instanceof self) {
                $parent = $parentOrName;
            } elseif (is_string($parentOrName)) {
                $resolvedName = $parentOrName;
            } elseif ($parentOrName !== null) {
                throw new InvalidArgumentException('Instantiate parent must be a GameObject, Transform, string name, or null.');
            }

            return InstantiateOptions::at(
                $positionOrParentOrName,
                $rotation,
                $parent,
                $resolvedName,
            );
        }

        if ($positionOrParentOrName instanceof Transform || $positionOrParentOrName instanceof self) {
            $worldPositionStays = false;
            $resolvedName = $name;

            if (is_bool($rotationOrWorldPositionStaysOrName)) {
                $worldPositionStays = $rotationOrWorldPositionStaysOrName;
            } elseif (is_string($rotationOrWorldPositionStaysOrName)) {
                $resolvedName = $rotationOrWorldPositionStaysOrName;
            } elseif ($rotationOrWorldPositionStaysOrName !== null) {
                throw new InvalidArgumentException('Instantiate parent overload expects a bool worldPositionStays, string name, or null.');
            }

            if (is_string($parentOrName)) {
                $resolvedName = $parentOrName;
            } elseif ($parentOrName !== null) {
                throw new InvalidArgumentException('Instantiate parent overload does not accept a fourth argument except a string name.');
            }

            return InstantiateOptions::under($positionOrParentOrName, $worldPositionStays, $resolvedName);
        }

        if (is_string($positionOrParentOrName) || $positionOrParentOrName === null) {
            if ($rotationOrWorldPositionStaysOrName !== null || $parentOrName !== null) {
                throw new InvalidArgumentException('Instantiate name overload only accepts a source and optional name.');
            }

            return new InstantiateOptions(name: $positionOrParentOrName ?? $name);
        }

        throw new InvalidArgumentException('Unsupported GameObject::instantiate() argument combination.');
    }

    private static function resolveInstantiatedComponent(
        Transform|Component|Behaviour $source,
        self $instance,
    ): object|null {
        if ($source instanceof Transform) {
            return $instance->transform;
        }

        if ($source instanceof Behaviour) {
            $sceneComponentId = $source->getSceneComponentId();
            if ($sceneComponentId !== null && $sceneComponentId !== '') {
                foreach ($instance->getComponents($source::class) as $candidate) {
                    if (
                        $candidate instanceof Behaviour &&
                        $candidate->getSceneComponentId() === $sceneComponentId
                    ) {
                        return $candidate;
                    }
                }
            }

            return $instance->getComponent($source::class);
        }

        $componentType = $source->type;
        $sceneComponentId = $source->getSceneComponentId();
        if ($sceneComponentId !== null && $sceneComponentId !== '') {
            foreach ($instance->getComponents($componentType) as $candidate) {
                if (
                    $candidate instanceof Component &&
                    $candidate->getSceneComponentId() === $sceneComponentId
                ) {
                    return $candidate;
                }
            }
        }

        return $instance->getComponent($componentType);
    }

    /**
     * Creates a prefab reference from a project-relative prefab asset path.
     *
     * Most scripts receive prefab references through GameObject fields set in
     * the Inspector. Use this to choose a prefab in code, then pass the
     * reference to {@see GameObject::instantiate()}.
     *
     * @param string $assetPath The project-relative path of the prefab, such as `Assets/Prefabs/Bullet.prefab.json`.
     * @param string|null $name The name of the reference. Defaults to the file name without `.prefab.json`.
     * @return GameObject A prefab reference that is not part of the scene.
     * @throws InvalidArgumentException When the path is empty.
     */
    public static function fromPrefabAssetPath(string $assetPath, ?string $name = null): self
    {
        $assetPath = \trim($assetPath);
        if ($assetPath === '') {
            throw new InvalidArgumentException('Prefab asset path cannot be empty.');
        }

        $displayName = $name;
        if ($displayName === null || $displayName === '') {
            $displayName = \basename($assetPath);
            if (\str_ends_with($displayName, '.prefab.json')) {
                $displayName = \substr($displayName, 0, -\strlen('.prefab.json'));
            }
        }

        return new self(
            $displayName !== '' ? $displayName : 'Prefab',
            prefabAssetPath: $assetPath,
        );
    }

    /**
     * Creates a GameObject from the lookup data the engine returns for it.
     *
     * @internal Engine plumbing used by the lookup methods and by other engine
     *     classes that receive objects from the runtime.
     *
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

    /**
     * Resolves serialized GameObject and prefab asset references.
     *
     * @internal Used when saved field values are restored.
     *
     * @param array<string, mixed> $data
     */
    public static function fromSerializedReference(array $data): ?self
    {
        $referenceKind = isset($data['__lengaRefKind']) && is_string($data['__lengaRefKind'])
            ? $data['__lengaRefKind']
            : '';
        $assetPath = isset($data['assetPath']) && is_string($data['assetPath'])
            ? $data['assetPath']
            : '';
        if ($referenceKind === 'PrefabAsset' && $assetPath !== '') {
            return self::fromPrefabAssetPath(
                $assetPath,
                isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            );
        }

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
     * Resolves a serialized component reference whose owner may be a prefab asset.
     *
     * Prefab component references are detached authoring-time proxies. Use their
     * `gameObject` to instantiate the prefab source before calling runtime-only
     * component methods.
     *
     * @internal Used when saved field values are restored.
     *
     * @param array<string, mixed> $data
     */
    public static function componentFromSerializedReference(array $data): object|null
    {
        $referenceKind = isset($data['__lengaRefKind']) && is_string($data['__lengaRefKind'])
            ? $data['__lengaRefKind']
            : '';
        if (!\in_array($referenceKind, ['Component', 'Behaviour', 'Transform'], true)) {
            return null;
        }

        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = self::fromSerializedReference($gameObjectData);
        if (!$gameObject instanceof self) {
            return null;
        }

        if ($gameObject->prefabAssetPathValue === '') {
            if ($referenceKind === 'Transform') {
                return $gameObject->transform;
            }

            $componentType = isset($data['componentType']) && is_string($data['componentType'])
                ? $data['componentType']
                : (isset($data['className']) && is_string($data['className']) ? $data['className'] : '');
            return $componentType !== '' ? $gameObject->getComponent($componentType) : null;
        }

        return $gameObject->createPrefabComponentReference($data);
    }

    /**
     * Tells Lenga which PHP class represents a component type.
     *
     * Lenga finds the class of most engine components by name: the
     * `Rigidbody2D` type maps to `Lenga\Engine\Core\Rigidbody2D`. Packages
     * that put a component class in another namespace register it here, so
     * {@see GameObject::getComponent()} and the other lookup methods return
     * that class.
     *
     * @param string $nativeType The component type name, such as `Rigidbody2D`.
     * @param class-string<Component> $componentClass A concrete subclass of {@see Component}.
     * @throws InvalidArgumentException When the type name is empty or the class is not a concrete Component subclass.
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

    /**
     * Creates a component object from the lookup data the engine returns for it.
     *
     * @internal Engine plumbing used by engine classes that receive components from the runtime.
     */
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
     * Creates component objects from a list of lookup data the engine returns.
     *
     * @internal Engine plumbing used by engine classes that receive components from the runtime.
     *
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
     * @param array<string, mixed> $data
     */
    private function createPrefabComponentReference(array $data): object|null
    {
        $referenceKind = isset($data['__lengaRefKind']) && is_string($data['__lengaRefKind'])
            ? $data['__lengaRefKind']
            : '';
        if ($referenceKind === 'Transform') {
            return $this->transformValue;
        }

        $componentId = isset($data['instanceId']) && is_int($data['instanceId'])
            ? $data['instanceId']
            : 0;
        $componentSceneId = isset($data['componentSceneId']) && is_string($data['componentSceneId'])
            ? $data['componentSceneId']
            : null;

        if ($referenceKind === 'Behaviour') {
            $className = isset($data['className']) && is_string($data['className'])
                ? ltrim($data['className'], '\\')
                : '';
            if ($className === '' || !class_exists($className) || !is_subclass_of($className, Behaviour::class)) {
                return null;
            }

            /** @var Behaviour $behaviour */
            $behaviour = new $className();
            self::attachPrefabBehaviourReference($behaviour, $this, $componentId, $componentSceneId);
            return $behaviour;
        }

        $componentType = isset($data['componentType']) && is_string($data['componentType'])
            ? $data['componentType']
            : '';
        if ($componentType === '' || $componentType === 'Transform' || $componentType === Transform::class) {
            return $this->transformValue;
        }

        $descriptor = self::normalizeComponentSpecifier($componentType);
        $nativeType = $descriptor['nativeType'];
        $component = $this->createComponentWrapper($nativeType, $componentId)
            ?? new NativeComponent($this, $componentId, $nativeType);
        if ($componentSceneId !== null && $componentSceneId !== '') {
            self::attachSceneComponentId($component, $componentSceneId);
        }

        return $component;
    }

    private static function attachPrefabBehaviourReference(
        Behaviour $behaviour,
        self $gameObject,
        int $componentId,
        ?string $sceneComponentId,
    ): void {
        $bound = Closure::bind(
            function () use ($gameObject, $componentId, $sceneComponentId): void {
                $this->gameObjectValue = $gameObject;
                $this->componentId = $componentId;
                $this->sceneComponentId = $sceneComponentId;
            },
            $behaviour,
            Behaviour::class,
        );

        if ($bound === null) {
            throw new RuntimeException('Failed to bind prefab Behaviour reference.');
        }

        $bound();
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
