<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\EventBus;
use Lenga\Engine\Core\SignalSubscription;
use Lenga\Engine\Internal\BehaviourBridge;
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

        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
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

        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
        $dispatcher->fire();
        BehaviourBridge::invokeLifecycle($listener, 'onDisable');
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

        BehaviourBridge::invokeLifecycle($listener, 'awake');
        BehaviourBridge::invokeLifecycle($listener, 'onDisable');
        $dispatcher->fire();
        BehaviourBridge::invokeLifecycle($listener, 'onDestroy');
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

        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
        EventBus::emit('game.custom');
        BehaviourBridge::invokeLifecycle($listener, 'onDisable');
        EventBus::emit('game.custom');

        self::assertSame(1, $listener->count);
        self::assertNotNull($listener->subscription);
        self::assertTrue($listener->subscription->isDisposed());
    }

    public function testRepeatedEnableDoesNotDuplicateEventSubscriptions(): void
    {
        $listener = new class extends Behaviour {
            public int $enableCalls = 0;
            public int $count = 0;

            public function onEnable(): void
            {
                ++$this->enableCalls;
                $this->onEvent('game.ball.out_of_bounds', function (): void {
                    ++$this->count;
                });
            }
        };

        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
        EventBus::emit('game.ball.out_of_bounds');

        self::assertSame(1, $listener->enableCalls);
        self::assertSame(1, $listener->count);
        self::assertSame(1, EventBus::listenerCount('game.ball.out_of_bounds'));
    }

    public function testDuplicateNativeBehaviourProxyReplacesSameCallsiteSubscription(): void
    {
        $factory = static fn (): Behaviour => new class extends Behaviour {
            public int $count = 0;

            public function onEnable(): void
            {
                $this->subscribeToOutOfBounds();
            }

            public function subscribeToOutOfBounds(): void
            {
                $this->onEvent('game.ball.out_of_bounds', function (): void {
                    ++$this->count;
                });
            }
        };

        $first = $factory();
        $second = $factory();

        BehaviourBridge::attachComponentId($first, 42);
        BehaviourBridge::attachComponentId($second, 42);
        BehaviourBridge::invokeLifecycle($first, 'onEnable');
        BehaviourBridge::invokeLifecycle($second, 'onEnable');
        EventBus::emit('game.ball.out_of_bounds');

        self::assertSame(0, $first->count);
        self::assertSame(1, $second->count);
        self::assertSame(1, EventBus::listenerCount('game.ball.out_of_bounds'));
    }

    public function testDistinctOnEventCallsitesOnSameBehaviourAreBothAllowed(): void
    {
        $listener = new class extends Behaviour {
            public int $count = 0;

            public function onEnable(): void
            {
                $this->onEvent('game.dual', function (): void {
                    $this->count += 1;
                });
                $this->onEvent('game.dual', function (): void {
                    $this->count += 10;
                });
            }
        };

        BehaviourBridge::attachComponentId($listener, 77);
        BehaviourBridge::invokeLifecycle($listener, 'onEnable');
        EventBus::emit('game.dual');

        self::assertSame(11, $listener->count);
        self::assertSame(2, EventBus::listenerCount('game.dual'));
    }

}
