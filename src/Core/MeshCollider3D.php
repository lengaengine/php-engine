<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class MeshCollider3D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'MeshCollider3D');
    }
}
