<?php

declare(strict_types=1);

namespace Lenga\Engine\Networking;

use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Signal;
use Lenga\Engine\Core\WaitForWebRequest;
use Lenga\Engine\Enumerations\WebRequestMethod;
use Lenga\Engine\Enumerations\WebRequestResult;
use Lenga\Engine\Exceptions\WebRequestRefusedException;
use Lenga\Engine\Internal\WebRequestDispatcher;
use InvalidArgumentException;
use JsonException;
use LogicException;

use function is_array;
use function is_finite;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Sends an HTTP request to one of the project's web services and reads the
 * answer, while the game keeps running.
 *
 * Scripts never hold a URL. Each web service the game talks to is listed
 * under **Project > Project Settings... > Network** with a name and a URL,
 * for example `leaderboard` and `https://scores.example.com/api`. A request
 * names the host and gives a path on it; the engine builds the URL and
 * refuses any host the project does not list.
 *
 * A request runs in the background. Wait for it in a coroutine with
 * `yield $request->send()`, connect a listener to
 * {@see WebRequest::$completed}, or check {@see WebRequest::$isDone} each
 * frame. When it has finished, {@see WebRequest::$result} says how it went,
 * and {@see WebRequest::$responseCode}, {@see WebRequest::$text},
 * {@see WebRequest::$responseHeaders} and {@see WebRequest::$error} hold the
 * answer.
 *
 * A sent request keeps running until it finishes, times out or is cancelled,
 * even when the script no longer holds it.
 *
 * Without a coroutine, connect a listener before sending:
 *
 * ```php
 * $request = new WebRequest('leaderboard', '/v1/scores?limit=10');
 * $request->completed->add(function (WebRequest $request): void {
 *     if ($request->result === WebRequestResult::Success) {
 *         $this->showScores($request->decodeJsonResponse());
 *     }
 * });
 * $request->send();
 * ```
 *
 * The URL a request goes to depends on the build: Release builds use the
 * host's URL, and editor Play and Development builds use its Development
 * URL when it has one.
 *
 * In a Web build the browser sends the request. The page and the web service
 * are on different origins, so the service must allow the game's origin with
 * CORS headers, including a preflight answer for any header the game sets,
 * such as Authorization or a JSON Content-Type.
 *
 * @example examples/Networking/WebRequest.php Load the top ten scores and submit a score to an online leaderboard
 */
final class WebRequest
{
    /**
     * Headers to send, by name, for example
     * `['Authorization' => 'Bearer ' . $token]`. The engine sets Host,
     * Content-Length, Cookie, Origin and the other headers browsers reserve;
     * sending one of those is refused.
     *
     * @var array<string, string>
     */
    public array $headers = [];

    /**
     * The body to send, or null for none. GET and HEAD requests cannot have
     * one. Without a Content-Type header the engine sends
     * `text/plain; charset=utf-8`.
     */
    public ?string $body = null;

    /**
     * Seconds the whole request may take before it ends as
     * {@see WebRequestResult::TimedOut}. 0, the default, sets no limit.
     */
    public float $timeout = 0.0 {
        set(float $value) {
            if (!is_finite($value) || $value < 0.0) {
                throw new InvalidArgumentException('A web request timeout must be 0 or more seconds.');
            }
            $this->timeout = $value;
        }
    }

    /**
     * How the request went. {@see WebRequestResult::InProgress} until it
     * finishes, and before it is sent.
     */
    public WebRequestResult $result {
        get => WebRequestResult::from($this->readOutcome()['result'] ?? WebRequestResult::InProgress->value);
    }

    /**
     * Whether the request was sent and has finished: it succeeded, failed,
     * timed out or was cancelled.
     */
    public bool $isDone {
        get => $this->readOutcome() !== null;
    }

    /**
     * The HTTP status code the server answered with, such as 200 or 404. 0
     * while the request is in progress and when the server did not answer.
     */
    public int $responseCode {
        get => (int) ($this->readOutcome()['statusCode'] ?? 0);
    }

    /**
     * The response body as the server sent it. Empty while the request is in
     * progress and when the server did not answer.
     */
    public string $text {
        get => (string) ($this->readOutcome()['body'] ?? '');
    }

    /**
     * The response headers, by lower-case name, for example
     * `$request->responseHeaders['content-type']`. A header the server sent
     * more than once has its values joined with ", ".
     *
     * @var array<string, string>
     */
    public array $responseHeaders {
        get => (array) ($this->readOutcome()['headers'] ?? []);
    }

    /**
     * What went wrong, for anything but {@see WebRequestResult::Success}:
     * "HTTP 404" for a protocol error, or the platform's description of a
     * connection error. Empty on success and while the request is in
     * progress.
     */
    public string $error {
        get => (string) ($this->readOutcome()['error'] ?? '');
    }

