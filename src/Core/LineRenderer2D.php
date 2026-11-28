<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Draws a straight 2D line between two points.
 *
 * Set the start point, end point, thickness, sorting layer and order in layer
 * in the Inspector. Scripts can tint it with {@see Renderer2D::$color} and use
 * the other members it inherits from {@see Renderer}.
 */
final class LineRenderer2D extends Renderer2D
{
    /**
     * Wraps the 2D line renderer component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'LineRenderer2D');
    }
}
