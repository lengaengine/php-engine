<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class Physics2D
{
    public const int ALL_LAYERS = -1;

    private function __construct()
    {
    }

    /**
     * @param array<int, mixed> $results
     * @return list<RaycastHit2D>
     */
    private static function wrapHitResults(array $results): array
    {
        $hits = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $hits[] = RaycastHit2D::fromNativeData($result);
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
    ): ?RaycastHit2D {
        $result = \lenga_internal_physics2d_raycast(
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

        return \is_array($result) ? RaycastHit2D::fromNativeData($result) : null;
    }

    /**
     * @return list<RaycastHit2D>
     */
    public static function raycastAll(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = \lenga_internal_physics2d_raycast_all(
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

    public static function circleCast(
        Vector3 $origin,
        float $radius,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit2D {
        $result = \lenga_internal_physics2d_circle_cast(
            $origin->x,
            $origin->y,
            $origin->z,
            $radius,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? RaycastHit2D::fromNativeData($result) : null;
    }

    /**
     * @return list<RaycastHit2D>
     */
    public static function circleCastAll(
        Vector3 $origin,
        float $radius,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = \lenga_internal_physics2d_circle_cast_all(
            $origin->x,
            $origin->y,
            $origin->z,
            $radius,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? self::wrapHitResults($result) : [];
    }

    public static function boxCast(
        Vector3 $origin,
        Vector3 $size,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit2D {
        $result = \lenga_internal_physics2d_box_cast(
            $origin->x,
            $origin->y,
            $origin->z,
            $size->x,
            $size->y,
            $size->z,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? RaycastHit2D::fromNativeData($result) : null;
    }

    /**
     * @return list<RaycastHit2D>
     */
    public static function boxCastAll(
        Vector3 $origin,
        Vector3 $size,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = \lenga_internal_physics2d_box_cast_all(
            $origin->x,
            $origin->y,
            $origin->z,
            $size->x,
            $size->y,
            $size->z,
            $direction->x,
            $direction->y,
            $direction->z,
            $distance,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        return \is_array($result) ? self::wrapHitResults($result) : [];
    }

    public static function overlapPoint(
        Vector3 $point,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?Component
    {
        $result = \lenga_internal_physics2d_overlap_point(
            $point->x,
            $point->y,
            $point->z,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        $component = GameObject::wrapNativeComponentLookupData($result);

        return $component instanceof Component ? $component : null;
    }

    /**
     * @return list<Component>
     */
    public static function overlapCircleAll(
        Vector3 $center,
        float $radius,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = \lenga_internal_physics2d_overlap_circle_all(
            $center->x,
            $center->y,
            $center->z,
            $radius,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        $components = GameObject::wrapNativeComponentLookupResults($result);

        return array_values(array_filter(
            $components,
            static fn (object $component): bool => $component instanceof Component,
        ));
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
        $result = \lenga_internal_physics2d_overlap_box_all(
            $center->x,
            $center->y,
            $center->z,
            $size->x,
            $size->y,
            $size->z,
            $includeTriggers,
            $layerMask ?? self::ALL_LAYERS,
        );

        $components = GameObject::wrapNativeComponentLookupResults($result);

        return array_values(array_filter(
            $components,
            static fn (object $component): bool => $component instanceof Component,
        ));
    }
}
