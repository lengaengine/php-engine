<?php

declare(strict_types=1);

namespace {
    /**
     * @param array<string, string> $headers
     */
    function lenga_internal_web_request_send(
        string $host,
        string $path,
        string $method,
        array $headers,
        ?string $body,
        float $timeout,
    ): int|string {
        $state = &$GLOBALS['lenga_web_request_test_state'];
        $state['sent'][] = compact('host', 'path', 'method', 'headers', 'body', 'timeout');

        return $state['sendAnswer'];
    }

    /**
     * @return array<string, mixed>|false
     */
    function lenga_internal_web_request_poll(int $id): array|false
    {
        $state = &$GLOBALS['lenga_web_request_test_state'];
        $state['polls'][] = $id;

        return $state['requests'][$id] ?? false;
    }

    function lenga_internal_web_request_cancel(int $id): bool
    {
        $state = &$GLOBALS['lenga_web_request_test_state'];
        $state['cancels'][] = $id;
        if (!isset($state['requests'][$id])) {
            return false;
        }
        if ($state['requests'][$id]['result'] === 'InProgress') {
            $state['requests'][$id] = [
                'result' => 'Cancelled',
                'statusCode' => 0,
                'error' => 'The request was cancelled.',
                'body' => '',
                'headers' => [],
            ];
        }

        return true;
    }

    function lenga_internal_web_request_release(int $id): bool
    {
        $state = &$GLOBALS['lenga_web_request_test_state'];
        $state['releases'][] = $id;
        $known = isset($state['requests'][$id]);
        unset($state['requests'][$id]);

        return $known;
    }
}

namespace Lenga\Engine\Tests\Networking {
    use InvalidArgumentException;
    use JsonException;
    use Lenga\Engine\Core\WaitForWebRequest;
    use Lenga\Engine\Core\YieldInstruction;
    use Lenga\Engine\Enumerations\WebRequestMethod;
    use Lenga\Engine\Enumerations\WebRequestResult;
    use Lenga\Engine\Exceptions\LengaRuntimeException;
    use Lenga\Engine\Exceptions\WebRequestRefusedException;
    use Lenga\Engine\Internal\WebRequestDispatcher;
    use Lenga\Engine\Networking\WebRequest;
    use LogicException;
    use PHPUnit\Framework\TestCase;

