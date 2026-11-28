<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Where a {@see \Lenga\Engine\Networking\WebRequest} stands.
 */
enum WebRequestResult: string
{
    /** The request has not finished, or has not been sent yet. */
    case InProgress = 'InProgress';

    /** The server answered with a status code below 400. */
    case Success = 'Success';

    /**
     * The server could not be reached: no network connection, a server name
     * that does not resolve, a refused connection or a rejected TLS
     * certificate. In a Web build, a response the browser withholds because
     * the server does not allow the game's origin (CORS) is reported here too.
     */
    case ConnectionError = 'ConnectionError';

    /**
     * The server answered with a status code of 400 or above, such as 404
     * Not Found or 500 Internal Server Error. The response body and headers
     * are available.
     */
    case ProtocolError = 'ProtocolError';

    /** No answer came within the request's timeout. */
    case TimedOut = 'TimedOut';

    /** The request was cancelled before it finished. */
    case Cancelled = 'Cancelled';
}
