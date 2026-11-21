<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * A mesh UV channel a material samples its textures with.
 *
 * Each value is the name a material file stores.
 */
enum MaterialUvSet: string
{
    case UV0 = 'UV0';
    case UV1 = 'UV1';
}
