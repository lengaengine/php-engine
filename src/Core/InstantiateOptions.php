<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;

/**
 * Options used when instantiating a scene object, prefab asset reference, or
 * component source.
 *
 * Position and rotation are world-space values. When a parent is supplied
 * without an explicit position or rotation, `worldPositionStays` controls
 * whether the clone keeps the source world transform or keeps the source local
 * transform under the new parent.
 */
final class InstantiateOptions
{
    public function __construct(
        public ?string $name = null,
        public ?Vector3 $position = null,
        public ?Quaternion $rotation = null,
        public GameObject|Transform|null $parent = null,
        public bool $worldPositionStays = false,
        private bool $parentWasSpecified = false,
    ) {
        if ($this->parent !== null) {
            $this->parentWasSpecified = true;
        }
    }

    public static function named(string $name): self
    {
        return new self(name: $name);
    }

    public static function at(
        Vector3 $position,
        Vector3|Quaternion|null $rotation = null,
        GameObject|Transform|null $parent = null,
        ?string $name = null,
    ): self {
        return new self(
            name: $name,
            position: $position,
            rotation: self::normalizeRotation($rotation),
            parent: $parent,
            worldPositionStays: $parent !== null,
            parentWasSpecified: $parent !== null,
        );
    }

    public static function under(
        GameObject|Transform|null $parent,
        bool $worldPositionStays = false,
        ?string $name = null,
    ): self {
        return new self(
            name: $name,
            parent: $parent,
            worldPositionStays: $worldPositionStays,
            parentWasSpecified: true,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $parentWasSpecified = \array_key_exists('parent', $data)
            || \array_key_exists('parentGameObject', $data)
            || \array_key_exists('parentTransform', $data);
        $parent = $data['parent']
            ?? $data['parentGameObject']
            ?? $data['parentTransform']
            ?? null;

        if (!$parent instanceof GameObject && !$parent instanceof Transform && $parent !== null) {
            throw new InvalidArgumentException('Instantiate parent must be a GameObject, Transform, or null.');
        }

        $rotation = null;
        if (\array_key_exists('rotation', $data)) {
            $rotation = self::normalizeRotation($data['rotation']);
        } elseif (\array_key_exists('eulerAngles', $data)) {
            $rotation = self::normalizeRotation(self::normalizeVector3($data['eulerAngles'], 'eulerAngles'));
        }

        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            position: \array_key_exists('position', $data)
                ? self::normalizeVector3($data['position'], 'position')
                : null,
            rotation: $rotation,
            parent: $parent,
            worldPositionStays: (bool) ($data['worldPositionStays']
                ?? $data['instantiateInWorldSpace']
                ?? false),
            parentWasSpecified: $parentWasSpecified,
        );
    }

    public function withName(?string $name): self
    {
        $copy = clone $this;
        $copy->name = $name;
        return $copy;
    }

    /**
     * @return array<string, mixed>
     */
    public function toNativeArray(): array
    {
        $data = [
            'parentSpecified' => $this->parentWasSpecified,
            'worldPositionStays' => $this->worldPositionStays,
        ];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->position !== null) {
            $data['position'] = $this->position->toArray();
        }

        if ($this->rotation !== null) {
            $rotation = $this->rotation->normalized;
            $data['rotation'] = $rotation->__serialize();
        }

        if ($this->parentWasSpecified) {
            $parent = $this->parentGameObject();
            if ($parent === null) {
                $data['parentGameObjectId'] = null;
            } else {
                $parentId = $parent->getInstanceId();
                if ($parentId === null) {
                    throw new InvalidArgumentException('Instantiate parent must be a live scene GameObject or Transform.');
                }

                $data['parentGameObjectId'] = $parentId;
            }
        }

        return $data;
    }

    public function parentWasSpecified(): bool
    {
        return $this->parentWasSpecified;
    }

    private function parentGameObject(): ?GameObject
    {
        if ($this->parent instanceof GameObject) {
            return $this->parent;
        }

        return $this->parent?->gameObject;
    }

    private static function normalizeVector3(mixed $value, string $label): Vector3
    {
        if ($value instanceof Vector3) {
            return $value;
        }

        if (\is_array($value)) {
            return Vector3::fromArray($value);
        }

        throw new InvalidArgumentException("Instantiate {$label} must be a Vector3 or vector array.");
    }

    private static function normalizeRotation(mixed $value): ?Quaternion
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Quaternion) {
            return $value;
        }

        if ($value instanceof Vector3) {
            return Quaternion::fromEulerAngles($value);
        }

        if (\is_array($value)) {
            if (\array_key_exists('w', $value) || \array_key_exists(3, $value)) {
                return new Quaternion(
                    (float) ($value['x'] ?? $value[0] ?? 0.0),
                    (float) ($value['y'] ?? $value[1] ?? 0.0),
                    (float) ($value['z'] ?? $value[2] ?? 0.0),
                    (float) ($value['w'] ?? $value[3] ?? 1.0),
                );
            }

            return Quaternion::fromEulerAngles(Vector3::fromArray($value));
        }

        throw new InvalidArgumentException('Instantiate rotation must be a Quaternion, Vector3 Euler angle, or array.');
    }
}
