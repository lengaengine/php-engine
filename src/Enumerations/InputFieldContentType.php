<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Which characters an {@see \Lenga\Engine\UI\InputField} accepts, how it shows them, and which on-screen keyboard phones and tablets show for it.
 *
 * Set it with {@see \Lenga\Engine\UI\InputField::$contentType}. Characters a
 * type refuses are left out as the player types or pastes, and removed from
 * the text when the type changes.
 *
 * ```php
 * $codeField->contentType = InputFieldContentType::Alphanumeric;
 * ```
 */
enum InputFieldContentType: string
{
    /** Any printable character. */
    case Standard = 'Standard';

    /** Digits, with a minus sign only as the first character. Phones show a number keyboard. */
    case IntegerNumber = 'IntegerNumber';

    /** Digits and one decimal point, with a minus sign only as the first character. Phones show a decimal keyboard. */
    case DecimalNumber = 'DecimalNumber';

    /** ASCII letters (a-z, A-Z) and digits (0-9). */
    case Alphanumeric = 'Alphanumeric';

    /** Letters, single spaces and one apostrophe; each word starts with a capital letter and continues in lower case. */
    case Name = 'Name';

    /** The characters of an email address: ASCII letters and digits, one @, the symbols !#$%&'*+-/=?^_`{|}~ and dots that are not next to each other. Phones show an email keyboard. */
    case EmailAddress = 'EmailAddress';

    /** Any printable character, shown as the mask character. The text cannot be copied or cut. */
    case Password = 'Password';

    /** Digits as for IntegerNumber, shown as the mask character. The text cannot be copied or cut. Phones show a number keyboard. */
    case Pin = 'Pin';
}
