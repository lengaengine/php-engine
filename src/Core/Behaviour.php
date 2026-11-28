<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use BackedEnum;
use Generator;
use Lenga\Engine\Attributes\HideInInspector;
use Lenga\Engine\Attributes\ListOf;
use Lenga\Engine\Attributes\RequireComponent;
use Lenga\Engine\Attributes\SerializeReference;
use Lenga\Engine\Audio\AudioClip;
use Lenga\Engine\Audio\AudioMixer;
use Lenga\Engine\Interfaces\ComponentInterface;
use Lenga\Engine\Interfaces\RendererInterface;
use Lenga\Engine\Internal\ScriptExceptions;
use Lenga\Engine\UI\Canvas;
use Lenga\Engine\UI\UIElement;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use Throwable;
use function class_exists;
use function debug_backtrace;
use function enum_exists;
use function interface_exists;
use function is_a;
use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function property_exists;
use function spl_object_id;
use const DEBUG_BACKTRACE_IGNORE_ARGS;

/**
 * Base class for the game scripts you attach to GameObjects.
 *
 * Extend Behaviour when code belongs to something in the scene: it reads
 * input, moves its GameObject, talks to other components, reacts to
 * collisions or exposes values in the Inspector. Public fields of your
 * class appear in the Inspector, so you can tune them per GameObject
 * without editing code. Keep logic that does not belong to a GameObject in
 * ordinary PHP classes.
 *
 * Add a script to a GameObject in the editor, or from code with
 * {@see GameObject::addComponent()}. The engine creates the instance; do not
 * create Behaviours with `new`.
 *
 * ## Lifecycle
 *
 * Override only the methods you need. The engine calls them in this order:
 *
 * 1. {@see Behaviour::awake()} once, the first time the GameObject is active
 *    in the hierarchy. Inspector values are already applied. Use it to look
 *    up components and set up state.
 * 2. {@see Behaviour::onEnable()} each time the behaviour becomes enabled on
 *    an active GameObject, starting right after `awake()`.
 * 3. {@see Behaviour::start()} once, at the beginning of the first frame in
 *    which the behaviour is enabled and active, before that frame's
 *    `fixedUpdate()`. Every `awake()` in the scene has run by then.
 * 4. Each frame:
 *    - {@see Behaviour::fixedUpdate()} zero or more times, once per fixed
 *      physics step, each call followed by a physics step;
 *    - {@see Behaviour::update()} once;
 *    - {@see Behaviour::lateUpdate()} once, after every `update()`.
 * 5. {@see Behaviour::onDisable()} each time the behaviour is disabled or its
 *    GameObject becomes inactive.
 * 6. {@see Behaviour::onDestroy()} once, when the behaviour or its GameObject
 *    is destroyed. `onDisable()` runs first if the behaviour was enabled.
 *
 * Per-frame methods run only while the behaviour is enabled and its
 * GameObject is active. While the game is paused, `fixedUpdate()` and
 * physics stop, and `update()` and `lateUpdate()` keep running with
 * {@see Time::deltaTime()} at zero.
 *
 * The engine also calls collision and trigger methods when your class
 * defines them: `onCollisionEnter2D(Collision2D $collision)`,
 * `onTriggerEnter2D(...)` and their `Stay` and `Exit` forms for 2D physics,
 * and `onCollisionEnter(Collision3D $collision)`, `onTriggerEnter(...)` and
 * their `Stay` and `Exit` forms for 3D physics.
 *
 * Behaviours also run coroutines ({@see Behaviour::startCoroutine()}) and
 * send and receive gameplay events ({@see Behaviour::emitEvent()},
 * {@see Behaviour::onEvent()}), cleaning both up when the behaviour is
 * destroyed.
 *
 * When one of these methods throws an exception it does not catch, the
 * engine logs the exception with its trace to the Console and the game
 * carries on. The method is called again at its next turn, for example
 * `update()` on the next frame. An `awake()` or `start()` that throws
 * counts as done, and the behaviour goes on to receive its per-frame
 * methods.
 *
 * @example examples/Core/Behaviour.php Move a GameObject with the arrow keys
 */
abstract class Behaviour implements ComponentInterface
{
    private ?GameObject $gameObjectValue = null;
    private ?int $componentId = null;
    private ?string $sceneComponentId = null;
    /**
     * @var array<int, Coroutine>
     */
    private array $coroutines = [];
    /**
     * @var array<int, Signal>
     */
    private array $ownedSignals = [];
    /**
     * @var array<int, SignalSubscription>
     */
    private array $ownedSubscriptionsOnDisable = [];
    /**
     * @var array<int, SignalSubscription>
     */
    private array $ownedSubscriptionsUntilDestroy = [];
    /**
     * @var array<string, SignalSubscription>
     */
    private static array $eventSubscriptionsByOwnerCallsite = [];
    private bool $lifecycleEnabled = false;

    /**
     * Creates the behaviour instance.
     *
     * @internal The engine creates Behaviours when it adds them to a GameObject.
     *     Add one with {@see GameObject::addComponent()}, and put setup code in
     *     {@see Behaviour::awake()}.
     */
    public function __construct() {}

    // ---------------------------------------------------------------------
    // Owning GameObject
    // ---------------------------------------------------------------------

