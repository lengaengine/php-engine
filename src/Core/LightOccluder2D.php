<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class LightOccluder2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'LightOccluder2D');
    }
}
