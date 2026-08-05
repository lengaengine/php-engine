<?php

declare(strict_types=1);

namespace {
    function lenga_internal_game_object_get_name(int $gameObjectId): string
    {
        return $GLOBALS['lenga_require_component_test_state']['names'][$gameObjectId] ?? 'GameObject';
    }

    function lenga_internal_game_object_has_component_by_id(
        int $gameObjectId,
        string $nativeType,
        ?string $scriptClass = null,
    ): bool {
        $key = $scriptClass ?? $nativeType;

        return isset($GLOBALS['lenga_require_component_test_state']['components'][$gameObjectId][$key]);
    }

    /**
     * @return array{id: int, type: string, gameObjectId: int}
     */
    function lenga_internal_game_object_add_component_by_id(
        int $gameObjectId,
        string $nativeType,
        ?string $scriptClass = null,
    ): array {
        $key = $scriptClass ?? $nativeType;
        $state = &$GLOBALS['lenga_require_component_test_state'];

        if (($state['failAdd'] ?? null) === $key) {
            throw new RuntimeException("Factory rejected {$key}");
        }

        $componentId = ++$state['nextComponentId'];
        $state['components'][$gameObjectId][$key] = true;
        $state['added'][] = [
            'gameObjectId' => $gameObjectId,
            'nativeType' => $nativeType,
            'scriptClass' => $scriptClass,
        ];

        return [
            'id' => $componentId,
            'type' => $nativeType,
            'gameObjectId' => $gameObjectId,
        ];
    }

    function lenga_internal_debug_log(string $message, string $level): void
    {
        $GLOBALS['lenga_require_component_test_state']['logs'][] = [
            'level' => $level,
            'message' => $message,
        ];
    }
}

namespace Lenga\Engine\Tests\Core {
    use Lenga\Engine\Attributes\RequireComponent;
    use Lenga\Engine\Core\Behaviour;
    use Lenga\Engine\Core\CharacterController;
    use Lenga\Engine\Core\GameObject;
    use Lenga\Engine\Internal\BehaviourBridge;
    use PHPUnit\Framework\TestCase;
    use RuntimeException;

    #[RequireComponent(CharacterController::class)]
    final class RequireCharacterControllerBehaviour extends Behaviour
    {
        public int $onEnableCalls = 0;
        public int $updateCalls = 0;

        public function onEnable(): void
        {
            $this->onEnableCalls++;
        }

        public function update(): void
        {
            $this->updateCalls++;
        }
    }

    #[RequireComponent('MissingNativeComponent')]
    final class RequireMissingNativeComponentBehaviour extends Behaviour
    {
    }

    final class RequireComponentTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_require_component_test_state'] = [
                'added' => [],
                'components' => [],
                'failAdd' => null,
                'logs' => [],
                'names' => [42 => 'Roller'],
                'nextComponentId' => 100,
            ];
        }

        public function testRequireComponentAutoAddsMissingDependencyDuringAttachment(): void
        {
            $gameObject = new GameObject('Roller', instanceId: 42);
            $behaviour = new RequireCharacterControllerBehaviour();

            BehaviourBridge::attachGameObject($behaviour, $gameObject);
            BehaviourBridge::attachComponentId($behaviour, 77);

            self::assertTrue($gameObject->hasComponent(CharacterController::class));
            self::assertSame(
                [
                    [
                        'gameObjectId' => 42,
                        'nativeType' => 'CharacterController',
                        'scriptClass' => null,
                    ],
                ],
                $GLOBALS['lenga_require_component_test_state']['added'],
            );
            self::assertSame([], $GLOBALS['lenga_require_component_test_state']['logs']);
        }

        public function testRequireComponentResolutionDoesNotScheduleDisabledOnlyLifecycleCallbacks(): void
        {
            $gameObject = new GameObject('Roller', instanceId: 42);
            $behaviour = new RequireCharacterControllerBehaviour();

            BehaviourBridge::attachGameObject($behaviour, $gameObject);
            BehaviourBridge::attachComponentId($behaviour, 77);

            self::assertSame(0, $behaviour->onEnableCalls);
            self::assertSame(0, $behaviour->updateCalls);
            self::assertTrue($gameObject->hasComponent(CharacterController::class));
        }

        public function testMissingRequiredComponentTypeLogsClearDiagnostic(): void
        {
            $GLOBALS['lenga_require_component_test_state']['failAdd'] = 'MissingNativeComponent';

            $gameObject = new GameObject('Roller', instanceId: 42);
            $behaviour = new RequireMissingNativeComponentBehaviour();

            try {
                BehaviourBridge::attachGameObject($behaviour, $gameObject);
                self::fail('Expected RequireComponent dependency resolution to fail.');
            } catch (RuntimeException $exception) {
                self::assertStringContainsString('RequireComponent dependency resolution failed', $exception->getMessage());
                self::assertStringContainsString(RequireMissingNativeComponentBehaviour::class, $exception->getMessage());
                self::assertStringContainsString("gameObject='Roller'", $exception->getMessage());
                self::assertStringContainsString("requiredComponent='MissingNativeComponent'", $exception->getMessage());
                self::assertStringContainsString('attemptedAutoAdd=true', $exception->getMessage());
            }

            self::assertNotEmpty($GLOBALS['lenga_require_component_test_state']['logs']);
            self::assertSame('error', $GLOBALS['lenga_require_component_test_state']['logs'][0]['level']);
            self::assertStringContainsString(
                "requiredComponent='MissingNativeComponent'",
                $GLOBALS['lenga_require_component_test_state']['logs'][0]['message'],
            );
        }
    }
}
