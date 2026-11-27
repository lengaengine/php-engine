<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Lights 2D sprites and shapes in a circle around its GameObject's position.
 *
 * Set the light's colour, intensity, radius, falloff and shadows in the
 * Inspector. Scripts can turn the light on and off with
 * {@see Component::$enabled}.
 */
final class PointLight2D extends Component
{
    /**
     * Wraps an existing PointLight2D component.
     *
     * The engine creates PointLight2D wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'PointLight2D');
    }
}
