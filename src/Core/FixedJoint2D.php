<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A 2D physics joint that locks a {@see Rigidbody2D} to another body so the two
 * move as one rigid piece.
 *
 * Use it for welded props, temporary attachments and objects built from several
 * bodies. Add it to a GameObject that has a Rigidbody2D, then set the connected
 * body and the anchors in the Inspector. Scripts can turn the joint off and on
 * with {@see Component::$enabled}, for example to break the attachment.
 */
final class FixedJoint2D extends Component
{
    /**
     * Wraps the fixed joint component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'FixedJoint2D');
    }
}
