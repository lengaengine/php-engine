<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A 2D physics joint that lets a {@see Rigidbody2D} rotate around one anchor
 * point.
 *
 * Use it for doors, pendulums, swinging traps and wheels. Add it to a
 * GameObject that has a Rigidbody2D, then set the anchor, the connected body
 * and any angle limits in the Inspector. Scripts can turn the joint off and on
 * with {@see Component::$enabled}.
 */
final class HingeJoint2D extends Component
{
    /**
     * Wraps the hinge joint component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'HingeJoint2D');
    }
}
