<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Position, rotation and scale of a GameObject, and its place in the scene hierarchy.
 *
 * Every GameObject has a Transform. Reach it from a script with `$this->transform`
 * or {@see GameObject::$transform}. A Transform can have a parent: moving, rotating or
 * scaling the parent moves, rotates and scales its children with it.
 *
 * Each value comes in two spaces:
 *
 * - Local values ({@see Transform::$localPosition}, {@see Transform::$localRotation},
 *   {@see Transform::$localScale}) are relative to the parent. They are the values the
 *   Inspector shows.
 * - World values ({@see Transform::$position}, {@see Transform::$rotation},
 *   {@see Transform::$lossyScale}) are the final placement in the scene.
 *
 * Without a parent, local and world values are the same. Positions are in world units
 * and Euler angles are in degrees. In 2D scenes you mostly change position X and Y,
 * rotation Z and scale X and Y.
 *
 * Reading a vector or rotation property returns a copy. Changing a component of that
 * copy does not move the object, so assign a whole new value:
 *
 * ```php
 * // Does not move the object:
 * $this->transform->position->x += 1.0;
 *
 * // Moves the object:
 * $position = $this->transform->position;
 * $this->transform->position = new Vector3($position->x + 1.0, $position->y, $position->z);
 * ```
 *
 * A {@see Rigidbody2D} or {@see Rigidbody3D} on the same GameObject owns its motion
 * during play. Move such objects through the rigidbody instead of the Transform.
 *
 * @example examples/Core/Transform.php Patrol between waypoints, face the direction of travel and spin a child
 */
final class Transform
{
    /**
     * Creates a Transform.
     *
     * @internal The engine creates a Transform for every GameObject. Scripts use
     *     `$this->transform` or {@see GameObject::$transform}.
     *
     * @param Transform|null $parent Parent Transform, or `null` for none.
     * @param Vector3|null $localPosition Starting local position; `null` means `(0, 0, 0)`.
     * @param Vector3|null $localEulerAngles Starting local rotation in degrees; `null` means `(0, 0, 0)`.
     * @param Vector3|null $localScale Starting local scale; `null` means `(1, 1, 1)`.
     * @param int|null $nativeId Instance ID of the engine Transform this object wraps.
     * @param int|null $gameObjectId Instance ID of the GameObject that owns this Transform.
     */
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
        $this->localRotationValue = Quaternion::fromEulerAngles($this->localEulerAnglesValue);
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
    private Quaternion $localRotationValue;
    private Vector3 $localScaleValue;
    private Vector3 $worldPositionCache;