    /**
     * The GameObject this behaviour is attached to.
     *
     * The engine attaches the GameObject before it calls
     * {@see Behaviour::awake()}, so the property is ready in every lifecycle
     * method.
     */
    #[HideInInspector]
    public GameObject $gameObject {
        get {
            if ($this->gameObjectValue === null) {
                // Temporary fallback
                $this->gameObjectValue = new GameObject(static::class);
            }

            return $this->gameObjectValue;
        }
    }

    // ---------------------------------------------------------------------
    // Owning Transform
    // (always reflects this behaviour's GameObject)
    // ---------------------------------------------------------------------

    /**
     * The Transform of this behaviour's GameObject: its position, rotation and scale.
     *
     * Same as `$this->gameObject->transform`.
     */
    #[HideInInspector]
    public Transform $transform {
        get {
            return $this->gameObject->transform;
        }
    }

    private function ensureRequiredComponents(GameObject $gameObject): void
    {
        static $resolving = [];

        $reflection = new ReflectionObject($this);
        foreach ($reflection->getAttributes(RequireComponent::class) as $attribute) {
            $required = $attribute->newInstance();
            foreach ($required->componentTypes as $componentType) {
                if (!is_string($componentType) || $componentType === '') {
                    $message = $this->formatRequireComponentDiagnostic(
                        $gameObject,
                        '<invalid>',
                        false,
                        'Dependency metadata is invalid.',
                    );
                    Debug::error($message);
                    throw new RuntimeException($message);
                }

                if ($componentType === self::class || $componentType === static::class) {
                    continue;
                }

                if ($gameObject->hasComponent($componentType)) {
                    continue;
                }

                $gameObjectId = $gameObject->getInstanceId() ?? spl_object_id($gameObject);
                $resolutionKey = $gameObjectId . '|' . $componentType;
                if (isset($resolving[$resolutionKey])) {
                    continue;
                }

                $resolving[$resolutionKey] = true;
                try {
                    $gameObject->addComponent($componentType);
                } catch (Throwable $exception) {
                    $message = $this->formatRequireComponentDiagnostic(
                        $gameObject,
                        $componentType,
                        true,
                        $exception->getMessage(),
                    );
                    Debug::error($message);
                    throw new RuntimeException($message, previous: $exception);
                } finally {
                    unset($resolving[$resolutionKey]);
                }
            }
        }
    }

    private function formatRequireComponentDiagnostic(
        GameObject $gameObject,
        string $componentType,
        bool $attemptedAutoAdd,
        string $reason,
    ): string {
        return "RequireComponent dependency resolution failed during component attachment: "
            . "behaviour='" . static::class . "', "
            . "gameObject='{$gameObject->name}', "
            . "requiredComponent='{$componentType}', "
            . 'attemptedAutoAdd=' . ($attemptedAutoAdd ? 'true' : 'false') . ', '
            . "reason='{$reason}'";
    }

    /**
     * Whether the behaviour is switched on.
     *
     * Setting it to false stops the per-frame methods and its coroutines and
     * makes the engine call {@see Behaviour::onDisable()}. Setting it back to
     * true calls {@see Behaviour::onEnable()} and resumes them. A behaviour
     * that is not attached to a GameObject reads as enabled, and setting the
     * property on it has no effect.
     */
    #[HideInInspector]
    public bool $enabled {
        get {
            if ($this->componentId === null) {
                return true;
            }

            return NativeEngine::call('component_get_enabled', $this->componentId);
        }

        set(bool $value) {
            if ($this->componentId === null) {
                return;
            }

            NativeEngine::call('component_set_enabled', $this->componentId, $value);
        }
    }

    /**
     * Returns the engine's ID for this behaviour.
     *
     * The ID is unique among the components in the running game, so you can
     * use it to tell instances apart or as an array key. It can change
     * between play sessions, so do not save it.
     *
     * @return int|null The instance ID, or null when the behaviour is not attached to a GameObject.
     */
    public function getInstanceId(): ?int
    {
        return $this->componentId;
    }

    /**
     * Returns the ID this behaviour has in the saved scene file.
     *
     * @internal Engine bookkeeping used to resolve saved component references.
     *
     * @return string|null The scene ID, or null for a behaviour that has none.
     */
    public function getSceneComponentId(): ?string
    {
        return $this->sceneComponentId;
    }

    private function internalAttachGameObject(GameObject $gameObject): void
    {
        $this->gameObjectValue = $gameObject;
    }

    private function internalAttachComponentId(int $componentId): void
    {
        $this->componentId = $componentId;
    }

    private function internalAttachSceneComponentId(string $sceneComponentId): void
    {
        $this->sceneComponentId = $sceneComponentId;
    }

