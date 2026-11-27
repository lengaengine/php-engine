<?php

namespace Lenga\Engine\Exceptions;

use RuntimeException;

/**
 * The base class for errors that come from the Lenga runtime.
 *
 * Catch this class to handle every Lenga runtime error in one place, such as
 * {@see LengaRuntimeUnavailableException}.
 */
class LengaRuntimeException extends RuntimeException
{
}
