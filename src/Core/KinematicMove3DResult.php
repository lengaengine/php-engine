<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * The outcome of a kinematic 3D move: where the moving shape ended up and
 * whether something blocked it.
 *
 * Returned by {@see Physics3D::moveAndSlideSphere()},
 * {@see Physics3D::moveAndSlideCapsule()} and the `moveAndSlide()` methods of
 * {@see SphereCollider3D} and {@see CapsuleCollider3D}. Those methods only
 * calculate the move; assign {@see KinematicMove3DResult::$position} to the
 * Transform to apply it. All values are in world space and world units.
 */
final class KinematicMove3DResult
{
    /**
     * Creates a move result.
     *
     * @internal The move-and-slide methods create results.
     *
     * @param Vector3 $position The position after the move.
     * @param Vector3 $resolvedDelta The movement that was applied.
     * @param Vector3 $attemptedDelta The movement that was requested.
     * @param bool $collided Whether the shape was blocked.
     * @param bool $usedSlide Whether the move slid along a blocking collider.
     * @param RaycastHit3D|null $hit The last collider hit found while checking the move, if any.
     */
    public function __construct(
        /** The position after the move. Assign it to the Transform to apply the move. */
        public readonly Vector3 $position,
        /**
         * The movement that was applied: the difference between the start
         * position and {@see KinematicMove3DResult::$position}.
         *
         * It is shorter than {@see KinematicMove3DResult::$attemptedDelta}
         * when something blocked the move, and zero when nothing could move.
         */
        public readonly Vector3 $resolvedDelta,
        /**
         * The movement that was requested.
         *
         * When the first probe shortens the move to stop before a collider,
         * this holds the shortened movement.
         */
        public readonly Vector3 $attemptedDelta,
        /**
         * Whether the shape was blocked: it could not move at all, or the
         * shortened move still overlapped a collider and had to slide.
         */
        public readonly bool $collided,
        /**
         * Whether the move slid along a blocking collider by keeping the X or Z
         * part of the movement that fit.
         */
        public readonly bool $usedSlide,
        /**
         * The last collider hit found while checking the move, or `null` when
         * none was found.
         *
         * It can be set while {@see KinematicMove3DResult::$collided} is
         * `false`, when the move was shortened to stop just before a collider.
         */
        public readonly ?RaycastHit3D $hit = null,
    ) {
    }
}
