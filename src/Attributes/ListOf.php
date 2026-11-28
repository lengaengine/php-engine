<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Gives an `array` property an element type so the Inspector edits it as a typed list.
 *
 * PHP arrays do not record what they hold, so add `#[ListOf(...)]` with the
 * class of each element:
 *
 * ```php
 * use Lenga\Engine\Attributes\ListOf;
 * use Lenga\Engine\Core\Behaviour;
 * use Lenga\Engine\Core\GameObject;
 * use Lenga\Engine\Core\Vector3;
 *
 * final class Waypoint
 * {
 *     public string $label = '';
 *     public Vector3 $position;
 * }
 *
 * final class PatrolRoute extends Behaviour
 * {
 *     #[ListOf(GameObject::class)]
 *     public array $targets = [];
 *
 *     #[ListOf(Waypoint::class)]
 *     public array $waypoints = [];
 * }
 * ```
 *
 * The Inspector shows the property as an expandable list with a `Size`
 * control. GameObjects, components and assets get pickers, vectors and
 * colours get value fields, and your own classes get a nested editor for
 * their public and serialized fields.
 *
 * When the scene loads, Lenga turns each saved element into the listed type
 * before the Behaviour's lifecycle methods run: `Vector2`, `Vector3`,
 * `Vector4` and `Color` elements become those objects, and your own classes
 * become instances of that class. Keep the property typed as `array`, and add
 * a `@var array<int, Waypoint>` docblock above it so your code editor and
 * static analysis know the element type too.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ListOf
{
    /**
     * Creates the attribute with the element type.
     *
     * @param class-string $listType Class of each list element, usually written as `SomeClass::class`.
     */
    public function __construct(
        /** Fully qualified class name of each list element. */
        public readonly string $listType
    )
    {
    }
}