    final class WebRequestTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_web_request_test_state'] = [
                'sendAnswer' => 7,
                'sent' => [],
                'polls' => [],
                'cancels' => [],
                'releases' => [],
                'requests' => [7 => ['result' => 'InProgress']],
            ];
        }

        protected function tearDown(): void
        {
            // The engine gives every request its own id; this fake reuses 7.
            // Finish and dispatch it so no test's request stays held for the
            // next test.
            $state = &$GLOBALS['lenga_web_request_test_state'];
            if (\is_int($state['sendAnswer']) && $state['sent'] !== []) {
                $state['requests'][7] ??= ['result' => 'Cancelled', 'statusCode' => 0, 'error' => '', 'body' => '', 'headers' => []];
                if ($state['requests'][7]['result'] === 'InProgress') {
                    $state['requests'][7]['result'] = 'Cancelled';
                }
                WebRequestDispatcher::dispatchFinished(7);
            }
        }

        public function testSendHandsTheEngineTheHostPathMethodHeadersBodyAndTimeout(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores?limit=10', WebRequestMethod::Post);
            $request->headers['Authorization'] = 'Bearer token-1';
            $request->body = 'score=10';
            $request->timeout = 2.5;

            $wait = $request->send();

            self::assertInstanceOf(WaitForWebRequest::class, $wait);
            self::assertSame($request, $wait->request);
            self::assertSame([[
                'host' => 'leaderboard',
                'path' => '/v1/scores?limit=10',
                'method' => 'POST',
                'headers' => ['Authorization' => 'Bearer token-1'],
                'body' => 'score=10',
                'timeout' => 2.5,
            ]], $this->state()['sent']);
        }

        public function testARequestDefaultsToGetWithNoBodyAndNoTimeout(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();

            $sent = $this->state()['sent'][0];
            self::assertSame(WebRequestMethod::Get, $request->method);
            self::assertSame('GET', $sent['method']);
            self::assertNull($sent['body']);
            self::assertSame(0.0, $sent['timeout']);
            self::assertSame([], $sent['headers']);
        }

        public function testARequestIsInProgressUntilTheEngineReportsItFinished(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            self::assertFalse($request->isDone, 'not done before it is sent');
            self::assertSame(WebRequestResult::InProgress, $request->result);
            self::assertSame([], $this->state()['polls'], 'an unsent request asks the engine nothing');

            $wait = $request->send();
            self::assertFalse($request->isDone);
            self::assertTrue($wait->keepWaiting());
            self::assertSame(WebRequestResult::InProgress, $request->result);
            self::assertSame(0, $request->responseCode);
            self::assertSame('', $request->text);

            $this->finish(7, 'Success', 200, '{"rank":3}', ['content-type' => 'application/json']);

            self::assertTrue($request->isDone);
            self::assertFalse($wait->keepWaiting());
            self::assertSame(WebRequestResult::Success, $request->result);
            self::assertSame(200, $request->responseCode);
            self::assertSame('{"rank":3}', $request->text);
            self::assertSame(['content-type' => 'application/json'], $request->responseHeaders);
            self::assertSame('', $request->error);
        }

        public function testAFinishedRequestIsKeptInPhpAndReleasedInTheEngineOnce(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();
            $this->finish(7, 'ProtocolError', 404, 'missing', [], 'HTTP 404');

            self::assertSame(WebRequestResult::ProtocolError, $request->result);
            self::assertSame(404, $request->responseCode);
            self::assertSame('HTTP 404', $request->error);
            self::assertSame('missing', $request->text);
            $pollsAfterFinish = \count($this->state()['polls']);

            unset($request);

            self::assertSame([7], $this->state()['releases']);
            self::assertSame(1, $pollsAfterFinish, 'reads after the first finished answer do not ask again');
        }

        public function testTheEngineRefusingARequestThrowsWithItsReason(): void
        {
            $GLOBALS['lenga_web_request_test_state']['sendAnswer'] = 'Host "scores" is not one of the project\'s hosts.';
            $request = new WebRequest('scores', '/v1/scores');

            try {
                $request->send();
                self::fail('send() should throw');
            } catch (WebRequestRefusedException $exception) {
                self::assertInstanceOf(LengaRuntimeException::class, $exception);
                self::assertSame('Host "scores" is not one of the project\'s hosts.', $exception->getMessage());
            }

            self::assertFalse($request->isDone);
        }

        public function testARequestIsSentOnce(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();

            $this->expectException(LogicException::class);
            $request->send();
        }

        public function testANegativeOrNonFiniteTimeoutIsRefused(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            foreach ([-1.0, NAN, INF] as $timeout) {
                try {
                    $request->timeout = $timeout;
                    self::fail("timeout {$timeout} should be refused");
                } catch (InvalidArgumentException) {
                    self::assertSame(0.0, $request->timeout);
                }
            }
        }

        public function testCancellingARequestInProgressEndsItAsCancelled(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();

            $request->cancel();

            self::assertSame([7], $this->state()['cancels']);
            self::assertTrue($request->isDone);
            self::assertSame(WebRequestResult::Cancelled, $request->result);
            self::assertSame('The request was cancelled.', $request->error);

            $request->cancel();
            self::assertSame([7], $this->state()['cancels'], 'a finished request is not cancelled again');
        }

        public function testCancellingFiresCompletedWhenTheEngineReportsIt(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $heard = [];
            $request->completed->add(static function (WebRequest $done) use (&$heard): void {
                $heard[] = $done->result;
            });
            $request->send();

            $request->cancel();
            self::assertSame([], $heard, 'listeners run when the engine reports the end, not inside cancel()');

            WebRequestDispatcher::dispatchFinished(7);
            self::assertSame([WebRequestResult::Cancelled], $heard);
        }

        public function testCancellingAnUnsentRequestDoesNothing(): void
        {
            (new WebRequest('leaderboard', '/v1/scores'))->cancel();

            self::assertSame([], $this->state()['cancels']);
        }

        public function testASentRequestTheScriptDropsKeepsRunningUntilItFinishes(): void
        {
            $pendingBefore = WebRequestDispatcher::getPendingCount();
            $heard = [];
            (static function () use (&$heard): void {
                $request = new WebRequest('leaderboard', '/v1/scores');
                $request->completed->add(static function (WebRequest $done) use (&$heard): void {
                    $heard[] = [$done->path, $done->result, $done->responseCode];
                });
                $request->send();
            })();

            self::assertSame([], $this->state()['releases'], 'a dropped request is not stopped');
            self::assertSame([], $this->state()['cancels']);
            self::assertSame($pendingBefore + 1, WebRequestDispatcher::getPendingCount());

            $this->finish(7, 'Success', 200, 'ok', []);
            WebRequestDispatcher::dispatchFinished(7);

            self::assertSame([['/v1/scores', WebRequestResult::Success, 200]], $heard);
            self::assertSame([7], $this->state()['releases'], 'the engine forgets it once its answer is read');
            self::assertSame($pendingBefore, WebRequestDispatcher::getPendingCount());
        }

        public function testCompletedFiresOnceWithTheRequestItself(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $received = [];
            $request->completed->add(static function (WebRequest $done) use (&$received): void {
                $received[] = $done;
            });
            $request->send();

            self::assertSame([], $received, 'nothing fires while the request runs');

            $this->finish(7, 'ProtocolError', 404, 'missing', [], 'HTTP 404');
            self::assertTrue($request->isDone, 'a script may read the answer before the listener runs');
            WebRequestDispatcher::dispatchFinished(7);
            WebRequestDispatcher::dispatchFinished(7);

            self::assertCount(1, $received);
            self::assertSame($request, $received[0]);
            self::assertSame(WebRequestResult::ProtocolError, $received[0]->result);
            self::assertSame('missing', $received[0]->text);
        }

        public function testDispatchingAnUnknownRequestDoesNothing(): void
        {
            WebRequestDispatcher::dispatchFinished(4242);

            self::assertSame([], $this->state()['polls']);
        }

        public function testCompletedIsASignal(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');

            self::assertInstanceOf(\Lenga\Engine\Core\Signal::class, $request->completed);
            self::assertFalse($request->completed->hasListeners());
        }

        public function testARequestTheEngineNoLongerKnowsEndsAsCancelled(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();
            unset($GLOBALS['lenga_web_request_test_state']['requests'][7]);

            self::assertTrue($request->isDone);
            self::assertSame(WebRequestResult::Cancelled, $request->result);
            self::assertNotSame('', $request->error);
        }

        public function testEncodeJsonBodySetsTheBodyAndContentType(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores', WebRequestMethod::Post);
            $request->encodeJsonBody(['player' => 'Ada/1', 'score' => 1200, 'name' => 'Zoë']);

            self::assertSame('{"player":"Ada/1","score":1200,"name":"Zoë"}', $request->body);
            self::assertSame(['Content-Type' => 'application/json'], $request->headers);
        }

        public function testEncodeJsonBodyThrowsForDataJsonCannotHold(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores', WebRequestMethod::Post);

            $this->expectException(JsonException::class);
            $request->encodeJsonBody(['score' => NAN]);
        }

        public function testDecodeJsonResponseGivesArrays(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();
            $this->finish(7, 'Success', 200, '{"scores":[{"name":"Ada","score":1200}]}', []);

            self::assertSame(['scores' => [['name' => 'Ada', 'score' => 1200]]], $request->decodeJsonResponse());
        }

        public function testDecodeJsonResponseThrowsForABodyThatIsNotJson(): void
        {
            $request = new WebRequest('leaderboard', '/v1/scores');
            $request->send();
            $this->finish(7, 'Success', 200, '<html>', []);

            $this->expectException(JsonException::class);
            $request->decodeJsonResponse();
        }

        public function testWaitForWebRequestIsAYieldInstructionResumedOnUpdate(): void
        {
            $wait = new WaitForWebRequest(new WebRequest('leaderboard', '/v1/scores'));

            self::assertInstanceOf(YieldInstruction::class, $wait);
            self::assertSame(YieldInstruction::PHASE_UPDATE, $wait->resumePhase());
        }

        public function testResultNamesAreTheEnginesNames(): void
        {
            self::assertSame(
                ['InProgress', 'Success', 'ConnectionError', 'ProtocolError', 'TimedOut', 'Cancelled'],
                array_map(static fn (WebRequestResult $result): string => $result->value, WebRequestResult::cases())
            );
            self::assertSame(
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'],
                array_map(static fn (WebRequestMethod $method): string => $method->value, WebRequestMethod::cases())
            );
        }

        /**
         * @return array<string, mixed>
         */
        private function state(): array
        {
            return $GLOBALS['lenga_web_request_test_state'];
        }

        /**
         * @param array<string, string> $headers
         */
        private function finish(int $id, string $result, int $statusCode, string $body, array $headers, string $error = ''): void
        {
            $GLOBALS['lenga_web_request_test_state']['requests'][$id] = [
                'result' => $result,
                'statusCode' => $statusCode,
                'error' => $error,
                'body' => $body,
                'headers' => $headers,
            ];
        }
    }
}
