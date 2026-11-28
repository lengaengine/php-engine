<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * 2D light whose shape comes from a texture mask.
 *
 * Use it for shaped or stylized lighting such as light cones, magical glows and
 * highlights. Its color, intensity, size, falloff, texture, affected layers and
 * shadow settings are set in the Inspector. From a script you can turn the light
 * on or off with {@see Component::$enabled}.
 */
final class SpriteLight2D extends Component
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
        parent::__construct($gameObject, $componentId, 'SpriteLight2D');
    }
}
