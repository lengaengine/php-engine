<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Networking\WebRequest;

/**
 * Suspends a coroutine until a web request finishes.
 *
 * {@see WebRequest::send()} returns one, so a coroutine can send a request
 * and wait for it in one line:
 *
 * ```php
 * yield $request->send();
 * ```
 *
 * The coroutine continues on the first frame after the request succeeds,
 * fails, times out or is cancelled.
 */
final class WaitForWebRequest extends YieldInstruction
{
    /**
     * Waits for a request. {@see WebRequest::send()} creates one for you.
     *
     * @param WebRequest $request The request to wait for.
     */
    public function __construct(
        public readonly WebRequest $request,
    ) {
    }

    /**
     * Returns true while the request is still in progress.
     */
    public function keepWaiting(): bool
    {
        return !$this->request->isDone;
    }
}
