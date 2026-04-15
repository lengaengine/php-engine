<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Transform
{
    public function __construct(
        ?Transform $parent = null,
        ?Vector3 $localPosition = null,
        ?Vector3 $localEulerAngles = null,
        ?Vector3 $localScale = null,
        ?int $nativeId = null,
        ?int $gameObjectId = null,
    ) {
        $this->nativeId = $nativeId;
        $this->gameObjectId = $gameObjectId;
        $this->parentValue = $parent;
        $this->localPositionValue = $localPosition ?? new Vector3(0.0, 0.0, 0.0);
        $this->localEulerAnglesValue = $localEulerAngles ?? new Vector3(0.0, 0.0, 0.0);
        $this->localScaleValue = $localScale ?? new Vector3(1.0, 1.0, 1.0);
        $this->worldPositionCache = new Vector3(
            $this->localPositionValue->x,
            $this->localPositionValue->y,
            $this->localPositionValue->z,
        );

        if ($this->nativeId === null && $parent !== null) {
            $parent->addChild($this);
        }
    }

    private ?Transform $parentValue = null;
    private ?GameObject $gameObjectValue = null;
    private ?int $nativeId = null;
    private ?int $gameObjectId = null;

    /** @var list<Transform> */
    private array $children = [];

    private Vector3 $localPositionValue;
    private Vector3 $localEulerAnglesValue;
    private Vector3 $localScaleValue;
    private Vector3 $worldPositionCache;

    public GameObject $gameObject {
        get {
            if ($this->gameObjectValue !== null) {
                return $this->gameObjectValue;
            }

            if ($this->nativeId !== null) {
                /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
                $data = \lenga_internal_transform_get_game_object_by_id($this->nativeId);
                if (\is_array($data)) {
                    $this->gameObjectValue = GameObject::fromNativeLookupData($data, $this);
                    $this->gameObjectId = $this->gameObjectValue->getInstanceId();
                    return $this->gameObjectValue;
                }
            }

            $this->gameObjectValue = new GameObject('GameObject', $this, true, $this->gameObjectId);
            return $this->gameObjectValue;
        }
    }

    public ?Transform $parent {
        get {
            return $this->getParent();
        }

        set(?Transform $value) {
            $this->setParent($value);
        }
    }

    public Transform $root {
        get {
            $current = $this;
            while (($parent = $current->getParent()) !== null) {
                $current = $parent;
            }

            return $current;
        }
    }

    public Vector3 $localPosition {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('lenga_internal_transform_get_local_position3d_by_id');
            }

            return $this->localPositionValue;
        }

        set(Vector3 $value) {
            $this->localPositionValue = $value;

            if ($this->nativeId !== null) {
                \lenga_internal_transform_set_local_position3d_by_id(
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    public Vector3 $localEulerAngles {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('lenga_internal_transform_get_local_euler_angles3d_by_id');
            }

            return $this->localEulerAnglesValue;
        }

        set(Vector3 $value) {
            $this->localEulerAnglesValue = $value;

            if ($this->nativeId !== null) {
                \lenga_internal_transform_set_local_euler_angles3d_by_id(
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    public Vector3 $localScale {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('lenga_internal_transform_get_local_scale3d_by_id');
            }

            return $this->localScaleValue;
        }

        set(Vector3 $value) {
            $this->localScaleValue = $value;

            if ($this->nativeId !== null) {
                \lenga_internal_transform_set_local_scale3d_by_id(
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    public Vector3 $position {
        get {
            return $this->nativeId !== null
                ? $this->fetchVectorFromNative('lenga_internal_transform_get_position3d_by_id')
                : $this->fetchPositionFromFallback();
        }

        set(Vector3 $value) {
            if ($this->nativeId !== null) {
                \lenga_internal_transform_set_position3d_by_id(
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
                return;
            }

            if ($this->parentValue === null) {
                $this->localPositionValue = $value;
            } else {
                $this->localPositionValue = Vector3::difference($value, $this->parentValue->position);
            }
        }
    }

    public Vector3 $eulerAngles {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('lenga_internal_transform_get_euler_angles3d_by_id');
            }

            if ($this->parentValue === null) {
                return $this->localEulerAnglesValue;
            }

            return Vector3::sum($this->parentValue->eulerAngles, $this->localEulerAnglesValue);
        }

        set(Vector3 $value) {
            if ($this->nativeId !== null) {
                \lenga_internal_transform_set_euler_angles3d_by_id(
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
                return;
            }

            if ($this->parentValue === null) {
                $this->localEulerAnglesValue = $value;
            } else {
                $this->localEulerAnglesValue = Vector3::difference($value, $this->parentValue->eulerAngles);
            }
        }
    }

    public Vector3 $scale {
        get {
            return $this->localScale;
        }

        set(Vector3 $value) {
            $this->localScale = $value;
        }
    }

    public Vector3 $lossyScale {
        get {
            return $this->nativeId !== null
                ? $this->fetchVectorFromNative('lenga_internal_transform_get_scale3d_by_id')
                : $this->fetchWorldScaleFromFallback();
        }
    }

    public Vector3 $right {
        get {
            return self::basisRightByEuler($this->eulerAngles);
        }
    }

    public Vector3 $up {
        get {
            return self::basisUpByEuler($this->eulerAngles);
        }
    }

    public Vector3 $forward {
        get {
            return self::basisForwardByEuler($this->eulerAngles);
        }
    }

    public function __internalAttachGameObject(GameObject $gameObject, ?int $gameObjectId = null): void
    {
        $this->gameObjectValue = $gameObject;
        $this->gameObjectId = $gameObjectId ?? $gameObject->getInstanceId();
    }

    public function getParent(): ?Transform
    {
        if ($this->nativeId !== null) {
            /** @var int|null $parentId */
            $parentId = \lenga_internal_transform_get_parent_by_id($this->nativeId);

            return \is_int($parentId) ? new self(null, null, null, null, $parentId) : null;
        }

        return $this->parentValue;
    }

    public function setParent(?Transform $newParent, bool $worldPositionStays = true): void
    {
        if ($this->nativeId !== null) {
            \lenga_internal_transform_set_parent_by_id(
                $this->nativeId,
                $newParent?->nativeId,
                $worldPositionStays,
            );
            return;
        }

        if ($newParent === $this->parentValue) {
            return;
        }

        $worldPosition = $this->position;
        $worldEuler = $this->eulerAngles;
        $worldScale = $this->lossyScale;

        if ($this->parentValue !== null) {
            $this->parentValue->removeChild($this);
        }

        $this->parentValue = $newParent;

        if ($newParent !== null) {
            $newParent->addChild($this);
        }

        if ($worldPositionStays) {
            $this->position = $worldPosition;
            $this->eulerAngles = $worldEuler;
            $this->setWorldScaleFromFallback($worldScale);
        }
    }

    public function addChild(Transform $child): void
    {
        if ($this->nativeId !== null) {
            $child->setParent($this);
            return;
        }

        if ($child === $this) {
            return;
        }

        if ($child->parentValue !== null && $child->parentValue !== $this) {
            $child->parentValue->removeChild($child);
        }

        $this->children[] = $child;
        $child->parentValue = $this;
    }

    public function removeChild(Transform $child): void
    {
        if ($this->nativeId !== null) {
            if ($child->getParent()?->nativeId === $this->nativeId) {
                $child->setParent(null);
            }
            return;
        }

        foreach ($this->children as $i => $candidate) {
            if ($candidate === $child) {
                unset($this->children[$i]);
                $this->children = array_values($this->children);
                $child->parentValue = null;
                return;
            }
        }
    }

    /**
     * @return list<Transform>
     */
    public function getChildren(): array
    {
        if ($this->nativeId !== null) {
            /** @var list<int>|false $children */
            $children = \lenga_internal_transform_get_children_by_id($this->nativeId);
            if (!\is_array($children)) {
                return [];
            }

            return array_values(array_map(
                static fn (int $childId): Transform => new Transform(null, null, null, null, $childId),
                $children,
            ));
        }

        return $this->children;
    }

    public function childCount(): int
    {
        return \count($this->getChildren());
    }

    public function getChild(int $index): ?Transform
    {
        return $this->getChildren()[$index] ?? null;
    }

    public function find(string $path): ?Transform
    {
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
        if ($segments === []) {
            return null;
        }

        $current = $this;
        foreach ($segments as $segment) {
            $next = null;
            foreach ($current->getChildren() as $child) {
                if ($child->gameObject->name === $segment) {
                    $next = $child;
                    break;
                }
            }

            if ($next === null) {
                return null;
            }

            $current = $next;
        }

        return $current;
    }

    public function isChildOf(Transform $parent): bool
    {
        $current = $this->getParent();
        while ($current !== null) {
            if ($current->equals($parent)) {
                return true;
            }

            $current = $current->getParent();
        }

        return false;
    }

    public function detachChildren(): void
    {
        foreach ($this->getChildren() as $child) {
            $child->setParent(null, true);
        }
    }

    public function translate(Vector3 $offset, bool $relativeToSelf = true): void
    {
        $delta = $relativeToSelf ? self::transformDirectionByEuler($offset, $this->eulerAngles) : $offset;

        if ($this->nativeId !== null) {
            \lenga_internal_transform_translate3d_by_id(
                $this->nativeId,
                $delta->x,
                $delta->y,
                $delta->z,
            );
            return;
        }

        if ($relativeToSelf) {
            $this->localPositionValue->add($delta);
            return;
        }

        $this->position = Vector3::sum($this->position, $delta);
    }

    public function translate2D(Vector2 $offset, bool $relativeToSelf = true): void
    {
        $this->translate(new Vector3($offset->x, $offset->y, 0.0), $relativeToSelf);
    }

    public function rotate(Vector3|float $xOrEuler, ?float $y = null, ?float $z = null, bool $relativeToSelf = true): void
    {
        $delta = $xOrEuler instanceof Vector3
            ? $xOrEuler
            : new Vector3((float) $xOrEuler, (float) ($y ?? 0.0), (float) ($z ?? 0.0));

        if ($relativeToSelf) {
            $this->localEulerAngles = Vector3::sum($this->localEulerAngles, $delta);
            return;
        }

        $this->eulerAngles = Vector3::sum($this->eulerAngles, $delta);
    }

    public function rotate2D(float $degrees, bool $relativeToSelf = true): void
    {
        $this->rotate(0.0, 0.0, $degrees, $relativeToSelf);
    }

    public function lookAt(Vector3 $worldPosition): void
    {
        if ($this->nativeId !== null) {
            \lenga_internal_transform_look_at_by_id(
                $this->nativeId,
                $worldPosition->x,
                $worldPosition->y,
                $worldPosition->z,
            );
            return;
        }

        $direction = Vector3::difference($worldPosition, $this->position)->normalized;
        if ($direction->sqrMagnitude <= 0.000001) {
            return;
        }

        $pitch = rad2deg(asin(max(-1.0, min(1.0, $direction->y))));
        $yaw = rad2deg(atan2($direction->x, -$direction->z));
        $this->eulerAngles = new Vector3($pitch, $yaw, 0.0);
    }

    public function __serialize(): array
    {
        return [
            '__lengaRefKind' => 'Transform',
            'gameObject' => $this->gameObject->__serialize(),
            'nativeId' => $this->nativeId,
        ];
    }

    public function __unserialize(array $data): void
    {
        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);
        if ($gameObject !== null) {
            $resolved = $gameObject->transform;
            $this->parentValue = $resolved->getParent();
            $this->gameObjectValue = $gameObject;
            $this->nativeId = $resolved->nativeId;
            $this->gameObjectId = $resolved->gameObjectId;
            $this->children = [];
            $this->localPositionValue = $resolved->localPosition;
            $this->localEulerAnglesValue = $resolved->localEulerAngles;
            $this->localScaleValue = $resolved->localScale;
            $this->worldPositionCache = $resolved->position;
            return;
        }

        $this->parentValue = null;
        $this->gameObjectValue = null;
        $this->nativeId = isset($data['nativeId']) && \is_int($data['nativeId']) ? $data['nativeId'] : null;
        $this->gameObjectId = null;
        $this->children = [];
        $this->localPositionValue = new Vector3(0.0, 0.0, 0.0);
        $this->localEulerAnglesValue = new Vector3(0.0, 0.0, 0.0);
        $this->localScaleValue = new Vector3(1.0, 1.0, 1.0);
        $this->worldPositionCache = new Vector3(0.0, 0.0, 0.0);
    }

    public function getNativeId(): ?int
    {
        return $this->nativeId;
    }

    private function equals(Transform $other): bool
    {
        if ($this->nativeId !== null && $other->nativeId !== null) {
            return $this->nativeId === $other->nativeId;
        }

        return $this === $other;
    }

    private function fetchPositionFromFallback(): Vector3
    {
        if ($this->parentValue === null) {
            return $this->localPositionValue;
        }

        return Vector3::sum($this->parentValue->position, $this->localPositionValue);
    }

    private function fetchWorldScaleFromFallback(): Vector3
    {
        if ($this->parentValue === null) {
            return $this->localScaleValue;
        }

        return Vector3::product($this->parentValue->lossyScale, $this->localScaleValue);
    }

    private function setWorldScaleFromFallback(Vector3 $value): void
    {
        if ($this->parentValue === null) {
            $this->localScaleValue = $value;
            return;
        }

        $parentScale = $this->parentValue->lossyScale;
        $this->localScaleValue = new Vector3(
            $parentScale->x != 0.0 ? $value->x / $parentScale->x : $value->x,
            $parentScale->y != 0.0 ? $value->y / $parentScale->y : $value->y,
            $parentScale->z != 0.0 ? $value->z / $parentScale->z : $value->z,
        );
    }

    private function fetchVectorFromNative(string $getter): Vector3
    {
        if ($this->nativeId === null) {
            return new Vector3();
        }

        $callable = '\\' . $getter;

        /** @var array{x?: float, y?: float, z?: float}|false $data */
        $data = $callable($this->nativeId);
        if (!\is_array($data)) {
            return new Vector3();
        }

        return new Vector3(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    private static function rotateVectorByEuler(Vector3 $vector, Vector3 $eulerAngles): Vector3
    {
        $xRadians = deg2rad($eulerAngles->x);
        $yRadians = deg2rad($eulerAngles->y);
        $zRadians = deg2rad($eulerAngles->z);

        $x1 = $vector->x;
        $y1 = $vector->y * cos($xRadians) - $vector->z * sin($xRadians);
        $z1 = $vector->y * sin($xRadians) + $vector->z * cos($xRadians);

        $x2 = $x1 * cos($yRadians) + $z1 * sin($yRadians);
        $y2 = $y1;
        $z2 = -$x1 * sin($yRadians) + $z1 * cos($yRadians);

        return new Vector3(
            $x2 * cos($zRadians) - $y2 * sin($zRadians),
            $x2 * sin($zRadians) + $y2 * cos($zRadians),
            $z2,
        );
    }

    private static function basisRightByEuler(Vector3 $eulerAngles): Vector3
    {
        return self::rotateVectorByEuler(new Vector3(1.0, 0.0, 0.0), $eulerAngles)->normalized;
    }

    private static function basisUpByEuler(Vector3 $eulerAngles): Vector3
    {
        return self::rotateVectorByEuler(new Vector3(0.0, 1.0, 0.0), $eulerAngles)->normalized;
    }

    private static function basisForwardByEuler(Vector3 $eulerAngles): Vector3
    {
        return self::rotateVectorByEuler(new Vector3(0.0, 0.0, -1.0), $eulerAngles)->normalized;
    }

    private static function transformDirectionByEuler(Vector3 $vector, Vector3 $eulerAngles): Vector3
    {
        $right = self::basisRightByEuler($eulerAngles);
        $up = self::basisUpByEuler($eulerAngles);
        $forward = self::basisForwardByEuler($eulerAngles);

        return new Vector3(
            ($right->x * $vector->x) + ($up->x * $vector->y) + ($forward->x * $vector->z),
            ($right->y * $vector->x) + ($up->y * $vector->y) + ($forward->y * $vector->z),
            ($right->z * $vector->x) + ($up->z * $vector->y) + ($forward->z * $vector->z),
        );
    }
}
