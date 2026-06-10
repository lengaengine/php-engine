<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class DistanceJoint2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'DistanceJoint2D');
    }
}
