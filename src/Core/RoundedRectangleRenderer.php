<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class RoundedRectangleRenderer extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'RoundedRectangleRenderer');
    }
}
