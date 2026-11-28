<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\HideInInspector;
use Lenga\Engine\Interfaces\ComponentInterface;

/**
 * Base class of the built-in components the engine provides, such as
 * {@see Camera}, {@see Rigidbody2D}, {@see BoxCollider3D} and the renderers.
 *
 * A component belongs to one {@see GameObject} and gives it a capability:
 * drawing, physics, a camera view and so on. You do not create components
 * with `new`. Add one with {@see GameObject::addComponent()}, find one with
 * {@see GameObject::getComponent()}, or assign one to a typed field of your
 * {@see Behaviour} in the Inspector.
 *
 * Inspector fields can also point at a component stored inside a prefab
 * asset. Pass that component to {@see GameObject::instantiate()} to clone the
 * prefab and receive the matching component on the clone.
 *
 * Your own game scripts extend {@see Behaviour}, which is a separate base
 * class with lifecycle methods.
 */
abstract class Component implements ComponentInterface
{
    /**
     * Wraps an engine component for PHP.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObjectValue GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     * @param string $componentType Engine type name of the component, for example `Rigidbody2D`.
     * @param string|null $sceneComponentId ID of the component in the saved scene, or null when it has none.
     */
    public function __construct(
        /**
         * The GameObject this component is attached to.
         *
         * @internal Storage behind {@see Component::$gameObject}. Read that property instead.
         */
        protected GameObject $gameObjectValue,
        /**
         * The engine's instance ID for this component.
         *
         * @internal Storage behind {@see Component::getInstanceId()}. Call that method instead.
         */
        protected int $componentId,
        /**
         * The engine type name of this component.
         *
         * @internal Storage behind {@see Component::$type}. Read that property instead.
         */
        protected string $componentType,
        /**
         * The ID of this component in the saved scene file.
         *
         * @internal Engine bookkeeping used to resolve saved component references.
         */
        protected ?string $sceneComponentId = null,
    ) {
    }

    /** The GameObject this component is attached to. */
    #[HideInInspector]
    public GameObject $gameObject {
        get {
            return $this->gameObjectValue;
        }
    }

    /**
     * The engine type name of this component, for example `Camera` or `Rigidbody2D`.
     */
    #[HideInInspector]
    public string $type {
        get {
            return $this->componentType;
        }
    }

    /**
     * Whether the component is switched on.
     *
     * A disabled component stays on its GameObject but stops doing its job:
     * a disabled renderer draws nothing and a disabled collider takes no
     * part in physics. Set it to true to switch the component back on.
     */
    #[HideInInspector]
    public bool $enabled {
        get {
            return NativeEngine::call('component_get_enabled', $this->componentId);
        }

        set(bool $value) {
            NativeEngine::call('component_set_enabled', $this->componentId, $value);
        }
    }

    /**
     * Returns the engine's ID for this component.
     *
     * The ID is unique among the components in the running game, so you can
     * use it to tell components apart or as an array key. It can change
     * between play sessions, so do not save it.
     *
     * @return int The component's instance ID.
     */
    public function getInstanceId(): int
    {
        return $this->componentId;
    }

    /**
     * Returns the ID this component has in the saved scene file.
     *
     * @internal Engine bookkeeping used to resolve saved component references.
     *
     * @return string|null The scene ID, or null for a component that has none.
     */
    public function getSceneComponentId(): ?string
    {
        return $this->sceneComponentId;
    }

    public function __serialize(): array
    {
        return [
            '__lengaRefKind' => 'Component',
            'componentType' => $this->componentType,
            'componentSceneId' => $this->sceneComponentId,
            'gameObject' => $this->gameObjectValue->__serialize(),
            'instanceId' => $this->componentId,
        ];
    }

    public function __unserialize(array $data): void
    {
        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);

        $componentType = isset($data['componentType']) && \is_string($data['componentType'])
            ? $data['componentType']
            : $this->componentType;
        $componentSceneId = isset($data['componentSceneId']) && \is_string($data['componentSceneId'])
            ? $data['componentSceneId']
            : null;

        if ($gameObject !== null) {
            if ($gameObject->prefabAssetPath !== '') {
                $resolvedPrefabComponent = GameObject::componentFromSerializedReference($data);
                if ($resolvedPrefabComponent instanceof self) {
                    $this->gameObjectValue = $resolvedPrefabComponent->gameObject;
                    $this->componentId = $resolvedPrefabComponent->getInstanceId();
                    $this->componentType = $resolvedPrefabComponent->type;
                    $this->sceneComponentId = \method_exists($resolvedPrefabComponent, 'getSceneComponentId')
                        ? $resolvedPrefabComponent->getSceneComponentId()
                        : $componentSceneId;
                    return;
                }
            }

            $resolved = null;
            if ($componentType === 'Transform') {
                $resolved = $gameObject->transform;
            } elseif ($componentSceneId !== null && $componentSceneId !== '') {
                foreach ($gameObject->getComponents($componentType) as $candidate) {
                    if (
                        $candidate instanceof self &&
                        \method_exists($candidate, 'getSceneComponentId') &&
                        $candidate->getSceneComponentId() === $componentSceneId
                    ) {
                        $resolved = $candidate;
                        break;
                    }
                }
            }

            if ($resolved === null) {
                $resolved = $gameObject->getComponent($componentType);
            }

            if ($resolved instanceof self) {
                $this->gameObjectValue = $resolved->gameObject;
                $this->componentId = $resolved->getInstanceId();
                $this->componentType = $resolved->type;
                $this->sceneComponentId = \method_exists($resolved, 'getSceneComponentId')
                    ? $resolved->getSceneComponentId()
                    : $componentSceneId;
                return;
            }
        }

        $this->gameObjectValue = $gameObject ?? new GameObject('GameObject');
        $this->componentId = isset($data['instanceId']) && \is_int($data['instanceId']) ? $data['instanceId'] : 0;
        $this->componentType = $componentType;
        $this->sceneComponentId = $componentSceneId;
    }
}
