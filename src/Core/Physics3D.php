<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Reads and changes 3D gravity and asks the 3D physics world questions such as raycasts and overlaps.
 *
 * Queries run immediately when a script calls them and do not move or push
 * anything. Use a raycast for line of sight or ground probes, and an overlap
 * to find colliders in an area such as a pickup or hazard volume. Overlaps
 * return collider components; read each one's `gameObject` to find out what
 * was found.
 *
 * 3D scenes use a Y-up coordinate system. Every query takes
 * `$includeTriggers` (pass `false` to see only solid colliders) and an
 * optional `$layerMask` built with {@see Physics3D::layerMask()} (null checks
 * all layers).
 */
final class Physics3D
{
    /** A layer mask that matches every layer. */
    public const int ALL_LAYERS = -1;

    private function __construct()
    {
    }

    /**
     * Gets the gravity applied to 3D physics bodies, in world units per second squared.
     *
     * The engine default is `(0, -9.8, 0)`: negative Y pulls down. Returns a
     * zero vector when the engine reports no gravity.
     */
    public static function getGravity(): Vector3
    {
        /** @var array{x?: float|int, y?: float|int, z?: float|int}|false $gravity */
        $gravity = NativeEngine::call('physics3d_get_gravity');
        if (!\is_array($gravity)) {
            return new Vector3();
        }

        return Vector3::fromArray($gravity);
    }

    /**
     * Sets the gravity applied to 3D physics bodies.
     *
     * Gravity affects each dynamic {@see Rigidbody3D} with
     * {@see Rigidbody3D::$useGravity} turned on.
     *
     * @param Vector3 $gravity The new gravity, in world units per second squared.
     */
    public static function setGravity(Vector3 $gravity): void
    {
        NativeEngine::call('physics3d_set_gravity', $gravity->x, $gravity->y, $gravity->z);
    }

    private static function isSelfHit(?GameObject $ignoreGameObject, ?GameObject $candidate): bool
    {
        if (!$ignoreGameObject instanceof GameObject || !$candidate instanceof GameObject) {
            return false;
        }

        return $ignoreGameObject->getInstanceId() === $candidate->getInstanceId();
    }

    /**
     * @param list<RaycastHit3D> $hits
     */
    private static function firstBlockingHit(array $hits, ?GameObject $ignoreGameObject): ?RaycastHit3D
    {
        foreach ($hits as $hit) {
            if (self::isSelfHit($ignoreGameObject, $hit->gameObject)) {
                continue;
            }

            return $hit;
        }

        return null;
    }

