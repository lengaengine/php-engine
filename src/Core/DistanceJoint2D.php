<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A 2D physics joint that keeps a {@see Rigidbody2D} at a set distance from
 * another body or from a point in the world.
 *
 * Use it for ropes, chains, hanging lanterns and tethered pickups. Add it to a
 * GameObject that has a Rigidbody2D, then set the connected body, the anchors,
 * the distance and its limits in the Inspector. Scripts can turn the joint off
 * and on with {@see Component::$enabled}.
 */
final class DistanceJoint2D extends Component
{
    /**
     * Wraps the distance joint component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'DistanceJoint2D');
    }
}
