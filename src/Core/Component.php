<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Interfaces\ComponentInterface;

abstract class Component implements ComponentInterface
{
    public function __construct(
        protected GameObject $gameObjectValue,
        protected int $componentId,
        protected string $componentType,
    ) {
    }

    public GameObject $gameObject {
        get {
            return $this->gameObjectValue;
        }
    }

    public string $type {
        get {
            return $this->componentType;
        }
    }

    public bool $enabled {
        get {
            return \lenga_internal_component_get_enabled($this->componentId);
        }

        set(bool $value) {
            \lenga_internal_component_set_enabled($this->componentId, $value);
        }
    }

    public function getInstanceId(): int
    {
        return $this->componentId;
    }

    public function __serialize(): array
    {
        return [
            '__lengaRefKind' => 'Component',
            'componentType' => $this->componentType,
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

        if ($gameObject !== null) {
            $resolved = $componentType === 'Transform'
                ? $gameObject->transform
                : $gameObject->getComponent($componentType);

            if ($resolved instanceof self) {
                $this->gameObjectValue = $resolved->gameObject;
                $this->componentId = $resolved->getInstanceId();
                $this->componentType = $resolved->type;
                return;
            }
        }

        $this->gameObjectValue = $gameObject ?? new GameObject('GameObject');
        $this->componentId = isset($data['instanceId']) && \is_int($data['instanceId']) ? $data['instanceId'] : 0;
        $this->componentType = $componentType;
    }
}
