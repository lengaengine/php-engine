<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Physics3D
{
    public const int ALL_LAYERS = -1;

    private function __construct()
    {
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
     * @deprecated Use CharacterController::move() for Unity-style kinematic movement.
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
     * @deprecated Use CharacterController::move() for Unity-style kinematic movement.
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

    public static function raycast(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit3D {
        $result = \lenga_internal_physics3d_raycast(
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
     * @return list<RaycastHit3D>
     */
    public static function raycastAll(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = \lenga_internal_physics3d_raycast_all(
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
     * @return list<Component>
     */
    public static function overlapSphereAll(
        Vector3 $center,
        float $radius,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = \lenga_internal_physics3d_overlap_sphere_all(
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
     * @return list<Component>
     */
    public static function overlapBoxAll(
        Vector3 $center,
        Vector3 $size,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = \lenga_internal_physics3d_overlap_box_all(
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
     * @return list<Component>
     */
    public static function overlapCapsuleAll(
        Vector3 $center,
        float $radius,
        float $height,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $results = \lenga_internal_physics3d_overlap_capsule_all(
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
