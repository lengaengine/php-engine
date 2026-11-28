<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Renderer that draws a grid of tiles painted from a Tileset asset.
 *
 * Create a Tilemap in the scene, assign a Tileset, and paint tiles in the Scene
 * view. Tile painting happens in the editor. From a script you can use the
 * members inherited from {@see Renderer}, such as its material and
 * {@see Component::$enabled}.
 */
final class Tilemap extends Renderer
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
        parent::__construct($gameObject, $componentId, 'Tilemap');
    }
}
