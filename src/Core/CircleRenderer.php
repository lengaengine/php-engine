<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class CircleRenderer extends Renderer2D
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CircleRenderer');
    }
}
