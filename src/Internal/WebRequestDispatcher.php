<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use Closure;
use Lenga\Engine\Networking\WebRequest;

/**
 * Holds every sent web request until it finishes and runs its completed
 * listeners when the engine reports it finished.
 *
 * A sent request keeps running when the script no longer holds it, so a
 * request built in a local variable still reaches its listeners. The engine
 * calls dispatchFinished() once per finished request at the start of a
 * frame, before Update, in the order the requests were sent.
 *
 * @internal
 */
final class WebRequestDispatcher
{
    /** @var array<int, WebRequest> */
    private static array $pending = [];

    private static ?Closure $complete = null;

    private function __construct()
    {
    }

    /**
     * Holds a request the engine accepted until it finishes.
     */
    public static function track(int $requestId, WebRequest $request): void
    {
        self::$pending[$requestId] = $request;
    }

    /**
     * Runs the completed listeners of a finished request and stops holding it.
     */
    public static function dispatchFinished(int $requestId): void
    {
        $request = self::$pending[$requestId] ?? null;
        if ($request === null) {
            return;
        }
        unset(self::$pending[$requestId]);

        self::$complete ??= Closure::bind(
            static function (WebRequest $request): void {
                $request->notifyCompleted();
            },
            null,
            WebRequest::class
        );
        (self::$complete)($request);
    }

    /**
     * The number of sent requests that have not finished yet.
     */
    public static function getPendingCount(): int
    {
        return \count(self::$pending);
    }
}
