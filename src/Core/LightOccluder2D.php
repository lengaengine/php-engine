<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * A shape that blocks 2D light and casts shadows from nearby 2D lights.
 *
 * Add it to walls, platforms, crates and other objects that should stop light.
 * A {@see PointLight2D} with shadows turned on projects shadows away from the
 * occluder's shape. In the Inspector, the occluder's source picks where the
 * shape comes from: the GameObject's {@see BoxCollider2D}, its 2D renderer, or
 * a custom shape. Scripts can turn it off and on with {@see Component::$enabled}.
 */
final class LightOccluder2D extends Component
{
    /**
     * Wraps the 2D light occluder component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'LightOccluder2D');
    }
}
