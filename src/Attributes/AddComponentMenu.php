<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AddComponentMenu
{
    /**
     * Use the ContextMenu attribute to add commands to the context menu of the Inspector window.
     * 
     * @param string $menuName The path to the component.
     * @param int|null $order Where in the component menu to add the new item.
     */
    public function __construct(
        public readonly string $menuName,
        public readonly ?int $order = null,
    )
    {
    }
}