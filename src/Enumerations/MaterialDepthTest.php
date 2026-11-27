<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * The comparison a fragment's depth must pass against the stored depth for
 * the fragment to be drawn, as Unity URP names Depth Test.
 *
 * Each value is the name a material file stores.
 */
enum MaterialDepthTest: string
{
    /** Never draws the fragment. */
    case Never = 'Never';

    /** Draws the fragment when it is closer than the stored depth. */
    case Less = 'Less';

    /** Draws the fragment when it is at the stored depth. */
    case Equal = 'Equal';

    /** Draws the fragment when it is closer than or at the stored depth. This is the default. */
    case LessEqual = 'LessEqual';

    /** Draws the fragment when it is farther away than the stored depth. */
    case Greater = 'Greater';

    /** Draws the fragment when it is at any depth other than the stored depth. */
    case NotEqual = 'NotEqual';

    /** Draws the fragment when it is farther away than or at the stored depth. */
    case GreaterEqual = 'GreaterEqual';

    /** Always draws the fragment, whatever the stored depth. */
    case Always = 'Always';
}
