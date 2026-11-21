<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * How a Transparent material surface is combined with what is behind it, as
 * Unity URP names the Blending Mode.
 *
 * Opaque surfaces ignore it. Each value is the name a material file stores.
 */
enum MaterialBlendingMode: string
{
    /**
     * Colour times alpha over the background.
     */
    case Alpha = 'Alpha';

    /**
     * The shader multiplies colour by alpha and the result goes over the background.
     */
    case Premultiply = 'Premultiply';

    /**
     * Colour times alpha is added to the background.
     */
    case Additive = 'Additive';

    /**
     * The background is multiplied by the colour, which moves towards white as alpha falls.
     */
    case Multiply = 'Multiply';
}