    /**
     * The GameObject this Transform belongs to.
     */
    public GameObject $gameObject {
        get {
            if ($this->gameObjectValue !== null) {
                return $this->gameObjectValue;
            }

            if ($this->nativeId !== null) {
                /** @var array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null}|false $data */
                $data = NativeEngine::call('transform_get_game_object_by_id', $this->nativeId);
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

    /**
     * Parent Transform, or `null` when the GameObject is at the top of the scene hierarchy.
     *
     * Assigning a parent works like {@see Transform::setParent()} with
     * `$worldPositionStays` set to `true`, so the object stays where it is in the world.
     * Assign `null` to move it to the top level.
     */
    public ?Transform $parent {
        get {
            return $this->getParent();
        }

        set(?Transform $value) {
            $this->setParent($value);
        }
    }

    /**
     * Topmost Transform in this object's hierarchy.
     *
     * Returns this Transform when it has no parent.
     */
    public Transform $root {
        get {
            $current = $this;
            while (($parent = $current->getParent()) !== null) {
                $current = $parent;
            }

            return $current;
        }
    }

    /**
     * Position relative to the parent, in the parent's local units.
     *
     * Equals {@see Transform::$position} when there is no parent. This is the position the
     * Inspector shows. Reading it returns a copy, so assign a whole Vector3 to change it.
     */
    public Vector3 $localPosition {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('transform_get_local_position3d_by_id');
            }

            return $this->localPositionValue;
        }

        set(Vector3 $value) {
            $this->localPositionValue = $value;

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_local_position3d_by_id',
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    /**
     * Rotation relative to the parent, as X, Y and Z angles in degrees.
     *
     * This is the rotation the Inspector shows. Use it for readable angle edits, and
     * {@see Transform::$localRotation} for composing rotations in code. Reading it returns
     * a copy, so assign a whole Vector3 to change it.
     */
    public Vector3 $localEulerAngles {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('transform_get_local_euler_angles3d_by_id');
            }

            return $this->localEulerAnglesValue;
        }

        set(Vector3 $value) {
            $this->localEulerAnglesValue = $value;
            $this->localRotationValue = Quaternion::fromEulerAngles($value);

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_local_euler_angles3d_by_id',
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    /**
     * Rotation relative to the parent, as a quaternion.
     *
     * Assigned values are normalized. Use quaternions to combine rotations or turn
     * smoothly between orientations in 3D.
     */
    public Quaternion $localRotation {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchQuaternionFromNative('transform_get_local_rotation3d_by_id');
            }

            return $this->localRotationValue;
        }

        set(Quaternion $value) {
            $rotation = $value->normalized;
            $this->localRotationValue = $rotation;
            $this->localEulerAnglesValue = $rotation->toEulerAngles();

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_local_rotation3d_by_id',
                    $this->nativeId,
                    $rotation->x,
                    $rotation->y,
                    $rotation->z,
                    $rotation->w,
                );
            }
        }
    }

    /**
     * Scale relative to the parent.
     *
     * `(1, 1, 1)` is the original size and `(2, 2, 2)` is twice as large. A negative
     * value mirrors the object on that axis. Reading it returns a copy, so assign a whole
     * Vector3 to change it.
     */
    public Vector3 $localScale {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('transform_get_local_scale3d_by_id');
            }

            return $this->localScaleValue;
        }

        set(Vector3 $value) {
            $this->localScaleValue = $value;

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_local_scale3d_by_id',
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
            }
        }
    }

    /**
     * Position in world space, in world units.
     *
     * Assigning it moves the object to that world position and updates
     * {@see Transform::$localPosition} to match under the current parent. Reading it
     * returns a copy, so assign a whole Vector3 to change it.
     */
    public Vector3 $position {
        get {
            return $this->nativeId !== null
                ? $this->fetchVectorFromNative('transform_get_position3d_by_id')
                : $this->fetchPositionFromFallback();
        }

        set(Vector3 $value) {
            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_position3d_by_id',
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
                $parentOffset = Vector3::difference($value, $this->parentValue->position);
                $unrotatedOffset = $this->parentValue->rotation->inverse()->rotateVector($parentOffset);
                $parentScale = $this->parentValue->lossyScale;
                $this->localPositionValue = new Vector3(
                    \abs($parentScale->x) > 0.000001 ? $unrotatedOffset->x / $parentScale->x : $unrotatedOffset->x,
                    \abs($parentScale->y) > 0.000001 ? $unrotatedOffset->y / $parentScale->y : $unrotatedOffset->y,
                    \abs($parentScale->z) > 0.000001 ? $unrotatedOffset->z / $parentScale->z : $unrotatedOffset->z,
                );
            }
        }
    }

    /**
     * Rotation in world space, as X, Y and Z angles in degrees.
     *
     * Reading it returns a copy, so assign a whole Vector3 to change it. Use
     * {@see Transform::$rotation} when combining rotations in code.
     */
    public Vector3 $eulerAngles {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchVectorFromNative('transform_get_euler_angles3d_by_id');
            }

            if ($this->parentValue === null) {
                return $this->localEulerAnglesValue;
            }

            return $this->rotation->toEulerAngles();
        }

        set(Vector3 $value) {
            $rotation = Quaternion::fromEulerAngles($value);

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_euler_angles3d_by_id',
                    $this->nativeId,
                    $value->x,
                    $value->y,
                    $value->z,
                );
                return;
            }

            if ($this->parentValue === null) {
                $this->localEulerAnglesValue = $value;
                $this->localRotationValue = $rotation;
            } else {
                $this->localRotationValue = $this->parentValue->rotation->inverse()->multiply($rotation)->normalized;
                $this->localEulerAnglesValue = $this->localRotationValue->toEulerAngles();
            }
        }
    }

    /**
     * Rotation in world space, as a quaternion.
     *
     * Assigned values are normalized, and {@see Transform::$localRotation} is updated to
     * match under the current parent.
     */
    public Quaternion $rotation {
        get {
            if ($this->nativeId !== null) {
                return $this->fetchQuaternionFromNative('transform_get_rotation3d_by_id');
            }

            if ($this->parentValue === null) {
                return $this->localRotationValue;
            }

            return $this->parentValue->rotation->multiply($this->localRotationValue)->normalized;
        }

        set(Quaternion $value) {
            $rotation = $value->normalized;
            $this->localRotationValue = $rotation;
            $this->localEulerAnglesValue = $rotation->toEulerAngles();

            if ($this->nativeId !== null) {
                NativeEngine::call('transform_set_rotation3d_by_id',
                    $this->nativeId,
                    $rotation->x,
                    $rotation->y,
                    $rotation->z,
                    $rotation->w,
                );
                return;
            }

            if ($this->parentValue === null) {
                $this->localRotationValue = $rotation;
            } else {
                $this->localRotationValue = $this->parentValue->rotation->inverse()->multiply($rotation)->normalized;
            }

            $this->localEulerAnglesValue = $this->localRotationValue->toEulerAngles();
        }
    }

    /**
     * Scale relative to the parent. Same as {@see Transform::$localScale}.
     *
     * Use {@see Transform::$lossyScale} to read the final scale in world space.
     */
    public Vector3 $scale {
        get {
            return $this->localScale;
        }

        set(Vector3 $value) {
            $this->localScale = $value;
        }
    }

    /**
     * Final scale in world space, after the scale of every parent is applied. Read-only.
     *
     * Change the scale with {@see Transform::$localScale}.
     */
    public Vector3 $lossyScale {
        get {
            return $this->nativeId !== null
                ? $this->fetchVectorFromNative('transform_get_scale3d_by_id')
                : $this->fetchWorldScaleFromFallback();
        }
    }

    /**
     * Unit vector pointing along this object's local +X axis, in world space. Read-only.
     */
    public Vector3 $right {
        get {
            return $this->rotation->rotateVector(Vector3::right())->normalized;
        }
    }

    /**
     * Unit vector pointing along this object's local +Y axis, in world space. Read-only.
     */
    public Vector3 $up {
        get {
            return $this->rotation->rotateVector(Vector3::up())->normalized;
        }
    }

    /**
     * Unit vector pointing the way this object faces, in world space. Read-only.
     *
     * Lenga objects face their local -Z axis, so this is the local `(0, 0, -1)` direction
     * rotated by {@see Transform::$rotation}. {@see Transform::lookAt()} turns an object so
     * this vector points at a target.
     */
    public Vector3 $forward {
        get {
            return $this->rotation->rotateVector(Vector3::back())->normalized;
        }
    }

    /**
     * Returns the parent Transform, or `null` when there is none.
     *
     * Same as reading {@see Transform::$parent}. Each call can return a new Transform
     * object for the same parent, so use {@see Transform::isChildOf()} to compare
     * hierarchy relationships instead of `===`.
     */
    public function getParent(): ?Transform
    {
        if ($this->nativeId !== null) {
            /** @var int|null $parentId */
            $parentId = NativeEngine::call('transform_get_parent_by_id', $this->nativeId);

            return \is_int($parentId) ? new self(null, null, null, null, $parentId) : null;
        }

        return $this->parentValue;
    }

    /**
     * Moves this Transform under a new parent.
     *
     * @param Transform|null $newParent New parent, or `null` to move the object to the top
     *     of the hierarchy.
     * @param bool $worldPositionStays When `true`, the object keeps its world position,
     *     rotation and scale, and its local values are recalculated for the new parent.
     *     When `false`, it keeps its local values, so it moves along with the new parent.
     */
    public function setParent(?Transform $newParent, bool $worldPositionStays = true): void
    {
        if ($this->nativeId !== null) {
            NativeEngine::call('transform_set_parent_by_id',
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
        $worldRotation = $this->rotation;
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
            $this->rotation = $worldRotation;
            $this->setWorldScaleFromFallback($worldScale);
        }
    }

    /**
     * Makes another Transform a child of this one.
     *
     * The child keeps its world position, rotation and scale. Same as calling
     * `$child->setParent($this)`.
     *
     * @param Transform $child Transform to attach under this one.
     */
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

    /**
     * Detaches a child and moves it to the top of the hierarchy.
     *
     * The child keeps its world position. Does nothing when `$child` is not a direct
     * child of this Transform.
     *
     * @param Transform $child Child Transform to detach.
     */
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
    /**
     * Returns the direct children of this Transform.
     *
     * Grandchildren are not included. Use {@see Transform::find()} to reach a deeper child
     * by name.
     *
     * @return list<Transform> The child Transforms, or an empty list when there are none.
     */
    public function getChildren(): array
    {
        if ($this->nativeId !== null) {
            /** @var list<int>|false $children */
            $children = NativeEngine::call('transform_get_children_by_id', $this->nativeId);
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

    /**
     * Returns the number of direct children.
     */
    public function childCount(): int
    {
        return \count($this->getChildren());
    }

    /**
     * Returns a direct child by its position in the child list.
     *
     * @param int $index Index from `0` to {@see Transform::childCount()} minus one.
     * @return Transform|null The child, or `null` when the index is out of range.
     */
    public function getChild(int $index): ?Transform
    {
        return $this->getChildren()[$index] ?? null;
    }

    /**
     * Finds a descendant by the names of the GameObjects on the way to it.
     *
     * Each part of the path names a direct child of the previous part, starting from this
     * Transform's children. For example, `Weapon/Muzzle` finds the child named `Muzzle`
     * inside the child named `Weapon`. When several children share a name, the first one
     * is used.
     *
     * @param string $path Child names separated by `/`.
     * @return Transform|null The Transform at the end of the path, or `null` when any part
     *     is missing or the path is empty.
     */
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

    /**
     * Returns whether this Transform is anywhere below another Transform in the hierarchy.
     *
     * Checks the parent, the parent's parent and so on, so it also returns `true` for
     * grandchildren. A Transform is not a child of itself.
     *
     * @param Transform $parent Transform to look for above this one.
     */
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

    /**
     * Moves every direct child to the top of the hierarchy.
     *
     * Each child keeps its world position, rotation and scale.
     */
    public function detachChildren(): void
    {
        foreach ($this->getChildren() as $child) {
            $child->setParent(null, true);
        }
    }

    /**
     * Moves the object by an offset.
     *
     * Multiply the offset by {@see Time::deltaTime()} to move at a steady speed in `update()`.
     *
     * @param Vector3 $offset Distance to move, in world units.
     * @param bool $relativeToSelf When `true`, the offset follows the object's own axes, so
     *     `(0, 0, -1)` moves it one unit forward whichever way it faces. When `false`, the
     *     offset follows the world axes.
     */
    public function translate(Vector3 $offset, bool $relativeToSelf = true): void
    {
        if ($this->nativeId !== null) {
            if ($relativeToSelf) {
                NativeEngine::call('transform_translate3d_by_id',
                    $this->nativeId,
                    $offset->x,
                    $offset->y,
                    $offset->z,
                );
            } else {
                $targetPosition = Vector3::sum($this->position, $offset);
                NativeEngine::call('transform_set_position3d_by_id',
                    $this->nativeId,
                    $targetPosition->x,
                    $targetPosition->y,
                    $targetPosition->z,
                );
            }
            return;
        }

        if ($relativeToSelf) {
            $this->localPositionValue->add($offset);
            return;
        }

        $this->position = Vector3::sum($this->position, $offset);
    }

    /**
     * Moves the object by a 2D offset on the X and Y axes.
     *
     * Same as {@see Transform::translate()} with a Z offset of `0`.
     *
     * @param Vector2 $offset Distance to move on X and Y, in world units.
     * @param bool $relativeToSelf When `true`, the offset follows the object's own axes.
     *     When `false`, it follows the world axes.
     */
    public function translate2D(Vector2 $offset, bool $relativeToSelf = true): void
    {
        $this->translate(new Vector3($offset->x, $offset->y, 0.0), $relativeToSelf);
    }

    /**
     * Rotates the object by Euler angles in degrees.
     *
     * Pass either one Vector3 of angles or separate X, Y and Z angles. Multiply the angles
     * by {@see Time::deltaTime()} to spin at a steady rate in `update()`.
     *
     * ```php
     * // Spin 90 degrees per second around the Y axis.
     * $this->transform->rotate(0.0, 90.0 * Time::deltaTime(), 0.0);
     * ```
     *
     * @param Vector3|float $xOrEuler All three angles as a Vector3, or the X angle in degrees.
     * @param float|null $y Y angle in degrees when `$xOrEuler` is a float; `null` means `0`.
     *     Ignored when `$xOrEuler` is a Vector3.
     * @param float|null $z Z angle in degrees when `$xOrEuler` is a float; `null` means `0`.
     *     Ignored when `$xOrEuler` is a Vector3.
     * @param bool $relativeToSelf When `true`, the rotation turns the object around its own
     *     axes. When `false`, it turns around the world axes.
     */
    public function rotate(Vector3|float $xOrEuler, ?float $y = null, ?float $z = null, bool $relativeToSelf = true): void
    {
        $delta = $xOrEuler instanceof Vector3
            ? $xOrEuler
            : new Vector3((float) $xOrEuler, (float) ($y ?? 0.0), (float) ($z ?? 0.0));

        if ($this->nativeId !== null) {
            NativeEngine::call('transform_rotate3d_by_id',
                $this->nativeId,
                $delta->x,
                $delta->y,
                $delta->z,
                $relativeToSelf,
            );
            return;
        }

        if ($relativeToSelf) {
            $deltaRotation = Quaternion::fromEulerAngles($delta);
            $this->localRotation = $this->localRotation->multiply($deltaRotation);
            return;
        }

        $deltaRotation = Quaternion::fromEulerAngles($delta);
        $this->rotation = $deltaRotation->multiply($this->rotation);
    }

    /**
     * Rotates the object around the Z axis, the rotation axis for 2D objects.
     *
     * @param float $degrees Angle to turn, in degrees.
     * @param bool $relativeToSelf When `true`, the rotation uses the object's own Z axis.
     *     When `false`, it uses the world Z axis.
     */
    public function rotate2D(float $degrees, bool $relativeToSelf = true): void
    {
        $this->rotate(0.0, 0.0, $degrees, $relativeToSelf);
    }

    /**
     * Rotates this transform around a world-space point and axis.
     *
     * The object moves on a circle around the point and turns by the same angle, like a
     * satellite or an orbit camera. Nothing happens when the axis has zero length.
     *
     * @param Vector3 $point Pivot point in world space.
     * @param Vector3 $axis Axis to rotate around, in world space. It does not need to be
     *     normalized.
     * @param float $angleDegrees Angle to rotate, in degrees.
     */
    public function rotateAround(Vector3 $point, Vector3 $axis, float $angleDegrees): void
    {
        if ($this->nativeId !== null) {
            NativeEngine::call('transform_rotate_around_by_id',
                $this->nativeId,
                $point->x,
                $point->y,
                $point->z,
                $axis->x,
                $axis->y,
                $axis->z,
                $angleDegrees,
            );
            return;
        }

        if ($axis->sqrMagnitude <= 0.000001) {
            return;
        }

        $deltaRotation = Quaternion::fromAxisAngle($axis, $angleDegrees)->normalized;
        $rotatedOffset = $deltaRotation->rotateVector(Vector3::difference($this->position, $point));
        $this->position = Vector3::sum($point, $rotatedOffset);
        $this->rotation = $deltaRotation->multiply($this->rotation)->normalized;
    }

    /**
     * Turns the object so {@see Transform::$forward} points at a world position.
     *
     * The object's roll (Z angle) is set to `0`. Use it for cameras that follow a target
     * and for turrets. Nothing happens when the target is at the object's own position.
     *
     * @param Vector3 $worldPosition Point to face, in world space.
     */
    public function lookAt(Vector3 $worldPosition): void
    {
        if ($this->nativeId !== null) {
            NativeEngine::call('transform_look_at_by_id',
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

    /**
     * Converts a point from this transform's local space to world space.
     *
     * Position, rotation and scale are all applied. Use it to find a world position on the
     * object, such as a muzzle point in front of a gun.
     *
     * @param Vector3 $localPoint Point in this transform's local space.
     * @return Vector3 The same point in world space.
     */
    public function transformPoint(Vector3 $localPoint): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_transform_point_by_id', $localPoint);
        }

        return Vector3::sum($this->position, $this->transformVector($localPoint));
    }

    /**
     * Converts a point from world space to this transform's local space.
     *
     * The reverse of {@see Transform::transformPoint()}.
     *
     * @param Vector3 $worldPoint Point in world space.
     * @return Vector3 The same point in this transform's local space.
     */
    public function inverseTransformPoint(Vector3 $worldPoint): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_inverse_transform_point_by_id', $worldPoint);
        }

        return $this->inverseTransformVector(Vector3::difference($worldPoint, $this->position));
    }

    /**
     * Converts a direction from this transform's local space to world space without applying scale or position.
     *
     * Only rotation is applied, so the length of the vector stays the same.
     *
     * @param Vector3 $localDirection Direction in this transform's local space.
     * @return Vector3 The same direction in world space.
     */
    public function transformDirection(Vector3 $localDirection): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_transform_direction_by_id', $localDirection);
        }

        return $this->rotation->rotateVector($localDirection);
    }

    /**
     * Converts a direction from world space to this transform's local space without applying scale or position.
     *
     * The reverse of {@see Transform::transformDirection()}.
     *
     * @param Vector3 $worldDirection Direction in world space.
     * @return Vector3 The same direction in this transform's local space.
     */
    public function inverseTransformDirection(Vector3 $worldDirection): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_inverse_transform_direction_by_id', $worldDirection);
        }

        return $this->rotation->inverse()->rotateVector($worldDirection);
    }

    /**
     * Converts a vector from this transform's local space to world space without applying position.
     *
     * Rotation and {@see Transform::$lossyScale} are applied, so the length changes with
     * the object's scale.
     *
     * @param Vector3 $localVector Vector in this transform's local space.
     * @return Vector3 The same vector in world space.
     */
    public function transformVector(Vector3 $localVector): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_transform_vector_by_id', $localVector);
        }

        return $this->transformDirection(Vector3::product($localVector, $this->lossyScale));
    }

    /**
     * Converts a vector from world space to this transform's local space without applying position.
     *
     * The reverse of {@see Transform::transformVector()}: rotation and scale are removed.
     *
     * @param Vector3 $worldVector Vector in world space.
     * @return Vector3 The same vector in this transform's local space.
     */
    public function inverseTransformVector(Vector3 $worldVector): Vector3
    {
        if ($this->nativeId !== null) {
            return $this->fetchVectorOperationFromNative('transform_inverse_transform_vector_by_id', $worldVector);
        }

        return $this->divideByScale($this->inverseTransformDirection($worldVector), $this->lossyScale);
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
            $this->localRotationValue = $resolved->localRotation;
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
        $this->localRotationValue = Quaternion::identity();
        $this->localScaleValue = new Vector3(1.0, 1.0, 1.0);
        $this->worldPositionCache = new Vector3(0.0, 0.0, 0.0);
    }

    /**
     * Returns the instance ID of the engine Transform this object wraps.
     *
     * @internal Engine bookkeeping used by the runtime bindings.
     *
     * @return int|null The instance ID, or `null` for a Transform that is not in a scene.
     */
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

        $parentScale = $this->parentValue->lossyScale;
        $scaledLocal = new Vector3(
            $this->localPositionValue->x * $parentScale->x,
            $this->localPositionValue->y * $parentScale->y,
            $this->localPositionValue->z * $parentScale->z,
        );

        return Vector3::sum(
            $this->parentValue->position,
            $this->parentValue->rotation->rotateVector($scaledLocal),
        );
    }

    private function fetchWorldScaleFromFallback(): Vector3
    {
        if ($this->parentValue === null) {
            return $this->localScaleValue;
        }

        return Vector3::product($this->parentValue->lossyScale, $this->localScaleValue);
    }

    private function divideByScale(Vector3 $value, Vector3 $scale): Vector3
    {
        return new Vector3(
            \abs($scale->x) > 0.000001 ? $value->x / $scale->x : $value->x,
            \abs($scale->y) > 0.000001 ? $value->y / $scale->y : $value->y,
            \abs($scale->z) > 0.000001 ? $value->z / $scale->z : $value->z,
        );
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

    private function fetchVectorFromNative(string $bindingName): Vector3
    {
        if ($this->nativeId === null) {
            return new Vector3();
        }

        /** @var array{x?: float, y?: float, z?: float}|false $data */
        $data = NativeEngine::call($bindingName, $this->nativeId);
        if (!\is_array($data)) {
            return new Vector3();
        }

        return new Vector3(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    private function fetchVectorOperationFromNative(string $bindingName, Vector3 $value): Vector3
    {
        if ($this->nativeId === null) {
            return new Vector3();
        }

        /** @var array{x?: float, y?: float, z?: float}|false $data */
        $data = NativeEngine::call($bindingName, $this->nativeId, $value->x, $value->y, $value->z);
        if (!\is_array($data)) {
            return new Vector3();
        }

        return new Vector3(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    private function fetchQuaternionFromNative(string $bindingName): Quaternion
    {
        if ($this->nativeId === null) {
            return Quaternion::identity();
        }

        /** @var array{x?: float, y?: float, z?: float, w?: float}|false $data */
        $data = NativeEngine::call($bindingName, $this->nativeId);
        if (!\is_array($data)) {
            return Quaternion::identity();
        }

        return new Quaternion(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
            (float) ($data['w'] ?? 1.0),
        );
    }

}
