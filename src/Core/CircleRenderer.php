<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Draws a filled 2D circle at the GameObject's position.
 *
 * The circle is drawn in the color from {@see Renderer2D::$color} and
 * takes 2D lighting into account. Its size grows with the average of the
 * GameObject's X and Y scale. Drawing a circle does not make it collide;
 * add a {@see CircleCollider2D} for that.
 */
final class CircleRenderer extends Renderer2D
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
        parent::__construct($gameObject, $componentId, 'CircleRenderer');
    }
}
