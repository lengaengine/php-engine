<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class RaycastHit3D
{
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
