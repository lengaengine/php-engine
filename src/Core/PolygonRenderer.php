<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Draws a filled regular polygon, such as a triangle or a hexagon, in a 2D scene.
 *
 * Set the number of sides, radius and rotation in the Inspector. Scripts
 * can tint it with {@see Renderer2D::$color} and change its material with
 * {@see Renderer::$material}.
 */
final class PolygonRenderer extends Renderer2D
{
    /**
     * Wraps an existing PolygonRenderer component.
     *
     * The engine creates PolygonRenderer wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PolygonRenderer');
    }
}
