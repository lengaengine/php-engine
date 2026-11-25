<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class PolygonRenderer extends Renderer2D
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PolygonRenderer');
    }
}
