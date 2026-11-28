<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * 2D renderer that draws a triangle shape.
 *
 * Set its tint with {@see Renderer2D::$color} and its material with
 * {@see Renderer::$material}. Its shape settings are set in the Inspector.
 */
final class TriangleRenderer2D extends Renderer2D
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
        parent::__construct($gameObject, $componentId, 'TriangleRenderer2D');
    }
}
