<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class TriangleRenderer2D extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'TriangleRenderer2D');
    }
}
