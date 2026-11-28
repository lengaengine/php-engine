<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Describes where a 2D raycast or shape cast hit a collider.
 *
 * {@see Physics2D::raycast()}, {@see Physics2D::circleCast()},
 * {@see Physics2D::boxCast()} and their `All` variants return these values.
 * Use the point to place an impact effect and the normal to orient it.
 */
final class RaycastHit2D
{
    /**
     * Creates a hit description.
     *
     * The 2D physics queries create hits. Scripts read them.
     *
     * @internal
     *
     * @param GameObject|null $gameObject The GameObject whose collider was hit, or null when it could not be found.
     * @param Component|null $collider The collider component that was hit, or null when it could not be found.
     * @param Vector3 $point The world position where the cast touched the collider.
     * @param Vector3 $normal The surface direction of the collider at the hit point.
     * @param float $distance How far the cast travelled from its origin before the hit, in world units.
     * @param float $fraction How far along the cast's distance the hit happened, from 0 at the origin to 1 at the full distance.
     */
    public function __construct(
        public readonly ?GameObject $gameObject,
        public readonly ?Component $collider,
        public readonly Vector3 $point,
        public readonly Vector3 $normal,
        public readonly float $distance,
        public readonly float $fraction,
    ) {
    }

    /**
     * Creates a hit from the result data a 2D physics query reports.
     *
     * @internal Used by {@see Physics2D} to build query results.
     *
     * @param array{
     *     gameObject?: array<string, mixed>,
     *     collider?: mixed,
     *     point?: array{x?: float|int, y?: float|int, z?: float|int},
     *     normal?: array{x?: float|int, y?: float|int, z?: float|int},
     *     distance?: float|int,
     *     fraction?: float|int
     * } $data
     */
    public static function fromNativeData(array $data): self
    {
        $gameObject = \is_array($data['gameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['gameObject'])
            : null;

        $collider = GameObject::wrapNativeComponentLookupData($data['collider'] ?? null);

        $pointState = \is_array($data['point'] ?? null) ? $data['point'] : [];
        $normalState = \is_array($data['normal'] ?? null) ? $data['normal'] : [];

        return new self(
            $gameObject,
            $collider instanceof Component ? $collider : null,
            new Vector3(
                (float) ($pointState['x'] ?? 0.0),
                (float) ($pointState['y'] ?? 0.0),
                (float) ($pointState['z'] ?? 0.0),
            ),
            new Vector3(
                (float) ($normalState['x'] ?? 0.0),
                (float) ($normalState['y'] ?? 0.0),
                (float) ($normalState['z'] ?? 0.0),
            ),
            (float) ($data['distance'] ?? 0.0),
            (float) ($data['fraction'] ?? 0.0),
        );
    }
}
