<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;

/**
 * Describes where to place, how to parent and what to name a copy made with
 * {@see GameObject::instantiate()}.
 *
 * Create options with {@see InstantiateOptions::at()} to spawn at a position,
 * {@see InstantiateOptions::under()} to spawn under a parent, or
 * {@see InstantiateOptions::named()} to only rename the copy, then pass them as
 * the second argument of {@see GameObject::instantiate()} or
 * {@see Prefab::instantiate()}.
 *
 * Position and rotation are in world space. When a parent is given without a
 * position or rotation, {@see InstantiateOptions::$worldPositionStays} decides
 * whether the copy keeps the source's world placement or its local transform
 * under the new parent.
 *
 * ```php
 * $spark = GameObject::instantiate(
 *     $this->hitSparkPrefab,
 *     InstantiateOptions::at($hitPoint, new Vector3(0, 45, 0), name: 'Hit Spark'),
 * );
 * ```
 */
final class InstantiateOptions
{
    /**
     * Creates instantiate options from individual values.
     *
     * The factory methods {@see InstantiateOptions::at()},
     * {@see InstantiateOptions::under()} and {@see InstantiateOptions::named()}
     * cover the common cases.
     *
     * @param string|null $name The name of the copy, or `null` to keep the engine's default name.
     * @param Vector3|null $position The world position of the copy, or `null` to keep the source's position.
     * @param Quaternion|null $rotation The world rotation of the copy, or `null` to keep the source's rotation.
     * @param GameObject|Transform|null $parent The GameObject, or its Transform, to place the copy under.
     * @param bool $worldPositionStays With a parent, `true` keeps the source's world placement and `false` keeps its local transform under the parent.
     * @param bool $parentWasSpecified Whether a parent was given. It is set automatically when `$parent` is not `null`.
     */
    public function __construct(
        /** The name of the copy, or `null` to keep the engine's default name. */
        public ?string $name = null,
        /** The world position of the copy, or `null` to keep the source's position. */
        public ?Vector3 $position = null,
        /** The world rotation of the copy, or `null` to keep the source's rotation. */
        public ?Quaternion $rotation = null,
        /** The GameObject, or its Transform, to place the copy under. */
        public GameObject|Transform|null $parent = null,
        /**
         * With a parent, whether the copy keeps the source's world placement
         * (`true`) or keeps its local transform under the new parent (`false`).
         */
        public bool $worldPositionStays = false,
        private bool $parentWasSpecified = false,
    ) {
        if ($this->parent !== null) {
            $this->parentWasSpecified = true;
        }
    }

    /**
     * Creates options that only set the name of the copy.
     *
     * @param string $name The name of the copy.
     */
    public static function named(string $name): self
    {
        return new self(name: $name);
    }

    /**
     * Creates options that place the copy at a world position, with an
     * optional rotation, parent and name.
     *
     * When a parent is given, the position and rotation stay in world space:
     * the copy ends up at `$position` whatever the parent's transform is.
     *
     * @param Vector3 $position The world position of the copy.
     * @param Vector3|Quaternion|null $rotation The world rotation, as a Quaternion or as Euler angles in degrees, or `null` to keep the source's rotation.
     * @param GameObject|Transform|null $parent The GameObject, or its Transform, to place the copy under.
     * @param string|null $name The name of the copy.
     * @throws InvalidArgumentException When `$rotation` cannot be read as a rotation.
     */
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

    /**
     * Creates options that place the copy under a parent.
     *
     * @param GameObject|Transform|null $parent The GameObject, or its Transform, to place the copy under.
     * @param bool $worldPositionStays `true` keeps the source's world placement; `false` keeps its local transform under the parent.
     * @param string|null $name The name of the copy.
     */
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
     * Creates options from an array of named values.
     *
     * {@see GameObject::instantiate()} also accepts this array directly in
     * place of an InstantiateOptions object. Recognised keys:
     *
     * - `name`: the name of the copy.
     * - `position`: a Vector3 or an array such as `['x' => 0, 'y' => 1, 'z' => 0]`.
     * - `rotation`: a Quaternion, a Vector3 of Euler angles in degrees, or an array. An array with a `w` or fourth value is read as a quaternion; otherwise it is read as Euler angles.
     * - `eulerAngles`: Euler angles in degrees, used when `rotation` is absent.
     * - `parent`, `parentGameObject` or `parentTransform`: a GameObject, a Transform or `null`.
     * - `worldPositionStays` or `instantiateInWorldSpace`: a boolean.
     *
     * @param array<string, mixed> $data The values to read.
     * @throws InvalidArgumentException When the parent, position or rotation has the wrong type.
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

    /**
     * Returns a copy of these options with a different name.
     *
     * @param string|null $name The name of the copy, or `null` to keep the engine's default name.
     */
    public function withName(?string $name): self
    {
        $copy = clone $this;
        $copy->name = $name;
        return $copy;
    }

    /**
     * Returns the options in the form the engine reads them.
     *
     * @internal Called by {@see GameObject::instantiate()} and the prefab instantiate methods.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException When the parent is not a live scene object.
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

    /**
     * Returns whether a parent was given, including an explicit `null` parent.
     *
     * @internal Bookkeeping for the options passed to the engine.
     */
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
