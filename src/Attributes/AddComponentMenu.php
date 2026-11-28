<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Places a Behaviour script at a chosen path in the Inspector's Add Component menu.
 *
 * Put the attribute on the Behaviour class. Each `/` in the path opens a
 * submenu, and the last part is the item name:
 *
 * ```php
 * use Lenga\Engine\Attributes\AddComponentMenu;
 * use Lenga\Engine\Core\Behaviour;
 *
 * #[AddComponentMenu('Combat/Melee Hitbox')]
 * final class MeleeHitbox extends Behaviour
 * {
 * }
 * ```
 *
 * A Behaviour without this attribute still appears in the menu, under
 * `Scripts` with a name made from its class name (`MeleeHitbox` becomes
 * `Melee Hitbox`). The editor reads the attribute from the script's source,
 * so pass the path and order as plain values in that order.
 *
 * `AddComponentMenuAttribute` is another name for this attribute. Write
 * `AddComponentMenu`.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AddComponentMenu
{
    /**
     * Creates the attribute with a menu path and an optional sort order.
     *
     * @param string $menuName Menu path for the script, with `/` between submenus, for example `'Enemies/Patrol'`.
     * @param int|null $order Sort position among the scripts in the menu. Scripts with a lower order come first; a script without an order sorts as 0. Scripts with the same order sort by path.
     */
    public function __construct(
        public readonly string $menuName,
        public readonly ?int $order = null,
    )
    {
    }
}
