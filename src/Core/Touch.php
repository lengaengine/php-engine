<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\TouchPhase;
use function is_array;
use function is_string;

/**
 * Snapshot of a single touch point for the current input frame.
 *
 * Raylib exposes touch id, position, and count. Pressure is reported as
 * unsupported by Lenga's current backend and is normalized to 1.0.
 */
final class Touch
{
    public function __construct(
        public readonly int $fingerId,
        public readonly Vector2 $position,
        public readonly Vector2 $rawPosition,
        public readonly Vector2 $deltaPosition,
        public readonly float $deltaTime,
        public readonly TouchPhase $phase,
        public readonly float $pressure = 1.0,
        public readonly float $maximumPossiblePressure = 1.0,
        public readonly int $tapCount = 1,
    ) {
    }

    /**
     * @param array{
     *     fingerId?: int,
     *     position?: array{x?: float|int, y?: float|int},
     *     rawPosition?: array{x?: float|int, y?: float|int},
     *     deltaPosition?: array{x?: float|int, y?: float|int},
     *     deltaTime?: float|int,
     *     phase?: string,
     *     pressure?: float|int,
     *     maximumPossiblePressure?: float|int,
     *     tapCount?: int
     * } $data
     */
    public static function fromNativeData(array $data): self
    {
        $position = is_array($data['position'] ?? null) ? $data['position'] : [];
        $rawPosition = is_array($data['rawPosition'] ?? null) ? $data['rawPosition'] : $position;
        $deltaPosition = is_array($data['deltaPosition'] ?? null) ? $data['deltaPosition'] : [];
        $phaseName = is_string($data['phase'] ?? null) ? $data['phase'] : TouchPhase::STATIONARY->value;

        return new self(
            (int) ($data['fingerId'] ?? -1),
            new Vector2(
                (float) ($position['x'] ?? 0.0),
                (float) ($position['y'] ?? 0.0),
            ),
            new Vector2(
                (float) ($rawPosition['x'] ?? 0.0),
                (float) ($rawPosition['y'] ?? 0.0),
            ),
            new Vector2(
                (float) ($deltaPosition['x'] ?? 0.0),
                (float) ($deltaPosition['y'] ?? 0.0),
            ),
            (float) ($data['deltaTime'] ?? 0.0),
            TouchPhase::tryFrom($phaseName) ?? TouchPhase::STATIONARY,
            (float) ($data['pressure'] ?? 1.0),
            (float) ($data['maximumPossiblePressure'] ?? 1.0),
            (int) ($data['tapCount'] ?? 1),
        );
    }
}