    /**
     * Fires once when the request ends, with the WebRequest as its only
     * argument, at the start of the next frame before any Behaviour's Update.
     * It fires for every ending: success, an error, a timeout, or
     * {@see WebRequest::cancel()}, so check {@see WebRequest::$result}.
     * Requests that end in the same frame fire in the order they were sent.
     * Connect listeners with `$request->completed->add(...)`.
     */
    public readonly Signal $completed;

    private ?int $requestId = null;

    /**
     * The finished request's state, kept once the engine reports it.
     *
     * @var array{result: string, statusCode?: int, error?: string, body?: string, headers?: array<string, string>}|null
     */
    private ?array $outcome = null;

    /**
     * Creates a request. Nothing is sent until {@see WebRequest::send()}.
     *
     * @param string $host The name of one of the project's hosts, as listed
     *     under Project Settings > Network.
     * @param string $path The path and query on that host, starting with
     *     "/", for example "/v1/scores?limit=10". Encode values with
     *     rawurlencode() or http_build_query().
     * @param WebRequestMethod $method The HTTP method.
     */
    public function __construct(
        public readonly string $host,
        public readonly string $path,
        public readonly WebRequestMethod $method = WebRequestMethod::Get,
    ) {
        $this->completed = new Signal();
    }

    /**
     * Sends the request and returns at once. The answer arrives on a later
     * frame.
     *
     * @return WaitForWebRequest Yield it from a coroutine to wait for the answer.
     * @throws WebRequestRefusedException When the engine will not send the
     *     request, for example because the host is not one of the project's
     *     hosts.
     * @throws LogicException When the request was already sent. Create a new
     *     WebRequest to send again.
     */
    public function send(): WaitForWebRequest
    {
        if ($this->requestId !== null) {
            throw new LogicException('This web request was already sent. Create a new WebRequest to send again.');
        }

        $sent = NativeEngine::call(
            'web_request_send',
            $this->host,
            $this->path,
            $this->method->value,
            $this->headers,
            $this->body,
            $this->timeout,
        );
        if (is_string($sent)) {
            throw new WebRequestRefusedException($sent);
        }
        if (!is_int($sent)) {
            throw new WebRequestRefusedException('The engine did not accept the web request.');
        }

        $this->requestId = $sent;
        WebRequestDispatcher::track($sent, $this);

        return new WaitForWebRequest($this);
    }

    /**
     * Stops the request if it is still in progress. Its result becomes
     * {@see WebRequestResult::Cancelled} at once, and
     * {@see WebRequest::$completed} fires at the start of the next frame.
     * Does nothing to a request that has finished or was never sent.
     */
    public function cancel(): void
    {
        if ($this->requestId === null || $this->outcome !== null) {
            return;
        }

        NativeEngine::call('web_request_cancel', $this->requestId);
        $this->readOutcome();
    }

    /**
     * Encodes data as JSON into the body and sets the Content-Type header to
     * application/json.
     *
     * @param mixed $data Arrays, objects implementing JsonSerializable, and
     *     scalars.
     * @throws JsonException When the data cannot be encoded.
     */
    public function encodeJsonBody(mixed $data): void
    {
        $this->body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->headers['Content-Type'] = 'application/json';
    }

    /**
     * Decodes the response body as JSON. Objects become associative arrays.
     *
     * @return mixed The decoded value: an array for a JSON object or list, or
     *     a string, number, bool or null.
     * @throws JsonException When the body is not valid JSON.
     */
    public function decodeJsonResponse(): mixed
    {
        return json_decode($this->text, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Stops the request if it is still in progress when PHP shuts down. A
     * sent request is held until it finishes, so this only happens when the
     * engine stops first.
     */
    public function __destruct()
    {
        if ($this->requestId !== null && $this->outcome === null && NativeEngine::hasFunction('web_request_release')) {
            NativeEngine::call('web_request_release', $this->requestId);
        }
    }

    /**
     * Reads the finished request and runs the completed listeners. Called by
     * the engine through WebRequestDispatcher.
     */
    private function notifyCompleted(): void
    {
        $this->readOutcome();
        $this->completed->dispatch($this);
    }

    /**
     * The finished request's state, or null while it is in progress or before
     * it is sent. The first time it sees the request finished it keeps the
     * state and lets the engine forget the request.
     *
     * @return array{result: string, statusCode?: int, error?: string, body?: string, headers?: array<string, string>}|null
     */
    private function readOutcome(): ?array
    {
        if ($this->outcome !== null || $this->requestId === null) {
            return $this->outcome;
        }

        $state = NativeEngine::call('web_request_poll', $this->requestId);
        if (!is_array($state)) {
            // The engine no longer knows the request: it stopped running.
            $this->outcome = [
                'result' => WebRequestResult::Cancelled->value,
                'error' => 'The engine stopped before the request finished.',
            ];

            return $this->outcome;
        }

        if (($state['result'] ?? null) === WebRequestResult::InProgress->value) {
            return null;
        }

        $this->outcome = $state;
        NativeEngine::call('web_request_release', $this->requestId);

        return $this->outcome;
    }
}
