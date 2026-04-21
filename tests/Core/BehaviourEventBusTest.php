<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\EventBus;
use Lenga\Engine\Core\SignalSubscription;
use PHPUnit\Framework\TestCase;

final class BehaviourEventBusTest extends TestCase
{
    protected function setUp(): void
    {
        EventBus::clear();
    }

    protected function tearDown(): void
    {
        EventBus::clear();
    }

    public function testBehaviourCanListenForGlobalEventsWithoutKnowingDispatcher(): void
    {
        $listener = new class extends Behaviour {
            /** @var list<mixed> */
            public array $received = [];

            public function onEnable(): void
            {
                $this->onEvent('game.ball.launched', function (mixed $payload): void {
                    $this->received[] = $payload;
                });
            }
        };

        $dispatcher = new class extends Behaviour {
            public function fire(mixed $payload): void
            {
                $this->emitEvent('game.ball.launched', $payload);
            }
        };

        $listener->__lengaInternalOnEnable();
        $dispatcher->fire(['speed' => 2.0]);

        self::assertSame([['speed' => 2.0]], $listener->received);
    }

    public function testOnEventSubscriptionsAreReleasedWhenBehaviourDisables(): void
    {
        $listener = new class extends Behaviour {
            public int $count = 0;

            public function onEnable(): void
            {
                $this->onEvent('game.round.started', function (): void {
                    ++$this->count;
                });
            }
        };

        $dispatcher = new class extends Behaviour {
            public function fire(): void
            {
                $this->dispatchEvent('game.round.started');
            }
        };

        $listener->__lengaInternalOnEnable();
        $dispatcher->fire();
        $listener->__lengaInternalOnDisable();
        $dispatcher->fire();

        self::assertSame(1, $listener->count);
    }

    public function testTrackSubscriptionCanKeepDestroyScopedSubscriptionsAliveAcrossDisable(): void
    {
        $listener = new class extends Behaviour {
            public int $count = 0;

            public function awake(): void
            {
                $this->trackSubscription(
                    EventBus::on('game.session.message', function (): void {
                        ++$this->count;
                    }),
                    false
                );
            }
        };

        $dispatcher = new class extends Behaviour {
            public function fire(): void
            {
                $this->emitEvent('game.session.message');
            }
        };

        $listener->__lengaInternalAwake();
        $listener->__lengaInternalOnDisable();
        $dispatcher->fire();
        $listener->__lengaInternalOnDestroy();
        $dispatcher->fire();

        self::assertSame(1, $listener->count);
    }

    public function testTrackSubscriptionWorksForDirectEventBusSubscriptionHandles(): void
    {
        $listener = new class extends Behaviour {
            public ?SignalSubscription $subscription = null;
            public int $count = 0;

            public function onEnable(): void
            {
                $this->subscription = $this->trackSubscription(
                    EventBus::subscribe('game.custom', function (): void {
                        ++$this->count;
                    })
                );
            }
        };

        $listener->__lengaInternalOnEnable();
        EventBus::emit('game.custom');
        $listener->__lengaInternalOnDisable();
        EventBus::emit('game.custom');

        self::assertSame(1, $listener->count);
        self::assertNotNull($listener->subscription);
        self::assertTrue($listener->subscription->isDisposed());
    }
}