    private function internalEnsureRequiredComponents(): void
    {
        if ($this->gameObjectValue === null) {
            throw new RuntimeException(
                "Cannot resolve RequireComponent dependencies for '" . static::class . "' before it is attached to a GameObject.",
            );
        }

        $this->ensureRequiredComponents($this->gameObjectValue);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function internalApplyProperties(array $properties): void
    {
        if ($properties === []) {
            return;
        }

        $this->applySerializedPropertiesToObject($this, $properties);
    }

    public function __serialize(): array
    {
        return [
            '__lengaRefKind' => 'Behaviour',
            'className' => static::class,
            'componentSceneId' => $this->sceneComponentId,
            'gameObject' => $this->gameObject->__serialize(),
            'instanceId' => $this->componentId,
        ];
    }

    public function __unserialize(array $data): void
    {
        $className = isset($data['className']) && is_string($data['className'])
            ? $data['className']
            : static::class;
        $componentSceneId = isset($data['componentSceneId']) && is_string($data['componentSceneId'])
            ? $data['componentSceneId']
            : null;
        $gameObjectData = is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);
        if ($gameObject !== null) {
            $resolved = null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($className) as $candidate) {
                    if (
                        $candidate instanceof self &&
                        property_exists($candidate, 'sceneComponentId') &&
                        $candidate->sceneComponentId === $componentSceneId
                    ) {
                        $resolved = $candidate;
                        break;
                    }
                }
            }
            if ($resolved === null) {
                $resolved = $gameObject->getComponent($className);
            }
            if ($resolved instanceof self) {
                $this->gameObjectValue = $resolved->gameObject;
                $this->componentId = $resolved->getInstanceId();
                $this->sceneComponentId = $resolved->sceneComponentId ?? $componentSceneId;

                $resolvedReflection = new ReflectionObject($resolved);
                foreach ($resolvedReflection->getProperties() as $property) {
                    if ($property->isStatic()) {
                        continue;
                    }

                    if (!$property->isPublic()) {
                        continue;
                    }

                    try {
                        $this->{$property->getName()} = $property->getValue($resolved);
                    } catch (Throwable) {
                    }
                }
                return;
            }
        }

        $this->componentId = isset($data['instanceId']) && is_int($data['instanceId']) ? $data['instanceId'] : null;
        $this->sceneComponentId = $componentSceneId;
        if ($gameObject !== null) {
            $this->gameObjectValue = $gameObject;
        }
    }

    private function resolveSerializedPropertyValue(ReflectionProperty $property, mixed $value): mixed
    {
        if ($value instanceof Color || $value instanceof Vector2 || $value instanceof Vector3 || $value instanceof Vector4) {
            return $value;
        }

        if (!is_array($value) || !isset($value['__lengaRefKind']) || !is_string($value['__lengaRefKind'])) {
            return $this->resolveStructuredValueType($property, $value);
        }

        $type = $property->getType();
        if (!$type instanceof ReflectionType) {
            return $value;
        }

        $resolvedTypeName = $this->resolvePropertyTypeName($type);
        if ($resolvedTypeName === null) {
            return $value;
        }

        return $this->resolveSerializedReferenceValueForType($resolvedTypeName, $value);
    }

    /**
     * @param array<string, mixed> $value
     */
    private function resolveSerializedReferenceValueForType(string $resolvedTypeName, array $value): mixed
    {
        if (!isset($value['__lengaRefKind']) || !is_string($value['__lengaRefKind'])) {
            return $value;
        }

        if ($value['__lengaRefKind'] === 'Canvas') {
            return $resolvedTypeName === Canvas::class
                ? Canvas::fromSerializedReference($value)
                : null;
        }

        if ($value['__lengaRefKind'] === 'UIElement') {
            $element = UIElement::fromSerializedReference($value);
            if ($element === null) {
                return null;
            }

            return $resolvedTypeName === UIElement::class || $element instanceof $resolvedTypeName
                ? $element
                : null;
        }

        $gameObject = $this->resolveSerializedReferenceGameObject($value);
        if ($gameObject === null) {
            return null;
        }
        $prefabComponent = $gameObject->prefabAssetPath !== ''
            ? GameObject::componentFromSerializedReference($value)
            : null;

        if ($resolvedTypeName === GameObject::class) {
            return $gameObject;
        }

        if ($resolvedTypeName === Transform::class) {
            if ($prefabComponent instanceof Transform) {
                return $prefabComponent;
            }

            return $gameObject->transform;
        }

        if ($resolvedTypeName === Behaviour::class || is_subclass_of($resolvedTypeName, Behaviour::class)) {
            if (
                $prefabComponent instanceof Behaviour &&
                ($resolvedTypeName === Behaviour::class || $prefabComponent instanceof $resolvedTypeName)
            ) {
                return $prefabComponent;
            }

            $componentClass = isset($value['className']) && is_string($value['className']) && $resolvedTypeName === Behaviour::class
                ? $value['className']
                : $resolvedTypeName;
            $componentSceneId = isset($value['componentSceneId']) && is_string($value['componentSceneId'])
                ? $value['componentSceneId']
                : null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($componentClass) as $candidate) {
                    if (
                        $candidate instanceof Behaviour &&
                        property_exists($candidate, 'sceneComponentId') &&
                        $candidate->sceneComponentId === $componentSceneId
                    ) {
                        return $candidate;
                    }
                }
            }
            return $gameObject->getComponent($componentClass);
        }

        if ($resolvedTypeName === Component::class || is_subclass_of($resolvedTypeName, Component::class)) {
            if (
                $prefabComponent instanceof Component &&
                ($resolvedTypeName === Component::class || $prefabComponent instanceof $resolvedTypeName)
            ) {
                return $prefabComponent;
            }

            $componentType = isset($value['componentType']) && is_string($value['componentType']) && $resolvedTypeName === Component::class
                ? $value['componentType']
                : $resolvedTypeName;

            if ($componentType === Transform::class || $componentType === 'Transform') {
                return null;
            }

            $componentSceneId = isset($value['componentSceneId']) && is_string($value['componentSceneId'])
                ? $value['componentSceneId']
                : null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($componentType) as $candidate) {
                    if (
                        $candidate instanceof Component &&
                        $candidate->getSceneComponentId() === $componentSceneId
                    ) {
                        return $candidate;
                    }
                }
            }

            $component = $gameObject->getComponent($componentType);
            return $component instanceof Component ? $component : null;
        }

        if (
            $resolvedTypeName === ComponentInterface::class ||
            $resolvedTypeName === RendererInterface::class ||
            (interface_exists($resolvedTypeName) && is_subclass_of($resolvedTypeName, ComponentInterface::class))
        ) {
            if ($prefabComponent instanceof $resolvedTypeName) {
                return $prefabComponent;
            }

            $componentType = isset($value['componentType']) && is_string($value['componentType'])
                ? $value['componentType']
                : $resolvedTypeName;

            if ($componentType === Transform::class || $componentType === 'Transform') {
                return null;
            }

            $componentSceneId = isset($value['componentSceneId']) && is_string($value['componentSceneId'])
                ? $value['componentSceneId']
                : null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($componentType) as $candidate) {
                    if (
                        $candidate instanceof $resolvedTypeName &&
                        (
                            !method_exists($candidate, 'getSceneComponentId') ||
                            $candidate->getSceneComponentId() === $componentSceneId
                        )
                    ) {
                        return $candidate;
                    }
                }
            }

            $component = $gameObject->getComponent($componentType);
            return $component instanceof $resolvedTypeName ? $component : null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function resolveSerializedReferenceGameObject(array $value): ?GameObject
    {
        $referenceKind = $value['__lengaRefKind'] ?? null;
        if (
            \is_string($referenceKind) &&
            ($referenceKind === 'Component' || $referenceKind === 'Behaviour' || $referenceKind === 'Transform') &&
            \is_array($value['gameObject'] ?? null)
        ) {
            return GameObject::fromSerializedReference($value['gameObject']);
        }

        return GameObject::fromSerializedReference($value);
    }

    private function resolveStructuredValueType(ReflectionProperty $property, mixed $value): mixed
    {
        $type = $property->getType();
        if (!$type instanceof ReflectionType) {
            return $value;
        }

        $resolvedTypeName = $this->resolvePropertyTypeName($type);
        if ($resolvedTypeName === null) {
            return $value;
        }

        if ($resolvedTypeName === 'array' && is_array($value)) {
            return $this->resolveListValue($property, $value);
        }

        if ($resolvedTypeName === AudioClip::class) {
            return AudioClip::fromSerializedReference($value);
        }

        if ($resolvedTypeName === AudioMixer::class) {
            return AudioMixer::fromSerializedReference($value);
        }

        if (enum_exists($resolvedTypeName)) {
            return $this->resolveEnumValue($resolvedTypeName, $value);
        }

        if ($resolvedTypeName === Color::class) {
            if ($value === null && $type->allowsNull()) {
                return null;
            }

            return is_array($value) ? Color::fromRGBAArray($value) : new Color();
        }

        if (!is_array($value)) {
            return $value;
        }

        if ($this->propertyHasAttribute($property, SerializeReference::class)) {
            return $this->resolveManagedReferenceValue($resolvedTypeName, $value);
        }

        if ($resolvedTypeName === Vector2::class) {
            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        if ($resolvedTypeName === Vector3::class) {
            return new Vector3(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
                (float) ($value['z'] ?? 0.0),
            );
        }

        if ($resolvedTypeName === Vector4::class) {
            return new Vector4(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
                (float) ($value['z'] ?? 0.0),
                (float) ($value['w'] ?? 0.0),
            );
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $value
     * @return array<array-key, mixed>
     */
    private function resolveListValue(ReflectionProperty $property, array $value): array
    {
        $itemTypeName = $this->resolveListItemTypeName($property);
        if ($itemTypeName === null || $itemTypeName === '') {
            return $value;
        }

        foreach ($value as $key => $itemValue) {
            $value[$key] = $this->resolveListItemValue($itemTypeName, $itemValue);
        }

        return $value;
    }

    private function resolveListItemValue(string $itemTypeName, mixed $value): mixed
    {
        $itemTypeName = ltrim($itemTypeName, '\\');
        if ($itemTypeName === '') {
            return $value;
        }

        if ($itemTypeName === AudioClip::class) {
            return AudioClip::fromSerializedReference($value);
        }

        if ($itemTypeName === AudioMixer::class) {
            return AudioMixer::fromSerializedReference($value);
        }

        if (enum_exists($itemTypeName)) {
            return $this->resolveEnumValue($itemTypeName, $value);
        }

        if ($itemTypeName === Color::class) {
            return is_array($value) ? Color::fromRGBAArray($value) : $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        if (isset($value['__lengaRefKind']) && is_string($value['__lengaRefKind'])) {
            return $this->resolveSerializedReferenceValueForType($itemTypeName, $value);
        }

        if ($itemTypeName === Vector2::class) {
            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        if ($itemTypeName === Vector3::class) {
            return new Vector3(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
                (float) ($value['z'] ?? 0.0),
            );
        }

        if ($itemTypeName === Vector4::class) {
            return new Vector4(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
                (float) ($value['z'] ?? 0.0),
                (float) ($value['w'] ?? 0.0),
            );
        }

        if (class_exists($itemTypeName)) {
            return $this->resolveManagedReferenceValue($itemTypeName, $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function applySerializedPropertiesToObject(object $target, array $properties): void
    {
        $reflection = new ReflectionObject($target);

        foreach ($properties as $name => $value) {
            if (!is_string($name) || $name === '' || $name[0] === '_' || !$reflection->hasProperty($name)) {
                continue;
            }

            $property = $reflection->getProperty($name);
            if ($property->isStatic()) {
                continue;
            }

            $resolvedValue = $this->resolveSerializedPropertyValue($property, $value);

            try {
                $property->setValue($target, $resolvedValue);
            } catch (Throwable) {
                // Ignore incompatible assignments for MVP; script authors can fix the scene value.
            }
        }

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic() || !$this->propertyHasAttribute($property, SerializeReference::class)) {
                continue;
            }

            $type = $property->getType();
            $resolvedTypeName = $type instanceof ReflectionType
                ? $this->resolvePropertyTypeName($type)
                : null;
            if ($resolvedTypeName === null) {
                continue;
            }

            if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
                continue;
            }

            try {
                if ($property->isInitialized($target) && $property->getValue($target) !== null) {
                    continue;
                }
            } catch (Throwable) {
                // Fall through and try to initialize a default object.
            }

            $defaultReference = $this->resolveManagedReferenceValue($resolvedTypeName, [
                '__className' => $resolvedTypeName,
            ]);

            try {
                $property->setValue($target, $defaultReference);
            } catch (Throwable) {
                // Leave the property untouched if the reference cannot be initialized.
            }
        }
    }

    private function propertyHasAttribute(ReflectionProperty $property, string $attributeClassName): bool
    {
        foreach ($property->getAttributes() as $attribute) {
            $resolvedName = ltrim($attribute->getName(), '\\');
            if ($resolvedName === ltrim($attributeClassName, '\\')) {
                return true;
            }
        }

        return false;
    }

    private function resolveListItemTypeName(ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(ListOf::class);
        if ($attributes === []) {
            return null;
        }

        $listOf = $attributes[0]->newInstance();
        $itemTypeName = $listOf->listType;
        if (!is_string($itemTypeName) || $itemTypeName === '') {
            return null;
        }

        return ltrim($itemTypeName, '\\');
    }

    private function resolveManagedReferenceValue(string $declaredTypeName, mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $concreteTypeName = $declaredTypeName;
        if (isset($value['__className']) && is_string($value['__className']) && $value['__className'] !== '') {
            $concreteTypeName = ltrim($value['__className'], '\\');
        }

        if ($concreteTypeName === '' || !class_exists($concreteTypeName)) {
            return $value;
        }

        if ($declaredTypeName !== '' &&
            $concreteTypeName !== $declaredTypeName &&
            !is_a($concreteTypeName, $declaredTypeName, true)) {
            return $value;
        }

        try {
            $reflection = new ReflectionClass($concreteTypeName);
            if (!$reflection->isInstantiable()) {
                return $value;
            }

            $instance = $reflection->newInstanceWithoutConstructor();
        } catch (Throwable) {
            return $value;
        }

        $nestedProperties = $value;
        unset($nestedProperties['__className']);
        $this->applySerializedPropertiesToObject($instance, $nestedProperties);

        return $instance;
    }

    /**
     * @param string $resolvedTypeName
     * @param mixed $value
     * @return mixed
     */
    private function resolveEnumValue(string $resolvedTypeName, mixed $value): mixed
    {
        if ($value === null || $value instanceof $resolvedTypeName) {
            return $value;
        }

        if (is_subclass_of($resolvedTypeName, BackedEnum::class) &&
            (is_string($value) || is_int($value))) {
            $resolvedCase = $resolvedTypeName::tryFrom($value);
            if ($resolvedCase !== null) {
                return $resolvedCase;
            }
        }

        if (is_string($value)) {
            foreach ($resolvedTypeName::cases() as $enumCase) {
                if ($enumCase->name === $value) {
                    return $enumCase;
                }
            }
        }

        return $value;
    }

    private function resolvePropertyTypeName(ReflectionType $type): ?string
    {
        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType instanceof ReflectionNamedType && $unionType->getName() !== 'null') {
                    return $unionType->getName();
                }
            }
        }

        return null;
    }

    /**
     * Starts a coroutine: a method that runs over several frames, pausing at each `yield`.
     *
     * Write the routine as a method that returns `\Generator`, then pass the
     * generator it returns: `$this->startCoroutine($this->flash())`. The
     * routine runs at once up to its first `yield`. What it yields decides
     * when it continues:
     *
     * - `yield new WaitForSeconds(1.0);` waits for gameplay time, which stops
     *   while the game is paused ({@see WaitForSeconds}).
     * - `yield new WaitForSecondsRealtime(1.0);` waits for real time
     *   ({@see WaitForSecondsRealtime}).
     * - `yield new WaitUntil(fn () => ...);` and {@see WaitWhile} wait for a
     *   condition.
     * - `yield new WaitForFixedUpdate();` continues after the next
     *   {@see Behaviour::fixedUpdate()} ({@see WaitForFixedUpdate}).
     * - Any other value, or a bare `yield;`, continues the next time
     *   coroutines advance after {@see Behaviour::update()}.
     *
     * Coroutines continue after this behaviour's {@see Behaviour::update()}
     * each frame, or after {@see Behaviour::fixedUpdate()} when waiting for a
     * fixed step, even in a frame where that method threw. They pause while
     * the behaviour is disabled and stop when it is destroyed. A coroutine
     * whose code throws an exception it does not catch ends; the exception
     * is logged, and the behaviour's other coroutines keep running.
     *
     * @param Generator $routine The generator returned by calling your coroutine method.
     * @return Coroutine A handle you can pass to {@see Behaviour::stopCoroutine()}.
     */
    public function startCoroutine(Generator $routine): Coroutine
    {
        $coroutine = new Coroutine($routine);
        $this->coroutines[$coroutine->getId()] = $coroutine;
        $coroutine->prime();

        if ($coroutine->isFinished()) {
            unset($this->coroutines[$coroutine->getId()]);
        }

        return $coroutine;
    }

    /**
     * Stops one coroutine this behaviour started.
     *
     * The coroutine does not run again. Nothing happens when the routine has
     * already finished or was not started by this behaviour.
     *
     * @param Coroutine|Generator $routine The handle {@see Behaviour::startCoroutine()} returned,
     *     or the generator you passed to it.
     */
    public function stopCoroutine(Coroutine|Generator $routine): void
    {
        foreach ($this->coroutines as $id => $coroutine) {
            if ($routine instanceof Coroutine) {
                if ($coroutine !== $routine) {
                    continue;
                }
            } elseif (!$coroutine->matchesGenerator($routine)) {
                continue;
            }

            $coroutine->stop();
            unset($this->coroutines[$id]);
            return;
        }
    }

    /**
     * Stops every coroutine this behaviour started.
     *
     * The engine calls this for you when the behaviour is destroyed.
     */
    public function stopAllCoroutines(): void
    {
        foreach ($this->coroutines as $coroutine) {
            $coroutine->stop();
        }

        $this->coroutines = [];
    }

    /**
     * Creates a {@see Signal} owned by this behaviour.
     *
     * Use a signal when other code holds a reference to this behaviour and
     * should react to something it does, such as a door that opens. The
     * engine removes every listener from the signal when the behaviour is
     * destroyed. For game-wide events that listeners receive without a
     * reference to the sender, use {@see Behaviour::emitEvent()} and
     * {@see Behaviour::onEvent()} instead.
     *
     * @return Signal A new, empty signal.
     */
    protected function createSignal(): Signal
    {
        $signal = new Signal();
        $this->ownedSignals[spl_object_id($signal)] = $signal;
        return $signal;
    }

    /**
     * Sends a named event to every listener in the game.
     *
     * Every listener registered with {@see Behaviour::onEvent()},
     * {@see Behaviour::onceEvent()} or {@see EventBus::on()} for the name
     * receives the payload. The sender needs no reference to the listeners.
     *
     * @param string $eventName Name of the event, for example `game.door.opened`.
     * @param mixed $payload Value passed to each listener, or null for none.
     */
    protected function emitEvent(string $eventName, mixed $payload = null): void
    {
        EventBus::emit($eventName, $payload);
    }

    /**
     * Sends a named event to every listener in the game. Same as {@see Behaviour::emitEvent()}.
     *
     * @param string $eventName Name of the event, for example `game.door.opened`.
     * @param mixed $payload Value passed to each listener, or null for none.
     */
    protected function dispatchEvent(string $eventName, mixed $payload = null): void
    {
        $this->emitEvent($eventName, $payload);
    }

    /**
     * Runs a listener each time the named event is sent, until this behaviour removes it.
     *
     * The behaviour keeps the subscription and removes it for you: when the
     * behaviour is disabled by default, or only when it is destroyed if
     * `$disposeOnDisable` is false. Subscribe in {@see Behaviour::onEnable()}
     * to listen whenever the behaviour is enabled.
     *
     * Calling `onEvent()` again from the same line of code for the same event
     * replaces the earlier subscription, so listeners do not pile up when
     * that line runs more than once.
     *
     * @param string $eventName Name of the event to listen for.
     * @param callable $listener Function called with the event's payload.
     * @param bool $disposeOnDisable True to remove the listener when the behaviour is disabled;
     *     false to keep it until the behaviour is destroyed.
     * @return SignalSubscription The subscription. Call {@see SignalSubscription::dispose()}
     *     on it to stop listening early.
     */
    protected function onEvent(
        string $eventName,
        callable $listener,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        return $this->trackSubscription(
            $this->replaceOwnerCallsiteEventSubscription($eventName, $listener, false),
            $disposeOnDisable
        );
    }

    /**
     * Runs a listener the next time the named event is sent, then removes it.
     *
     * Works like {@see Behaviour::onEvent()}: the behaviour removes the
     * listener for you if the event has not arrived when it is disabled, or
     * destroyed when `$disposeOnDisable` is false.
     *
     * @param string $eventName Name of the event to listen for.
     * @param callable $listener Function called once with the event's payload.
     * @param bool $disposeOnDisable True to remove the listener when the behaviour is disabled;
     *     false to keep it until the behaviour is destroyed.
     * @return SignalSubscription The subscription. Call {@see SignalSubscription::dispose()}
     *     on it to cancel before the event arrives.
     */
    protected function onceEvent(
        string $eventName,
        callable $listener,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        return $this->trackSubscription(
            $this->replaceOwnerCallsiteEventSubscription($eventName, $listener, true),
            $disposeOnDisable
        );
    }

    /**
     * Hands a subscription to this behaviour so it is removed with the behaviour.
     *
     * Use it for subscriptions you create yourself, for example with
     * {@see EventBus::on()} or {@see Signal::add()}, so you do not have to
     * dispose them by hand.
     *
     * @param SignalSubscription $subscription The subscription to manage.
     * @param bool $disposeOnDisable True to dispose it when the behaviour is disabled;
     *     false to keep it until the behaviour is destroyed.
     * @return SignalSubscription The same subscription, so you can chain the call.
     */
    protected function trackSubscription(
        SignalSubscription $subscription,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        $subscriptionId = spl_object_id($subscription);
        if ($disposeOnDisable) {
            $this->ownedSubscriptionsOnDisable[$subscriptionId] = $subscription;
            unset($this->ownedSubscriptionsUntilDestroy[$subscriptionId]);
        } else {
            $this->ownedSubscriptionsUntilDestroy[$subscriptionId] = $subscription;
            unset($this->ownedSubscriptionsOnDisable[$subscriptionId]);
        }

        return $subscription;
    }

    private function internalAwake(): void
    {
        $this->awake();
    }

    private function internalOnEnable(): void
    {
        if ($this->lifecycleEnabled) {
            return;
        }

        $this->lifecycleEnabled = true;
        $this->onEnable();
    }

    private function internalStart(): void
    {
        $this->start();
    }

    private function internalFixedUpdate(): void
    {
        // FixedUpdate and the coroutines are guarded apart, so an exception
        // in one does not stop the other.
        try {
            $this->fixedUpdate();
        } catch (Throwable $throwable) {
            ScriptExceptions::report($throwable, static::class . '::fixedUpdate()');
        }
        $this->tickCoroutinesFixedUpdate();
    }

    private function internalUpdate(): void
    {
        // Update and the coroutines are guarded apart, so an exception in
        // one does not stop the other.
        try {
            $this->update();
        } catch (Throwable $throwable) {
            ScriptExceptions::report($throwable, static::class . '::update()');
        }
        $this->tickCoroutines();
    }

    private function internalLateUpdate(): void
    {
        $this->lateUpdate();
    }

    private function internalOnDisable(): void
    {
        if (!$this->lifecycleEnabled) {
            $this->releaseOwnedSubscriptionsOnDisable();
            return;
        }

        $this->lifecycleEnabled = false;
        $this->releaseOwnedSubscriptionsOnDisable();
        $this->onDisable();
    }

    private function internalOnDestroy(): void
    {
        $this->lifecycleEnabled = false;
        $this->releaseOwnedSubscriptions();
        // The engine's own cleanup runs even when the script's onDestroy()
        // throws; the engine then reports the exception.
        try {
            $this->onDestroy();
        } finally {
            $this->stopAllCoroutines();
            $this->releaseOwnedSignals();
        }
    }

    private function tickCoroutines(): void
    {
        if ($this->coroutines === []) {
            return;
        }

        foreach ($this->coroutines as $id => $coroutine) {
            if (!isset($this->coroutines[$id]) || $this->coroutines[$id] !== $coroutine) {
                continue;
            }

            // A coroutine that throws ends; the others carry on.
            try {
                $coroutine->tick();
            } catch (Throwable $throwable) {
                ScriptExceptions::report($throwable, static::class . ' coroutine');
            }

            if ($coroutine->isFinished()) {
                unset($this->coroutines[$id]);
            }
        }
    }

    private function tickCoroutinesFixedUpdate(): void
    {
        if ($this->coroutines === []) {
            return;
        }

        foreach ($this->coroutines as $id => $coroutine) {
            if (!isset($this->coroutines[$id]) || $this->coroutines[$id] !== $coroutine) {
                continue;
            }

            // A coroutine that throws ends; the others carry on.
            try {
                $coroutine->tickFixedUpdate();
            } catch (Throwable $throwable) {
                ScriptExceptions::report($throwable, static::class . ' coroutine');
            }

            if ($coroutine->isFinished()) {
                unset($this->coroutines[$id]);
            }
        }
    }

    private function releaseTrackedSubscriptions(array &$subscriptions): void
    {
        foreach ($subscriptions as $subscriptionId => $subscription) {
            $subscription->dispose();
            unset($subscriptions[$subscriptionId]);
        }
    }

    private function replaceOwnerCallsiteEventSubscription(
        string $eventName,
        callable $listener,
        bool $once
    ): SignalSubscription
    {
        $registryKey = $this->eventSubscriptionRegistryKey($eventName, $once);
        if (isset(self::$eventSubscriptionsByOwnerCallsite[$registryKey])) {
            self::$eventSubscriptionsByOwnerCallsite[$registryKey]->dispose();
        }

        $subscription = null;
        if ($once) {
            $rawSubscription = EventBus::on(
                $eventName,
                static function (mixed ...$arguments) use (&$subscription, $listener): void {
                    $subscription?->dispose();
                    $listener(...$arguments);
                }
            );
        } else {
            $rawSubscription = EventBus::on($eventName, $listener);
        }

        $subscription = new SignalSubscription(
            static function () use ($rawSubscription, $registryKey, &$subscription): void {
                $rawSubscription->dispose();
                if ((self::$eventSubscriptionsByOwnerCallsite[$registryKey] ?? null) === $subscription) {
                    unset(self::$eventSubscriptionsByOwnerCallsite[$registryKey]);
                }
            }
        );

        self::$eventSubscriptionsByOwnerCallsite[$registryKey] = $subscription;
        return $subscription;
    }

    private function eventSubscriptionRegistryKey(string $eventName, bool $once): string
    {
        $ownerKey = $this->componentId !== null
            ? 'component:' . $this->componentId
            : 'object:' . spl_object_id($this);

        return $ownerKey . '|' . ($once ? 'once' : 'on') . '|' . $eventName . '|' . $this->eventSubscriptionCallsite();
    }

    private function eventSubscriptionCallsite(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10) as $frame) {
            if (!isset($frame['file'], $frame['line']) || $frame['file'] === __FILE__) {
                continue;
            }

            return $frame['file'] . ':' . $frame['line'];
        }

        return 'unknown:0';
    }

    private function releaseOwnedSubscriptionsOnDisable(): void
    {
        $this->releaseTrackedSubscriptions($this->ownedSubscriptionsOnDisable);
    }

    private function releaseOwnedSubscriptions(): void
    {
        $this->releaseTrackedSubscriptions($this->ownedSubscriptionsOnDisable);
        $this->releaseTrackedSubscriptions($this->ownedSubscriptionsUntilDestroy);
    }

    private function releaseOwnedSignals(): void
    {
        foreach ($this->ownedSignals as $signal) {
            $signal->clear();
        }

        $this->ownedSignals = [];
    }

    /**
     * Called once, the first time the GameObject is active in the hierarchy.
     *
     * Inspector values are applied before it runs. Use it to find
     * components with {@see GameObject::getComponent()} and set up state the
     * behaviour needs. It runs before {@see Behaviour::onEnable()} and
     * {@see Behaviour::start()}. A GameObject that starts inactive receives
     * `awake()` when it is first activated.
     */
    public function awake(): void {}

    /**
     * Called each time the behaviour becomes enabled on an active GameObject.
     *
     * Runs right after {@see Behaviour::awake()} the first time, then again
     * whenever {@see Behaviour::$enabled} is set back to true or the GameObject
     * is reactivated. Subscribe to events here with {@see Behaviour::onEvent()}.
     */
    public function onEnable(): void {}

    /**
     * Called once, at the start of the first frame in which the behaviour is enabled and active.
     *
     * Every `awake()` in the scene has run by then, so other objects are set
     * up and safe to read. It runs before that frame's first
     * {@see Behaviour::fixedUpdate()} and {@see Behaviour::update()}.
     */
    public function start(): void {}

    /**
     * Called once per fixed physics step, before the step runs.
     *
     * The engine runs as many fixed steps as the elapsed time needs, so a
     * frame can have none, one or several calls. Apply forces and change
     * physics bodies here. Read the step length with
     * {@see Time::fixedDeltaTime()}. It does not run while the game is paused.
     */
    public function fixedUpdate(): void {}

    /**
     * Called once per frame.
     *
     * Read input and run most gameplay logic here. Multiply movement by
     * {@see Time::deltaTime()} so it is the same at any frame rate. It runs
     * after the frame's fixed steps and before {@see Behaviour::lateUpdate()}.
     * It keeps running while the game is paused, with `Time::deltaTime()` at zero.
     */
    public function update(): void {}

    /**
     * Called once per frame, after every behaviour's {@see Behaviour::update()}.
     *
     * Use it for work that depends on where objects ended up this frame, such
     * as a camera that follows the player. It keeps running while the game is
     * paused.
     */
    public function lateUpdate(): void {}

    /**
     * Called each time the behaviour is disabled or its GameObject becomes inactive.
     *
     * Listeners added with {@see Behaviour::onEvent()} and
     * {@see Behaviour::onceEvent()} with the default `$disposeOnDisable` are
     * removed just before it runs. It also runs before
     * {@see Behaviour::onDestroy()} when an enabled behaviour is destroyed.
     */
    public function onDisable(): void {}

    /**
     * Called once, when the behaviour or its GameObject is destroyed.
     *
     * Release anything the behaviour set up outside itself. Every
     * subscription the behaviour manages is removed just before it runs.
     * After it returns, the engine stops the behaviour's coroutines and
     * clears the signals it created with {@see Behaviour::createSignal()},
     * also when it throws.
     */
    public function onDestroy(): void {}
}
