<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * The built-in shader a material draws with.
 *
 * Each value is the name a material file stores.
 */
enum MaterialShaderMode: string
{
    /**
     * Lit by the scene's lights.
     */
    case Lit = 'Lit';

    /**
     * Drawn with its colour and textures, whatever light reaches it.
     */
    case Unlit = 'Unlit';
}
