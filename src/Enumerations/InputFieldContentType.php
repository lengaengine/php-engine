<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Which characters an input field accepts.
 */
enum InputFieldContentType: string
{
    /** Any printable character. */
    case Standard = 'Standard';

    /** ASCII letters (a-z, A-Z) and digits (0-9) only. */
    case Alphanumeric = 'Alphanumeric';
}
