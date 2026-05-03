<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use Closure;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\GameObject;

/**
 * Internal adapter used by the native runtime to attach engine-owned state to PHP behaviours.
 *
 * Gameplay code should use Behaviour's public API rather than this bridge.
 */
final class BehaviourBridge
{
    /**
     * @var array<string, Closure>
     */
    private static array $internalCalls = [];

    private function __construct()
    {
    }

    public static function attachGameObject(Behaviour $behaviour, GameObject $gameObject): void
    {
        self::callInternal($behaviour, 'internalAttachGameObject', $gameObject);
    }

    public static function attachComponentId(Behaviour $behaviour, int $componentId): void
    {
        self::callInternal($behaviour, 'internalAttachComponentId', $componentId);
    }

    public static function attachSceneComponentId(Behaviour $behaviour, string $sceneComponentId): void
    {
        self::callInternal($behaviour, 'internalAttachSceneComponentId', $sceneComponentId);
    }

    /**
     * @param array<string, mixed> $properties
     */
    public static function applyProperties(Behaviour $behaviour, array $properties): void
    {
        self::callInternal($behaviour, 'internalApplyProperties', $properties);
    }

    public static function invokeLifecycle(Behaviour $behaviour, string $methodName): void
    {
        match ($methodName) {
            'awake' => self::callInternal($behaviour, 'internalAwake'),
            'onEnable' => self::callInternal($behaviour, 'internalOnEnable'),
            'start' => self::callInternal($behaviour, 'internalStart'),
            'fixedUpdate' => self::callInternal($behaviour, 'internalFixedUpdate'),
            'update' => self::callInternal($behaviour, 'internalUpdate'),
            'lateUpdate' => self::callInternal($behaviour, 'internalLateUpdate'),
            'onDisable' => self::callInternal($behaviour, 'internalOnDisable'),
            'onDestroy' => self::callInternal($behaviour, 'internalOnDestroy'),
            default => null,
        };
    }

    private static function callInternal(Behaviour $behaviour, string $methodName, mixed ...$arguments): mixed
    {
        $call = self::$internalCalls[$methodName] ??= Closure::bind(
            static function (Behaviour $target, mixed ...$arguments) use ($methodName): mixed {
                return $target->{$methodName}(...$arguments);
            },
            null,
            Behaviour::class
        );

        return $call($behaviour, ...$arguments);
    }
}
