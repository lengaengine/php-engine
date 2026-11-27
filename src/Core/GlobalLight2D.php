<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A 2D light that lights a 2D scene evenly with one color and brightness.
 *
 * Use it to set the overall mood of a 2D scene, such as a dark night tint,
 * before placing local lights like {@see PointLight2D} and {@see SpriteLight2D}.
 * Set its color, intensity and the layers it affects in the Inspector. Scripts can turn it off and on
 * with {@see Component::$enabled}.
 */
final class GlobalLight2D extends Component
{
    /**
     * Wraps the global 2D light component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'GlobalLight2D');
    }
}
