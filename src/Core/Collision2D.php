<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Collision2D
{
    public function __construct(
        private ?GameObject $gameObjectValue = null,
        private ?Component $colliderValue = null,
        private ?GameObject $otherGameObjectValue = null,
        private ?Component $otherColliderValue = null,
        private ?Vector3 $pointValue = null,
        private ?Vector3 $normalValue = null,
        private ?Vector3 $relativeVelocityValue = null,
        private float $separationValue = 0.0,
        private bool $isTriggerValue = false,
    ) {
    }

    public ?GameObject $gameObject {
        get {
            return $this->gameObjectValue;
        }
    }

    public ?Component $collider {
        get {
            return $this->colliderValue;
        }
    }

    public ?GameObject $otherGameObject {
        get {
            return $this->otherGameObjectValue;
        }
    }

    public ?Component $otherCollider {
        get {
            return $this->otherColliderValue;
        }
    }

    public Vector3 $point {
        get {
            return $this->pointValue ?? new Vector3();
        }
    }

    public Vector3 $normal {
        get {
            return $this->normalValue ?? new Vector3();
        }
    }

    public Vector3 $relativeVelocity {
        get {
            return $this->relativeVelocityValue ?? new Vector3();
        }
    }

    public float $separation {
        get {
            return $this->separationValue;
        }
    }

    public bool $isTrigger {
        get {
            return $this->isTriggerValue;
        }
    }

    /**
     * @param array{
     *     gameObject?: array<string, mixed>,
     *     collider?: mixed,
     *     otherGameObject?: array<string, mixed>,
     *     otherCollider?: mixed,
     *     point?: array{x?: float|int, y?: float|int, z?: float|int},
     *     normal?: array{x?: float|int, y?: float|int, z?: float|int},
     *     relativeVelocity?: array{x?: float|int, y?: float|int, z?: float|int},
     *     separation?: float|int,
     *     isTrigger?: bool
     * } $data
     */
    public static function fromNativeData(array $data): self
    {
        $gameObject = \is_array($data['gameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['gameObject'])
            : null;

        $otherGameObject = \is_array($data['otherGameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['otherGameObject'])
            : null;

        $collider = GameObject::wrapNativeComponentLookupData($data['collider'] ?? null);
        $otherCollider = GameObject::wrapNativeComponentLookupData($data['otherCollider'] ?? null);

        return new self(
            $gameObject,
            $collider instanceof Component ? $collider : null,
            $otherGameObject,
            $otherCollider instanceof Component ? $otherCollider : null,
            self::vectorFromArray($data['point'] ?? null),
            self::vectorFromArray($data['normal'] ?? null),
            self::vectorFromArray($data['relativeVelocity'] ?? null),
            (float) ($data['separation'] ?? 0.0),
            (bool) ($data['isTrigger'] ?? false),
        );
    }

    public function __serialize(): array
    {
        return [
            'gameObject' => $this->gameObjectValue?->__serialize(),
            'collider' => $this->colliderValue?->__serialize(),
            'otherGameObject' => $this->otherGameObjectValue?->__serialize(),
            'otherCollider' => $this->otherColliderValue?->__serialize(),
            'point' => self::vectorToArray($this->pointValue),
            'normal' => self::vectorToArray($this->normalValue),
            'relativeVelocity' => self::vectorToArray($this->relativeVelocityValue),
            'separation' => $this->separationValue,
            'isTrigger' => $this->isTriggerValue,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->gameObjectValue = \is_array($data['gameObject'] ?? null)
            ? GameObject::fromSerializedReference($data['gameObject'])
            : null;
        $this->otherGameObjectValue = \is_array($data['otherGameObject'] ?? null)
            ? GameObject::fromSerializedReference($data['otherGameObject'])
            : null;
        $this->colliderValue = self::componentFromSerializedData($data['collider'] ?? null);
        $this->otherColliderValue = self::componentFromSerializedData($data['otherCollider'] ?? null);
        $this->pointValue = self::vectorFromArray($data['point'] ?? null);
        $this->normalValue = self::vectorFromArray($data['normal'] ?? null);
        $this->relativeVelocityValue = self::vectorFromArray($data['relativeVelocity'] ?? null);
        $this->separationValue = (float) ($data['separation'] ?? 0.0);
        $this->isTriggerValue = (bool) ($data['isTrigger'] ?? false);
    }

    private static function componentFromSerializedData(mixed $data): ?Component
    {
        if (!\is_array($data)) {
            return null;
        }

        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);
        if (!$gameObject instanceof GameObject) {
            return null;
        }

        $componentType = isset($data['componentType']) && \is_string($data['componentType'])
            ? $data['componentType']
            : '';
        if ($componentType === '') {
            return null;
        }

        $component = $componentType === 'Transform'
            ? $gameObject->transform
            : $gameObject->getComponent($componentType);

        return $component instanceof Component ? $component : null;
    }

    /**
     * @param array{x?: float|int, y?: float|int, z?: float|int}|mixed $data
     */
    private static function vectorFromArray(mixed $data): ?Vector3
    {
        if (!\is_array($data)) {
            return null;
        }

        return new Vector3(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    /**
     * @return array{x: float, y: float, z: float}|null
     */
    private static function vectorToArray(?Vector3 $value): ?array
    {
        if (!$value instanceof Vector3) {
            return null;
        }

        return [
            'x' => $value->x,
            'y' => $value->y,
            'z' => $value->z,
        ];
    }
}
