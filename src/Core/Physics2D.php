<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Reads and changes 2D gravity and asks the 2D physics world questions such as raycasts and overlaps.
 *
 * Queries run immediately when a script calls them and report what the
 * physics world contains at that moment. Use a raycast for a thin line such
 * as line of sight or a ground probe, a circle or box cast to sweep a shape
 * along a direction, and an overlap to find colliders in an area.
 *
 * Positions, directions and sizes are {@see Vector3} values. 2D queries use
 * the X and Y components; set Z to `0.0`. 2D scenes use screen-oriented
 * coordinates: positive X points right and positive Y points down.
 *
 * Every query takes `$includeTriggers` (pass `false` to see only solid
 * colliders) and an optional `$layerMask` built with
 * {@see Physics2D::layerMask()} (null checks all layers).
 *
 * @example examples/Core/Physics2D.php Probe for ground and find nearby pickups
 */
final class Physics2D
{
    /** A layer mask that matches every layer. */
    public const int ALL_LAYERS = -1;

    private function __construct()
    {
    }

    /**
     * Gets the gravity applied to 2D physics bodies, in world units per second squared.
     *
     * The engine default is `(0, 980)`: positive Y pulls down the screen.
     * Returns a zero vector when the engine reports no gravity.
     */
    public static function getGravity(): Vector2
    {
        /** @var array{x?: float|int, y?: float|int}|false $gravity */
        $gravity = NativeEngine::call('physics2d_get_gravity');
        if (!\is_array($gravity)) {
            return new Vector2();
        }

        return new Vector2(
            (float) ($gravity['x'] ?? 0.0),
            (float) ($gravity['y'] ?? 0.0),
        );
    }

    /**
     * Sets the gravity applied to 2D physics bodies.
     *
     * Gravity affects each enabled `Dynamic` {@see Rigidbody2D} with
     * {@see Rigidbody2D::$useGravity} turned on, scaled by its
     * {@see Rigidbody2D::$gravityScale}.
     *
     * @param Vector2 $gravity The new gravity, in world units per second squared. Positive Y pulls down; negative Y pulls up.
     */
    public static function setGravity(Vector2 $gravity): void
    {
        NativeEngine::call('physics2d_set_gravity', $gravity->x, $gravity->y);
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

    /**
     * Builds a layer mask that matches the given layers.
     *
     * Pass the result as `$layerMask` to a query so it only reports colliders
     * on those layers, for example `Physics2D::layerMask(3)` for a ground
     * layer. Layers outside 0 to 31 are ignored.
     *
     * @param int ...$layers The layer numbers to include, from 0 to 31.
     * @return int The mask, or {@see Physics2D::ALL_LAYERS} when no layers are given.
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
     * Use it for line of sight, wall checks and ground probes.
     *
     * @param Vector3 $origin The world position the ray starts from.
     * @param Vector3 $direction The direction of the ray.
     * @param float $distance How far the ray travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return RaycastHit2D|null The nearest hit, or null when the ray hits nothing.
     */
    public static function raycast(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit2D {
        $result = NativeEngine::call('physics2d_raycast',
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
     * Casts a ray and returns every collider it hits.
     *
     * Use it when the first hit is not enough, for example for a shot that
     * passes through several targets.
     *
     * @param Vector3 $origin The world position the ray starts from.
     * @param Vector3 $direction The direction of the ray.
     * @param float $distance How far the ray travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<RaycastHit2D> Every hit along the ray; empty when it hits nothing.
     */
    public static function raycastAll(
        Vector3 $origin,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = NativeEngine::call('physics2d_raycast_all',
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
     * Sweeps a circle along a direction and returns the first collider it hits.
     *
     * A circle cast is a ray with thickness. Use it for forgiving ground
     * checks or to test whether a rounded body can move without hitting
     * anything.
     *
     * @param Vector3 $origin The world position of the circle's centre at the start of the sweep.
     * @param float $radius The radius of the circle, in world units.
     * @param Vector3 $direction The direction to sweep the circle.
     * @param float $distance How far the circle travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return RaycastHit2D|null The nearest hit, or null when the circle hits nothing.
     */
    public static function circleCast(
        Vector3 $origin,
        float $radius,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit2D {
        $result = NativeEngine::call('physics2d_circle_cast',
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
     * Sweeps a circle along a direction and returns every collider it hits.
     *
     * @param Vector3 $origin The world position of the circle's centre at the start of the sweep.
     * @param float $radius The radius of the circle, in world units.
     * @param Vector3 $direction The direction to sweep the circle.
     * @param float $distance How far the circle travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<RaycastHit2D> Every hit along the sweep; empty when it hits nothing.
     */
    public static function circleCastAll(
        Vector3 $origin,
        float $radius,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = NativeEngine::call('physics2d_circle_cast_all',
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

    /**
     * Sweeps a box along a direction and returns the first collider it hits.
     *
     * Use it when the moving shape is rectangular, such as a platformer
     * character's body or a melee hit area.
     *
     * @param Vector3 $origin The world position of the box's centre at the start of the sweep.
     * @param Vector3 $size The width and height of the box, in world units.
     * @param Vector3 $direction The direction to sweep the box.
     * @param float $distance How far the box travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return RaycastHit2D|null The nearest hit, or null when the box hits nothing.
     */
    public static function boxCast(
        Vector3 $origin,
        Vector3 $size,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?RaycastHit2D {
        $result = NativeEngine::call('physics2d_box_cast',
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
     * Sweeps a box along a direction and returns every collider it hits.
     *
     * @param Vector3 $origin The world position of the box's centre at the start of the sweep.
     * @param Vector3 $size The width and height of the box, in world units.
     * @param Vector3 $direction The direction to sweep the box.
     * @param float $distance How far the box travels, in world units.
     * @param bool $includeTriggers Whether trigger colliders can be hit.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<RaycastHit2D> Every hit along the sweep; empty when it hits nothing.
     */
    public static function boxCastAll(
        Vector3 $origin,
        Vector3 $size,
        Vector3 $direction,
        float $distance,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = NativeEngine::call('physics2d_box_cast_all',
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

    /**
     * Finds a collider that contains a world point.
     *
     * Use it to pick objects under the cursor or to check whether a spawn
     * point is occupied.
     *
     * @param Vector3 $point The world position to test.
     * @param bool $includeTriggers Whether trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return Component|null The collider component at the point, or null when there is none.
     */
    public static function overlapPoint(
        Vector3 $point,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): ?Component
    {
        $result = NativeEngine::call('physics2d_overlap_point',
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
     * Finds every collider that overlaps a circle.
     *
     * Use it for explosions, pickup magnets and awareness ranges.
     *
     * @param Vector3 $center The world position of the circle's centre.
     * @param float $radius The radius of the circle, in world units.
     * @param bool $includeTriggers Whether trigger colliders count.
     * @param int|null $layerMask Bit mask of the layers to check; null checks all layers.
     * @return list<Component> The overlapping collider components; empty when there are none.
     */
    public static function overlapCircleAll(
        Vector3 $center,
        float $radius,
        bool $includeTriggers = true,
        ?int $layerMask = null,
    ): array {
        $result = NativeEngine::call('physics2d_overlap_circle_all',
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
     * Finds every collider that overlaps a box.
     *
     * Use it for room sensors, rectangular hit zones and placement tests.
     *
     * @param Vector3 $center The world position of the box's centre.
     * @param Vector3 $size The width and height of the box, in world units.
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
        $result = NativeEngine::call('physics2d_overlap_box_all',
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
