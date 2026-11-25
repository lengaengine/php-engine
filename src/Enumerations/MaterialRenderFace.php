<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Which faces of a mesh a material draws, as Unity URP names the Render Face.
 *
 * Each value is the name a material file stores.
 */
enum MaterialRenderFace: string
{
    /**
     * Draws front faces and culls back faces.
     */
    case Front = 'Front';

    /**
     * Draws back faces and culls front faces.
     */
    case Back = 'Back';

    /**
     * Draws both faces.
     */
    case Both = 'Both';
}
