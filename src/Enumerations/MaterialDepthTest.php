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
    case Never = 'Never';
    case Less = 'Less';
    case Equal = 'Equal';
    case LessEqual = 'LessEqual';
    case Greater = 'Greater';
    case NotEqual = 'NotEqual';
    case GreaterEqual = 'GreaterEqual';
    case Always = 'Always';
}
