<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * 2D physics joint that limits a Rigidbody2D to movement along one axis.
 *
 * Use it for elevators, pistons, rails and moving platforms that travel along a
 * single line. Add it to a GameObject that has a {@see Rigidbody2D}. Its axis,
 * connected body, limits and motor are set in the Inspector. From a script you
 * can turn the joint on or off with {@see Component::$enabled}.
 */
final class SliderJoint2D extends Component
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SliderJoint2D');
    }
}
