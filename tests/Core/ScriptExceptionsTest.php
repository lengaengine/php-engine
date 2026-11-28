<?php

declare(strict_types=1);

namespace {
    function lenga_internal_script_exception_report(\Throwable $throwable, string $context): void
    {
        $GLOBALS['lenga_script_exception_reports'][] = [
            'class' => $throwable::class,
            'message' => $throwable->getMessage(),
            'context' => $context,
        ];
    }
}

namespace Lenga\Engine\Tests\Core {
    use Generator;
    use Lenga\Engine\Core\Behaviour;
    use Lenga\Engine\Core\EventBus;
    use Lenga\Engine\Core\Signal;
    use Lenga\Engine\Core\WaitUntil;
    use Lenga\Engine\Internal\BehaviourBridge;
    use Lenga\Engine\Internal\ScriptExceptions;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use RuntimeException;

    final class ThrowingUpdateBehaviour extends Behaviour
    {
        public int $updates = 0;
        public int $coroutineSteps = 0;
        public bool $destroyedCleanly = false;

        public function update(): void
        {
            ++$this->updates;
            throw new RuntimeException("update {$this->updates} failed");
        }

        public function countSteps(): Generator
        {
            while (true) {
                ++$this->coroutineSteps;
                yield null;
            }
        }

        public function failOnSecondStep(): Generator
        {
            yield null;
            throw new LogicException('coroutine failed');
        }

        public function onDestroy(): void
        {
            throw new RuntimeException('onDestroy failed');
        }
    }

    final class ScriptExceptionsTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_script_exception_reports'] = [];
            EventBus::clear();
        }

        public function testAThrowingSignalListenerIsReportedAndTheNextListenerRuns(): void
        {
            $signal = new Signal();
            $heard = [];
            $signal->add(static function (string $value): void {
                throw new RuntimeException("first listener failed on {$value}");
            });
            $signal->add(static function (string $value) use (&$heard): void {
                $heard[] = $value;
            });

            $signal->dispatch('tick');

            self::assertSame(['tick'], $heard);
            $reports = $GLOBALS['lenga_script_exception_reports'];
            self::assertCount(1, $reports);
            self::assertSame(RuntimeException::class, $reports[0]['class']);
            self::assertSame('first listener failed on tick', $reports[0]['message']);
            self::assertStringStartsWith('Signal listener closure in ScriptExceptionsTest.php:', $reports[0]['context']);
        }

        public function testAThrowingOnceListenerIsStillRemoved(): void
        {
            $signal = new Signal();
            $signal->once(static function (): void {
                throw new RuntimeException('once failed');
            });

            $signal->dispatch();
            $signal->dispatch();

            self::assertCount(1, $GLOBALS['lenga_script_exception_reports']);
            self::assertFalse($signal->hasListeners());
        }

        public function testAThrowingEventBusListenerIsReportedAndTheNextListenerRuns(): void
        {
            $heard = [];
            EventBus::on('score.changed', [self::class, 'throwingListener']);
            EventBus::on('score.changed', static function (mixed $payload) use (&$heard): void {
                $heard[] = $payload;
            });

            EventBus::emit('score.changed', 10);

            self::assertSame([10], $heard);
            $reports = $GLOBALS['lenga_script_exception_reports'];
            self::assertCount(1, $reports);
            self::assertSame(
                "EventBus listener for 'score.changed' " . self::class . '::throwingListener()',
                $reports[0]['context']
            );
        }

        public static function throwingListener(): void
        {
            throw new RuntimeException('bus listener failed');
        }

        public function testUpdateThatThrowsIsReportedAndCalledAgainEveryFrame(): void
        {
            $behaviour = new ThrowingUpdateBehaviour();

            BehaviourBridge::invokeLifecycle($behaviour, 'update');
            BehaviourBridge::invokeLifecycle($behaviour, 'update');
            BehaviourBridge::invokeLifecycle($behaviour, 'update');

            self::assertSame(3, $behaviour->updates);
            $reports = $GLOBALS['lenga_script_exception_reports'];
            self::assertSame(['update 1 failed', 'update 2 failed', 'update 3 failed'], array_column($reports, 'message'));
            self::assertSame(ThrowingUpdateBehaviour::class . '::update()', $reports[0]['context']);
        }

        public function testCoroutinesRunInAFrameWhoseUpdateThrows(): void
        {
            $behaviour = new ThrowingUpdateBehaviour();
            $behaviour->startCoroutine($behaviour->countSteps());
            self::assertSame(1, $behaviour->coroutineSteps, 'a coroutine runs to its first yield when started');

            BehaviourBridge::invokeLifecycle($behaviour, 'update');
            BehaviourBridge::invokeLifecycle($behaviour, 'update');

            self::assertSame(3, $behaviour->coroutineSteps, 'the coroutine stepped in both frames');
        }

        public function testACoroutineThatThrowsEndsAndTheOthersCarryOn(): void
        {
            $behaviour = new ThrowingUpdateBehaviour();
            $failing = $behaviour->startCoroutine($behaviour->failOnSecondStep());
            $behaviour->startCoroutine($behaviour->countSteps());

            BehaviourBridge::invokeLifecycle($behaviour, 'update');
            BehaviourBridge::invokeLifecycle($behaviour, 'update');

            self::assertTrue($failing->isFinished(), 'the throwing coroutine ended');
            self::assertSame(3, $behaviour->coroutineSteps, 'the other coroutine kept stepping');
            $coroutineReports = array_values(array_filter(
                $GLOBALS['lenga_script_exception_reports'],
                static fn (array $report): bool => $report['message'] === 'coroutine failed',
            ));
            self::assertCount(1, $coroutineReports, 'the coroutine exception is reported once');
            self::assertSame(ThrowingUpdateBehaviour::class . ' coroutine', $coroutineReports[0]['context']);
        }

        public function testAWaitConditionThatThrowsEndsItsCoroutine(): void
        {
            $behaviour = new ThrowingUpdateBehaviour();
            $routine = (static function (): Generator {
                yield new WaitUntil(static function (): bool {
                    throw new RuntimeException('condition failed');
                });
            })();
            $coroutine = $behaviour->startCoroutine($routine);

            BehaviourBridge::invokeLifecycle($behaviour, 'update');

            self::assertTrue($coroutine->isFinished());
            self::assertContains('condition failed', array_column($GLOBALS['lenga_script_exception_reports'], 'message'));
        }

        public function testOnDestroyCleanupRunsWhenTheScriptThrows(): void
        {
            $behaviour = new ThrowingUpdateBehaviour();
            $coroutine = $behaviour->startCoroutine($behaviour->countSteps());

            try {
                BehaviourBridge::invokeLifecycle($behaviour, 'onDestroy');
                self::fail('the exception reaches the engine, which reports it');
            } catch (RuntimeException $exception) {
                self::assertSame('onDestroy failed', $exception->getMessage());
            }

            self::assertTrue($coroutine->isFinished(), 'coroutines are stopped even though onDestroy() threw');
        }

        public function testListenersAreNamedForTheReport(): void
        {
            self::assertSame('strlen()', ScriptExceptions::describeListener('strlen'));
            self::assertSame(self::class . '::throwingListener()', ScriptExceptions::describeListener([self::class, 'throwingListener']));
            self::assertSame(self::class . '::throwingListener()', ScriptExceptions::describeListener([$this, 'throwingListener']));
        }
    }
}
