<?php

declare(strict_types=1);

namespace Lenga\Engine\Exceptions;

/**
 * Thrown when engine code runs outside the Lenga editor or a Lenga player.
 *
 * Most engine classes talk to the running engine. In a plain PHP process,
 * such as a command-line script or a unit test, that engine is missing, and
 * calls such as `Input::getKey()` throw this exception. The message names the
 * call that was not available. Run the code from the editor's Play mode or a
 * built game.
 */
final class LengaRuntimeUnavailableException extends LengaRuntimeException
{
}
