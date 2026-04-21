<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\RequireComponent;
use Lenga\Engine\Attributes\SerializeReference;
use Lenga\Engine\Interfaces\ComponentInterface;

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

    public function __construct() {}

    // ---------------------------------------------------------------------
    // Unity-style property: gameObject
    // ---------------------------------------------------------------------

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
    // Unity-style property: transform
    // (always reflects this behaviour's GameObject)
    // ---------------------------------------------------------------------

    public Transform $transform {
        get {
            return $this->gameObject->transform;
        }
    }

    // INTERNAL: Called by the engine bridge
    public function __internalAttachGameObject(GameObject $gameObject): void
    {
        $this->gameObjectValue = $gameObject;
        $this->__internalEnsureRequiredComponents($gameObject);
    }

    public function __internalAttachComponentId(int $componentId): void
    {
        $this->componentId = $componentId;
    }

    public function __internalAttachSceneComponentId(string $sceneComponentId): void
    {
        $this->sceneComponentId = $sceneComponentId;
    }

    private function __internalEnsureRequiredComponents(GameObject $gameObject): void
    {
        static $resolving = [];

        try {
            $reflection = new \ReflectionObject($this);
            foreach ($reflection->getAttributes(RequireComponent::class) as $attribute) {
                $required = $attribute->newInstance();
                foreach ($required->componentTypes as $componentType) {
                    if (!\is_string($componentType) || $componentType === '') {
                        continue;
                    }

                    if ($componentType === self::class || $componentType === static::class) {
                        continue;
                    }

                    if ($gameObject->hasComponent($componentType)) {
                        continue;
                    }

                    $gameObjectId = $gameObject->getInstanceId() ?? \spl_object_id($gameObject);
                    $resolutionKey = $gameObjectId . '|' . $componentType;
                    if (isset($resolving[$resolutionKey])) {
                        continue;
                    }

                    $resolving[$resolutionKey] = true;
                    try {
                        $gameObject->addComponent($componentType);
                    } finally {
                        unset($resolving[$resolutionKey]);
                    }
                }
            }
        } catch (\Throwable $exception) {
            Debug::warn(
                "Failed to apply RequireComponent for '" . static::class . "': " . $exception->getMessage()
            );
        }
    }

    public bool $enabled {
        get {
            if ($this->componentId === null) {
                return true;
            }

            return \lenga_internal_component_get_enabled($this->componentId);
        }

        set(bool $value) {
            if ($this->componentId === null) {
                return;
            }

            \lenga_internal_component_set_enabled($this->componentId, $value);
        }
    }

    public function getInstanceId(): ?int
    {
        return $this->componentId;
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
        $className = isset($data['className']) && \is_string($data['className'])
            ? $data['className']
            : static::class;
        $componentSceneId = isset($data['componentSceneId']) && \is_string($data['componentSceneId'])
            ? $data['componentSceneId']
            : null;
        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);
        if ($gameObject !== null) {
            $resolved = null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($className) as $candidate) {
                    if (
                        $candidate instanceof self &&
                        \property_exists($candidate, 'sceneComponentId') &&
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

                $resolvedReflection = new \ReflectionObject($resolved);
                foreach ($resolvedReflection->getProperties() as $property) {
                    if ($property->isStatic()) {
                        continue;
                    }

                    if (!$property->isPublic()) {
                        continue;
                    }

                    try {
                        $this->{$property->getName()} = $property->getValue($resolved);
                    } catch (\Throwable) {
                    }
                }
                return;
            }
        }

        $this->componentId = isset($data['instanceId']) && \is_int($data['instanceId']) ? $data['instanceId'] : null;
        $this->sceneComponentId = $componentSceneId;
        if ($gameObject !== null) {
            $this->gameObjectValue = $gameObject;
        }
    }

    /**
     * INTERNAL: Apply serialized scene properties before lifecycle callbacks run.
     *
     * MVP support is intentionally limited to properties that already exist on the
     * behaviour class. Unknown keys are ignored.
     *
     * @param array<string, mixed> $properties
     */
    public function __internalApplyProperties(array $properties): void
    {
        if ($properties === []) {
            return;
        }

        $this->applySerializedPropertiesToObject($this, $properties);
    }

    private function resolveSerializedPropertyValue(\ReflectionProperty $property, mixed $value): mixed
    {
        if ($value instanceof Vector2 || $value instanceof Vector3) {
            return $value;
        }

        if (!\is_array($value) || !isset($value['__lengaRefKind']) || !\is_string($value['__lengaRefKind'])) {
            return $this->resolveStructuredValueType($property, $value);
        }

        $type = $property->getType();
        if (!$type instanceof \ReflectionType) {
            return $value;
        }

        $resolvedTypeName = $this->resolvePropertyTypeName($type);
        if ($resolvedTypeName === null) {
            return $value;
        }

        $gameObject = GameObject::fromSerializedReference($value);
        if ($gameObject === null) {
            return null;
        }

        if ($resolvedTypeName === GameObject::class) {
            return $gameObject;
        }

        if ($resolvedTypeName === Transform::class) {
            return $gameObject->transform;
        }

        if ($resolvedTypeName === Behaviour::class || \is_subclass_of($resolvedTypeName, Behaviour::class)) {
            $componentClass = isset($value['className']) && \is_string($value['className']) && $resolvedTypeName === Behaviour::class
                ? $value['className']
                : $resolvedTypeName;
            $componentSceneId = isset($value['componentSceneId']) && \is_string($value['componentSceneId'])
                ? $value['componentSceneId']
                : null;
            if ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($componentClass) as $candidate) {
                    if (
                        $candidate instanceof Behaviour &&
                        \property_exists($candidate, 'sceneComponentId') &&
                        $candidate->sceneComponentId === $componentSceneId
                    ) {
                        return $candidate;
                    }
                }
            }
            return $gameObject->getComponent($componentClass);
        }

        if ($resolvedTypeName === Component::class || \is_subclass_of($resolvedTypeName, Component::class)) {
            $componentType = isset($value['componentType']) && \is_string($value['componentType']) && $resolvedTypeName === Component::class
                ? $value['componentType']
                : $resolvedTypeName;

            if ($componentType === Transform::class || $componentType === 'Transform') {
                return $gameObject->transform;
            }

            $componentSceneId = isset($value['componentSceneId']) && \is_string($value['componentSceneId'])
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

            return $gameObject->getComponent($componentType);
        }

        return $value;
    }

    private function resolveStructuredValueType(\ReflectionProperty $property, mixed $value): mixed
    {
        $type = $property->getType();
        if (!$type instanceof \ReflectionType) {
            return $value;
        }

        $resolvedTypeName = $this->resolvePropertyTypeName($type);
        if ($resolvedTypeName === null) {
            return $value;
        }

        if (\enum_exists($resolvedTypeName)) {
            return $this->resolveEnumValue($resolvedTypeName, $value);
        }

        if (!\is_array($value)) {
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

        return $value;
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function applySerializedPropertiesToObject(object $target, array $properties): void
    {
        $reflection = new \ReflectionObject($target);

        foreach ($properties as $name => $value) {
            if (!\is_string($name) || $name === '' || $name[0] === '_' || !$reflection->hasProperty($name)) {
                continue;
            }

            $property = $reflection->getProperty($name);
            if ($property->isStatic()) {
                continue;
            }

            if (!$property->isPublic() && \method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }

            $resolvedValue = $this->resolveSerializedPropertyValue($property, $value);

            try {
                $property->setValue($target, $resolvedValue);
            } catch (\Throwable) {
                // Ignore incompatible assignments for MVP; script authors can fix the scene value.
            }
        }

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic() || !$this->propertyHasAttribute($property, SerializeReference::class)) {
                continue;
            }

            $type = $property->getType();
            $resolvedTypeName = $type instanceof \ReflectionType
                ? $this->resolvePropertyTypeName($type)
                : null;
            if ($resolvedTypeName === null) {
                continue;
            }

            if ($type instanceof \ReflectionNamedType && $type->allowsNull()) {
                continue;
            }

            if (!$property->isPublic() && \method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }

            try {
                if ($property->isInitialized($target) && $property->getValue($target) !== null) {
                    continue;
                }
            } catch (\Throwable) {
                // Fall through and try to initialize a default object.
            }

            $defaultReference = $this->resolveManagedReferenceValue($resolvedTypeName, [
                '__className' => $resolvedTypeName,
            ]);

            try {
                $property->setValue($target, $defaultReference);
            } catch (\Throwable) {
                // Leave the property untouched if the reference cannot be initialized.
            }
        }
    }

    private function propertyHasAttribute(\ReflectionProperty $property, string $attributeClassName): bool
    {
        foreach ($property->getAttributes() as $attribute) {
            $resolvedName = ltrim($attribute->getName(), '\\');
            if ($resolvedName === ltrim($attributeClassName, '\\')) {
                return true;
            }
        }

        return false;
    }

    private function resolveManagedReferenceValue(string $declaredTypeName, mixed $value): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        $concreteTypeName = $declaredTypeName;
        if (isset($value['__className']) && \is_string($value['__className']) && $value['__className'] !== '') {
            $concreteTypeName = ltrim($value['__className'], '\\');
        }

        if ($concreteTypeName === '' || !\class_exists($concreteTypeName)) {
            return $value;
        }

        if ($declaredTypeName !== '' &&
            $concreteTypeName !== $declaredTypeName &&
            !\is_a($concreteTypeName, $declaredTypeName, true)) {
            return $value;
        }

        try {
            $reflection = new \ReflectionClass($concreteTypeName);
            if (!$reflection->isInstantiable()) {
                return $value;
            }

            $instance = $reflection->newInstanceWithoutConstructor();
        } catch (\Throwable) {
            return $value;
        }

        $nestedProperties = $value;
        unset($nestedProperties['__className']);
        $this->applySerializedPropertiesToObject($instance, $nestedProperties);

        return $instance;
    }

    private function resolveEnumValue(string $resolvedTypeName, mixed $value): mixed
    {
        if ($value === null || $value instanceof $resolvedTypeName) {
            return $value;
        }

        if (\is_subclass_of($resolvedTypeName, \BackedEnum::class) &&
            (\is_string($value) || \is_int($value))) {
            $resolvedCase = $resolvedTypeName::tryFrom($value);
            if ($resolvedCase !== null) {
                return $resolvedCase;
            }
        }

        if (\is_string($value)) {
            foreach ($resolvedTypeName::cases() as $enumCase) {
                if ($enumCase->name === $value) {
                    return $enumCase;
                }
            }
        }

        return $value;
    }

    private function resolvePropertyTypeName(\ReflectionType $type): ?string
    {
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType instanceof \ReflectionNamedType && $unionType->getName() !== 'null') {
                    return $unionType->getName();
                }
            }
        }

        return null;
    }

    public function startCoroutine(\Generator $routine): Coroutine
    {
        $coroutine = new Coroutine($routine);
        $this->coroutines[$coroutine->getId()] = $coroutine;
        $coroutine->prime();

        if ($coroutine->isFinished()) {
            unset($this->coroutines[$coroutine->getId()]);
        }

        return $coroutine;
    }

    public function stopCoroutine(Coroutine|\Generator $routine): void
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

    public function stopAllCoroutines(): void
    {
        foreach ($this->coroutines as $coroutine) {
            $coroutine->stop();
        }

        $this->coroutines = [];
    }

    protected function createSignal(): Signal
    {
        $signal = new Signal();
        $this->ownedSignals[\spl_object_id($signal)] = $signal;
        return $signal;
    }

    protected function emitEvent(string $eventName, mixed $payload = null): void
    {
        EventBus::emit($eventName, $payload);
    }

    protected function dispatchEvent(string $eventName, mixed $payload = null): void
    {
        $this->emitEvent($eventName, $payload);
    }

    protected function onEvent(
        string $eventName,
        callable $listener,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        return $this->trackSubscription(EventBus::on($eventName, $listener), $disposeOnDisable);
    }

    protected function onceEvent(
        string $eventName,
        callable $listener,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        return $this->trackSubscription(EventBus::once($eventName, $listener), $disposeOnDisable);
    }

    protected function trackSubscription(
        SignalSubscription $subscription,
        bool $disposeOnDisable = true
    ): SignalSubscription
    {
        $subscriptionId = \spl_object_id($subscription);
        if ($disposeOnDisable) {
            $this->ownedSubscriptionsOnDisable[$subscriptionId] = $subscription;
            unset($this->ownedSubscriptionsUntilDestroy[$subscriptionId]);
        } else {
            $this->ownedSubscriptionsUntilDestroy[$subscriptionId] = $subscription;
            unset($this->ownedSubscriptionsOnDisable[$subscriptionId]);
        }

        return $subscription;
    }

    final public function __lengaInternalAwake(): void
    {
        $this->awake();
    }

    final public function __lengaInternalOnEnable(): void
    {
        $this->onEnable();
    }

    final public function __lengaInternalStart(): void
    {
        $this->start();
    }

    final public function __lengaInternalFixedUpdate(): void
    {
        $this->fixedUpdate();
        $this->tickCoroutinesFixedUpdate();
    }

    final public function __lengaInternalUpdate(): void
    {
        $this->update();
        $this->tickCoroutines();
    }

    final public function __lengaInternalLateUpdate(): void
    {
        $this->lateUpdate();
    }

    final public function __lengaInternalOnDisable(): void
    {
        $this->releaseOwnedSubscriptionsOnDisable();
        $this->onDisable();
    }

    final public function __lengaInternalOnDestroy(): void
    {
        $this->releaseOwnedSubscriptions();
        $this->onDestroy();
        $this->stopAllCoroutines();
        $this->releaseOwnedSignals();
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

            $coroutine->tick();

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

            $coroutine->tickFixedUpdate();

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
     * Called when the script instance is being loaded.
     * 
     * @return void
     */
    public function awake(): void {}

    /**
     * Called when the object becomes enabled and active.
     * 
     * @return void
     */
    public function onEnable(): void {}

    /**
     * Called before the first frame update, after all Awake calls.
     * 
     * @return void
     */
    public function start(): void {}

    /**
     * Called on a fixed timestep, used for physics-like updates.
     * 
     * @return void
     */
    public function fixedUpdate(): void {}

    /**
     * Called once per frame.
     * 
     * @return void
     */
    public function update(): void {}

    /**
     * Called once per frame, after all Update calls.
     * 
     * @return void
     */
    public function lateUpdate(): void {}

    /**
     * Called when the object becomes disabled or inactive.
     * 
     * @return void
     */
    public function onDisable(): void {}

    /**
     * Called when the behaviour is destroyed.
     * 
     * @return void
     */
    public function onDestroy(): void {}
}
