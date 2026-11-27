<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Draws a rectangle with rounded corners in a 2D scene.
 *
 * Set the size, corner roundness and number of corner segments in the
 * Inspector. Scripts can tint it with {@see Renderer2D::$color} and change
 * its material with {@see Renderer::$material}.
 */
final class RoundedRectangleRenderer extends Renderer2D
{
    /**
     * Wraps an existing RoundedRectangleRenderer component.
     *
     * The engine creates RoundedRectangleRenderer wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'RoundedRectangleRenderer');
    }
}
