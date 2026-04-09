<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class KinematicMove3DResult
{
    public function __construct(
        public readonly Vector3 $position,
        public readonly Vector3 $resolvedDelta,
        public readonly Vector3 $attemptedDelta,
        public readonly bool $collided,
        public readonly bool $usedSlide,
        public readonly ?RaycastHit3D $hit = null,
    ) {
    }
}
