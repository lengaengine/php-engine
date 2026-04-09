<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class NativeComponent extends Component
{
    public function __construct(GameObject $gameObject, int $componentId, string $componentType)
    {
        parent::__construct($gameObject, $componentId, $componentType);
    }
}
