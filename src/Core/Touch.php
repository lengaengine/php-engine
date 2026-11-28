<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\TouchPhase;
use function is_array;
use function is_string;

/**
 * Snapshot of a single touch point for the current input frame.
 *
 * Read the touches for this frame from {@see Input::$touches} or
 * {@see Input::getTouch()}, and their number from {@see Input::$touchCount}. Each
 * Touch is a read-only copy taken when input was sampled, so read a fresh one every
 * frame. Follow one finger across frames with {@see Touch::$fingerId}, and use
 * {@see Touch::$phase} to tell when it went down, moved, stayed still or lifted.
 *
 * ```php
 * foreach (Input::$touches as $touch) {
 *     if ($touch->phase === TouchPhase::BEGAN) {
 *         $this->startDrag($touch->fingerId, $touch->position);
 *     }
 * }
 * ```
 *
 * Positions are in window pixels. Pressure and tap count are fixed at `1.0` and `1`
 * on platforms that do not report them. {@see Input::$touchPressureSupported} tells
 * you whether pressure values are real.
 */
final class Touch
{
    /**
     * Creates a touch snapshot.
     *
     * @internal The input system creates touch snapshots each frame. Scripts read them from
     *     {@see Input::$touches} or {@see Input::getTouch()}.
     *
     * @param int $fingerId Identifier of the finger, stable while it stays on the screen.
     * @param Vector2 $position Touch position in window pixels.
     * @param Vector2 $rawPosition Unprocessed touch position in window pixels.
     * @param Vector2 $deltaPosition Movement in pixels since the previous frame.
     * @param float $deltaTime Real time in seconds since the previous input sample.
     * @param TouchPhase $phase How the touch changed this frame.
     * @param float $pressure Touch pressure, from `0` to `$maximumPossiblePressure`.
     * @param float $maximumPossiblePressure Highest pressure value the device reports.
     * @param int $tapCount Number of taps in quick succession.
     */
    public function __construct(
        /** Identifier of this finger, stable from the frame it touches down until it lifts. */
        public readonly int $fingerId,
        /** Touch position in window pixels. */
        public readonly Vector2 $position,
        /**
         * Unprocessed touch position in window pixels.
         *
         * On current platforms it equals {@see Touch::$position}.
         */
        public readonly Vector2 $rawPosition,
        /**
         * Distance in pixels the touch moved since the previous frame.
         *
         * It is `(0, 0)` on the frame the touch begins and on the frame it ends.
         */
        public readonly Vector2 $deltaPosition,
        /** Real time in seconds between the previous input sample and this one. */
        public readonly float $deltaTime,
        /**
         * How the touch changed this frame.
         *
         * `BEGAN` on the first frame, `MOVED` or `STATIONARY` while the finger stays
         * down, and `ENDED` on the one frame after it lifts.
         */
        public readonly TouchPhase $phase,
        /**
         * How hard the finger presses, from `0` to {@see Touch::$maximumPossiblePressure}.
         *
         * Platforms that do not report pressure use `1.0`. Defaults to `1.0`.
         */
        public readonly float $pressure = 1.0,
        /**
         * Highest {@see Touch::$pressure} value the device can report.
         *
         * Defaults to `1.0`.
         */
        public readonly float $maximumPossiblePressure = 1.0,
        /**
         * Number of taps in quick succession at this position.
         *
         * Platforms that do not count taps use `1`. Defaults to `1`.
         */
        public readonly int $tapCount = 1,
    ) {
    }

    /**
     * Builds a touch snapshot from the array the native input system reports.
     *
     * @internal The input system calls this when it samples touches. Scripts read the
     *     results from {@see Input::$touches} or {@see Input::getTouch()}.
     *
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
     * } $data Touch data from the native input system. Missing fields use the same
     *     defaults as the constructor, a missing raw position copies the position, and an
     *     unknown phase becomes `STATIONARY`.
     *
     * @return self The touch snapshot.
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
