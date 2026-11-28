<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A component of a type that has no dedicated PHP class.
 *
 * {@see GameObject::getComponent()}, {@see GameObject::getComponents()} and
 * the other lookup methods return a NativeComponent for an engine component
 * type that PHP does not wrap. Read {@see Component::$type} to see which type
 * it is, and use {@see Component::$enabled} to turn it off and on. Ask for a
 * component by its own class, such as `Rigidbody2D::class`, to get its full
 * API.
 */
final class NativeComponent extends Component
{
    /**
     * Wraps the component with the given id and type name.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::getComponent()} and the other lookup methods.
     */
    public function __construct(GameObject $gameObject, int $componentId, string $componentType)
    {
        parent::__construct($gameObject, $componentId, $componentType);
    }
}
