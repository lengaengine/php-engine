<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Draws a ring or an arc in a 2D scene.
 *
 * Set the inner and outer radius, the start and end angle and the number
 * of segments in the Inspector. Scripts can tint it with
 * {@see Renderer2D::$color} and change its material with
 * {@see Renderer::$material}.
 */
final class RingRenderer extends Renderer2D
{
    /**
     * Wraps an existing RingRenderer component.
     *
     * The engine creates RingRenderer wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'RingRenderer');
    }
}
