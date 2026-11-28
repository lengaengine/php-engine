<?php

declare(strict_types=1);

namespace Lenga\Engine\Exceptions;

/**
 * Thrown by {@see \Lenga\Engine\Networking\WebRequest::send()} when the engine
 * will not send a request, before anything reaches the network.
 *
 * The message says why: the host is not one of the project's hosts, the path
 * or a header is not valid, a GET or HEAD request has a body, or the platform
 * cannot send web requests.
 */
final class WebRequestRefusedException extends LengaRuntimeException
{
}
