<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Whether a material's surface writes depth, as Unity URP names Depth Write.
 *
 * Each value is the name a material file stores.
 */
enum MaterialDepthWrite: string
{
    /**
     * Opaque surfaces write depth and Transparent surfaces do not.
     */
    case Auto = 'Auto';

    /**
     * The surface writes depth whatever its Surface Type.
     */
    case ForceEnabled = 'ForceEnabled';

    /**
     * The surface never writes depth.
     */
    case ForceDisabled = 'ForceDisabled';
}
