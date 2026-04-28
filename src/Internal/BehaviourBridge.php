<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\GameObject;

/**
 * Internal adapter used by the native runtime to attach engine-owned state to PHP behaviours.
 *
 * Gameplay code should use Behaviour's public API rather than this bridge.
 */
final class BehaviourBridge
{
    private function __construct()
    {
    }

    public static function attachGameObject(Behaviour $behaviour, GameObject $gameObject): void
    {
        $behaviour->__internalAttachGameObject($gameObject);
    }

    public static function attachComponentId(Behaviour $behaviour, int $componentId): void
    {
        $behaviour->__internalAttachComponentId($componentId);
    }

    public static function attachSceneComponentId(Behaviour $behaviour, string $sceneComponentId): void
    {
        $behaviour->__internalAttachSceneComponentId($sceneComponentId);
    }

    /**
     * @param array<string, mixed> $properties
     */
    public static function applyProperties(Behaviour $behaviour, array $properties): void
    {
        $behaviour->__internalApplyProperties($properties);
    }

    public static function invokeLifecycle(Behaviour $behaviour, string $methodName): void
    {
        match ($methodName) {
            'awake' => $behaviour->__lengaInternalAwake(),
            'onEnable' => $behaviour->__lengaInternalOnEnable(),
            'start' => $behaviour->__lengaInternalStart(),
            'fixedUpdate' => $behaviour->__lengaInternalFixedUpdate(),
            'update' => $behaviour->__lengaInternalUpdate(),
            'lateUpdate' => $behaviour->__lengaInternalLateUpdate(),
            'onDisable' => $behaviour->__lengaInternalOnDisable(),
            'onDestroy' => $behaviour->__lengaInternalOnDestroy(),
            default => null,
        };
    }
}
