<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * The HTTP method a {@see \Lenga\Engine\Networking\WebRequest} sends.
 *
 * GET and HEAD requests carry no body; the others may.
 */
enum WebRequestMethod: string
{
    /** Reads a resource. */
    case Get = 'GET';

    /** Sends data to the server, for example a new score. */
    case Post = 'POST';

    /** Replaces a resource with the request body. */
    case Put = 'PUT';

    /** Changes part of a resource. */
    case Patch = 'PATCH';

    /** Removes a resource. */
    case Delete = 'DELETE';

    /** Reads a resource's headers without its body. */
    case Head = 'HEAD';
}
