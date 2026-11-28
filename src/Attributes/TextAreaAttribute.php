<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

/**
 * Another name for the {@see TextArea} attribute. Use {@see TextArea} in your scripts.
 *
 * The editor reads `#[TextAreaAttribute(3, 8)]` the same way as `#[TextArea(3, 8)]`. PHP
 * itself does not treat `TextAreaAttribute` as an attribute class, so code that
 * reads attributes through reflection and calls `newInstance()` on it gets an
 * Error. Writing `#[TextArea(3, 8)]` works in both places.
 */
final class TextAreaAttribute extends TextArea
{
}