    /**
     * @param list<Component> $components
     */
    private static function hasBlockingComponent(array $components, ?GameObject $ignoreGameObject): bool
    {
        foreach ($components as $component) {
            if (!$component instanceof Component) {
                continue;
            }

            if (self::isSelfHit($ignoreGameObject, $component->gameObject)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private static function limitDeltaWithForwardProbe(
        Vector3 $probeOrigin,
        float $probeRadius,
        Vector3 $delta,
        ?GameObject $ignoreGameObject,
        bool $includeTriggers,
        ?int $layerMask,
        float $skinWidth,
    ): array {
        if ($delta->sqrMagnitude <= 0.000001) {
            return [$delta, null];
        }

        $direction = $delta->normalized;
        $moveDistance = $delta->magnitude;
        $probeDistance = $moveDistance + $probeRadius + $skinWidth;
        $hit = self::firstBlockingHit(
            self::raycastAll($probeOrigin, $direction, $probeDistance, $includeTriggers, $layerMask),
            $ignoreGameObject,
        );

        if (!$hit instanceof RaycastHit3D) {
            return [$delta, null];
        }

        $allowedDistance = \max(0.0, $hit->distance - $probeRadius - $skinWidth);
        if ($allowedDistance >= $moveDistance) {
            return [$delta, $hit];
        }

        if ($allowedDistance <= 0.000001) {
            return [Vector3::zero(), $hit];
        }

        return [Vector3::scaleNew($direction, $allowedDistance), $hit];
    }

    /**
     * Moves a sphere by a distance and slides it along whatever blocks it.
     *
     * The move first shortens the step to stop before the first collider in
     * its path. When the sphere would still overlap a collider, it tries the
     * X and Z parts of the move separately and keeps the ones that fit. The
     * method only calculates a position; assign the result's position to the
     * Transform to apply it.
     *
     * @deprecated Use {@see CharacterController::move()} for kinematic movement.
     *
     * @param Vector3 $position The sphere's current world position.
     * @param float $radius The sphere's radius, in world units.
     * @param Vector3 $delta The movement to attempt this step, in world units.
     * @param GameObject|null $ignoreGameObject A GameObject whose colliders do not block the move, usually the moving object itself.
     * @param Vector3|null $offset Offset from `$position` to the sphere's centre; null means no offset.
     * @param float $skinWidth Gap kept between the sphere and blocking colliders, in world units.
     * @param bool $includeTriggers Whether trigger colliders block the move.
     * @param int|null $layerMask Bit mask of the layers that can block the move; null checks all layers.
     * @return KinematicMove3DResult The resolved position, the movement that was applied and whether it collided or slid.
     */
    public static function moveAndSlideSphere(
        Vector3 $position,
        float $radius,
        Vector3 $delta,
        ?GameObject $ignoreGameObject = null,
        ?Vector3 $offset = null,
        float $skinWidth = 0.05,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): KinematicMove3DResult {
        if ($delta->sqrMagnitude <= 0.000001) {
            return new KinematicMove3DResult($position->clone(), Vector3::zero(), $delta->clone(), false, false);
        }

        $centerOffset = $offset ?? Vector3::zero();
        $probeRadius = \max(0.01, $radius - $skinWidth);
        $current = $position->clone();
        $lastHit = null;

        [$delta, $lastHit] = self::limitDeltaWithForwardProbe(
            Vector3::sum($current, $centerOffset),
            $radius,
            $delta,
            $ignoreGameObject,
            $includeTriggers,
            $layerMask,
            $skinWidth,
        );

        if ($delta->sqrMagnitude <= 0.000001) {
            return new KinematicMove3DResult($current, Vector3::zero(), $delta->clone(), true, false, $lastHit);
        }

        $target = Vector3::sum($current, $delta);
        $center = Vector3::sum($target, $centerOffset);
        $hits = self::overlapSphereAll($center, $probeRadius, $includeTriggers, $layerMask);
        if (!self::hasBlockingComponent($hits, $ignoreGameObject)) {
            return new KinematicMove3DResult($target, $delta->clone(), $delta->clone(), false, false, $lastHit);
        }

        $resolved = $current->clone();
        $usedSlide = false;
        $attemptedDelta = $delta->clone();

        if (\abs($delta->x) > 0.000001) {
            [$xDelta, $axisHit] = self::limitDeltaWithForwardProbe(
                Vector3::sum($resolved, $centerOffset),
                $radius,
                new Vector3($delta->x, 0.0, 0.0),
                $ignoreGameObject,
                $includeTriggers,
                $layerMask,
                $skinWidth,
            );
            $candidate = Vector3::sum($resolved, $xDelta);
            $candidateCenter = Vector3::sum($candidate, $centerOffset);
            if (!self::hasBlockingComponent(
                self::overlapSphereAll($candidateCenter, $probeRadius, $includeTriggers, $layerMask),
                $ignoreGameObject
            )) {
                $resolved = $candidate;
                $usedSlide = true;
            } elseif ($axisHit instanceof RaycastHit3D) {
                $lastHit = $axisHit;
            }
        }

        if (\abs($delta->z) > 0.000001) {
            [$zDelta, $axisHit] = self::limitDeltaWithForwardProbe(
                Vector3::sum($resolved, $centerOffset),
                $radius,
                new Vector3(0.0, 0.0, $delta->z),
                $ignoreGameObject,
                $includeTriggers,
                $layerMask,
                $skinWidth,
            );
            $candidate = Vector3::sum($resolved, $zDelta);
            $candidateCenter = Vector3::sum($candidate, $centerOffset);
            if (!self::hasBlockingComponent(
                self::overlapSphereAll($candidateCenter, $probeRadius, $includeTriggers, $layerMask),
                $ignoreGameObject
            )) {
                $resolved = $candidate;
                $usedSlide = true;
            } elseif ($axisHit instanceof RaycastHit3D) {
                $lastHit = $axisHit;
            }
        }

        return new KinematicMove3DResult(
            $resolved,
            Vector3::difference($resolved, $current),
            $attemptedDelta,
            true,
            $usedSlide,
            $lastHit,
        );
    }

    /**
     * Moves an upright capsule by a distance and slides it along whatever blocks it.
     *
     * The move first shortens the step to stop before the first collider in
     * its path. When the capsule would still overlap a collider, it tries the
     * X and Z parts of the move separately and keeps the ones that fit. The
     * method only calculates a position; assign the result's position to the
     * Transform to apply it.
     *
     * @deprecated Use {@see CharacterController::move()} for kinematic movement.
     *
     * @param Vector3 $position The capsule's current world position.
     * @param float $radius The capsule's radius, in world units.
     * @param float $height The capsule's total height, in world units.
     * @param Vector3 $delta The movement to attempt this step, in world units.
     * @param GameObject|null $ignoreGameObject A GameObject whose colliders do not block the move, usually the moving object itself.
     * @param Vector3|null $offset Offset from `$position` to the capsule's centre; null means no offset.
     * @param float $skinWidth Gap kept between the capsule and blocking colliders, in world units.
     * @param bool $includeTriggers Whether trigger colliders block the move.
     * @param int|null $layerMask Bit mask of the layers that can block the move; null checks all layers.
     * @return KinematicMove3DResult The resolved position, the movement that was applied and whether it collided or slid.
     */
    public static function moveAndSlideCapsule(
        Vector3 $position,
        float $radius,
        float $height,
        Vector3 $delta,
        ?GameObject $ignoreGameObject = null,
        ?Vector3 $offset = null,
        float $skinWidth = 0.05,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): KinematicMove3DResult {
        if ($delta->sqrMagnitude <= 0.000001) {
            return new KinematicMove3DResult($position->clone(), Vector3::zero(), $delta->clone(), false, false);
        }

        $centerOffset = $offset ?? Vector3::zero();
        $probeRadius = \max(0.01, $radius - $skinWidth);
        $probeHeight = \max($probeRadius * 2.0, $height - ($skinWidth * 2.0));
        $current = $position->clone();
        $lastHit = null;

        [$delta, $lastHit] = self::limitDeltaWithForwardProbe(
            Vector3::sum($current, $centerOffset),
            $radius,
            $delta,
            $ignoreGameObject,
            $includeTriggers,
            $layerMask,
            $skinWidth,
        );

        if ($delta->sqrMagnitude <= 0.000001) {
            return new KinematicMove3DResult($current, Vector3::zero(), $delta->clone(), true, false, $lastHit);
        }

        $target = Vector3::sum($current, $delta);
        $center = Vector3::sum($target, $centerOffset);
        $hits = self::overlapCapsuleAll($center, $probeRadius, $probeHeight, $includeTriggers, $layerMask);
        if (!self::hasBlockingComponent($hits, $ignoreGameObject)) {
            return new KinematicMove3DResult($target, $delta->clone(), $delta->clone(), false, false, $lastHit);
        }

        $resolved = $current->clone();
        $usedSlide = false;
        $attemptedDelta = $delta->clone();

        if (\abs($delta->x) > 0.000001) {
            [$xDelta, $axisHit] = self::limitDeltaWithForwardProbe(
                Vector3::sum($resolved, $centerOffset),
                $radius,
                new Vector3($delta->x, 0.0, 0.0),
                $ignoreGameObject,
                $includeTriggers,
                $layerMask,
                $skinWidth,
            );
            $candidate = Vector3::sum($resolved, $xDelta);
            $candidateCenter = Vector3::sum($candidate, $centerOffset);
            if (!self::hasBlockingComponent(
                self::overlapCapsuleAll($candidateCenter, $probeRadius, $probeHeight, $includeTriggers, $layerMask),
                $ignoreGameObject
            )) {
                $resolved = $candidate;
                $usedSlide = true;
            } elseif ($axisHit instanceof RaycastHit3D) {
                $lastHit = $axisHit;
            }
        }

        if (\abs($delta->z) > 0.000001) {
            [$zDelta, $axisHit] = self::limitDeltaWithForwardProbe(
                Vector3::sum($resolved, $centerOffset),
                $radius,
                new Vector3(0.0, 0.0, $delta->z),
                $ignoreGameObject,
                $includeTriggers,
                $layerMask,
                $skinWidth,
            );
            $candidate = Vector3::sum($resolved, $zDelta);
            $candidateCenter = Vector3::sum($candidate, $centerOffset);
            if (!self::hasBlockingComponent(
                self::overlapCapsuleAll($candidateCenter, $probeRadius, $probeHeight, $includeTriggers, $layerMask),
                $ignoreGameObject
            )) {
                $resolved = $candidate;
                $usedSlide = true;
            } elseif ($axisHit instanceof RaycastHit3D) {
                $lastHit = $axisHit;
            }
        }

        return new KinematicMove3DResult(
            $resolved,
            Vector3::difference($resolved, $current),
            $attemptedDelta,
            true,
            $usedSlide,
            $lastHit,
        );
    }

    /**
     * @param array<int, mixed> $results
     * @return list<RaycastHit3D>
     */
    private static function wrapHitResults(array $results): array
    {
        $hits = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $hits[] = RaycastHit3D::fromNativeData($result);
            }
        }

        return $hits;
    }

    /**
     * Builds a layer mask that matches the given layers.
     *
     * Pass the result as `$layerMask` to a query so it only reports colliders
     * on those layers. Layers outside 0 to 31 are ignored.
     *
     * @param int ...$layers The layer numbers to include, from 0 to 31.
     * @return int The mask, or {@see Physics3D::ALL_LAYERS} when no layers are given.
     */
    public static function layerMask(int ...$layers): int
    {
        if ($layers === []) {
            return self::ALL_LAYERS;
        }

        $mask = 0;
        foreach ($layers as $layer) {
            if ($layer < 0 || $layer >= 32) {
                continue;
            }

            $mask |= 1 << $layer;
        }

        return $mask;
    }

    /**
     * Casts a ray and returns the first collider it hits.
     *
     * @param Vector3 $origin The world position the ray starts from.
     * @param Vector3 $direction The direction of the ray.
     * @param float $distance How far the ray travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return RaycastHit3D|null The closest hit, or null when the ray hits nothing.
     */
    public static function raycast(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit3D {
        $result = NativeEngine::call('physics3d_raycast',
            $origin->x,
            $origin->y,
            $origin->z,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? RaycastHit3D::fromNativeData($result) : null;
    }

    /**
     * Casts a ray and returns every collider it hits.
     *
     * @param Vector3 $origin The world position the ray starts from.
     * @param Vector3 $direction The direction of the ray.
     * @param float $distance How far the ray travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<RaycastHit3D> Every hit along the ray; empty when it hits nothing.
     */
    public static function raycastAll(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = NativeEngine::call('physics3d_raycast_all',
            $origin->x,
            $origin->y,
            $origin->z,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? self::wrapHitResults($result) : [];
    }

    /**
     * Finds every collider that overlaps a sphere.
     *
     * Leave `$includeTriggers` on to detect pickup or hazard volumes.
     *
     * @param Vector3 $center The world position of the sphere's centre.
     * @param float $radius The radius of the sphere, in world units.
     * @param bool $includeTriggers Whether trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Component> The overlapping collider components; empty when there are none.
     */
    public static function overlapSphereAll(
        Vector3 $center,
        float $radius,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = NativeEngine::call('physics3d_overlap_sphere_all',
            $center->x,
            $center->y,
            $center->z,
            $radius,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $components = [];
        foreach ($results as $result) {
            $component = GameObject::wrapNativeComponentLookupData($result);
            if ($component instanceof Component) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Finds every collider that overlaps a box.
     *
     * @param Vector3 $center The world position of the box's centre.
     * @param Vector3 $size The width, height and depth of the box, in world units.
     * @param bool $includeTriggers Whether trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Component> The overlapping collider components; empty when there are none.
     */
    public static function overlapBoxAll(
        Vector3 $center,
        Vector3 $size,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = NativeEngine::call('physics3d_overlap_box_all',
            $center->x,
            $center->y,
            $center->z,
            $size->x,
            $size->y,
            $size->z,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $components = [];
        foreach ($results as $result) {
            $component = GameObject::wrapNativeComponentLookupData($result);
            if ($component instanceof Component) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Finds every collider that overlaps an upright capsule.
     *
     * @param Vector3 $center The world position of the capsule's centre.
     * @param float $radius The radius of the capsule, in world units.
     * @param float $height The total height of the capsule, in world units.
     * @param bool $includeTriggers Whether trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Component> The overlapping collider components; empty when there are none.
     */
    public static function overlapCapsuleAll(
        Vector3 $center,
        float $radius,
        float $height,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = NativeEngine::call('physics3d_overlap_capsule_all',
            $center->x,
            $center->y,
            $center->z,
            $radius,
            $height,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $components = [];
        foreach ($results as $result) {
            $component = GameObject::wrapNativeComponentLookupData($result);
            if ($component instanceof Component) {
                $components[] = $component;
            }
        }

        return $components;
    }
}
