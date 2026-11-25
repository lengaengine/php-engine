<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * How a SpriteRenderer fills its size.
 */
enum SpriteDrawMode: string
{
    /** The image is stretched over the sprite's size. */
    case Simple = 'Simple';

    /**
     * The image repeats every tile size across the sprite's size, from the
     * bottom-left corner, cut at the top and right edges.
     */
    case Tiled = 'Tiled';
}
