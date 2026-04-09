<?php

declare(strict_types=1);

namespace Lenga\Engine\Internal;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\Transform;

/**
 * Internal bridge between PHP and the native C++ runtime.
 *
 * All methods here are meant to be implemented by the native PHP extension.
 * This PHP file mainly exists for:
 *  - autoloading
 *  - static analysis
 *  - IDE completion
 */
final class Bindings
{
    private function __construct()
    {
        // static-only
    }

    // ---------------------------------------------------------------------
    // Behaviour → GameObject
    // ---------------------------------------------------------------------

    /**
     * Look up the GameObject attached to this Behaviour.
     *
     * Native side:
     *   PHP_METHOD(Bindings, getGameObjectForBehaviour)
     */
    public static function getGameObjectForBehaviour(Behaviour $behaviour): GameObject
    {
        throw new \LogicException('Bindings::getGameObjectForBehaviour() must be provided by the native extension.');
    }

    // ---------------------------------------------------------------------
    // GameObject core
    // ---------------------------------------------------------------------

    public static function getGameObjectName(int $gameObjectId): string
    {
        throw new \LogicException('Bindings::getGameObjectName() must be provided by the native extension.');
    }

    public static function setGameObjectName(int $gameObjectId, string $name): void
    {
        throw new \LogicException('Bindings::setGameObjectName() must be provided by the native extension.');
    }

    public static function getGameObjectActiveSelf(int $gameObjectId): bool
    {
        throw new \LogicException('Bindings::getGameObjectActiveSelf() must be provided by the native extension.');
    }

    public static function getGameObjectActiveInHierarchy(int $gameObjectId): bool
    {
        throw new \LogicException('Bindings::getGameObjectActiveInHierarchy() must be provided by the native extension.');
    }

    public static function setGameObjectActive(int $gameObjectId, bool $active): void
    {
        throw new \LogicException('Bindings::setGameObjectActive() must be provided by the native extension.');
    }

    /**
     * Returns a Transform proxy for the given GameObject id.
     */
    public static function getTransformForGameObject(int $gameObjectId): Transform
    {
        throw new \LogicException('Bindings::getTransformForGameObject() must be provided by the native extension.');
    }

    // ---------------------------------------------------------------------
    // Transform core
    // ---------------------------------------------------------------------

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformPosition(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformPosition() must be provided by the native extension.');
    }

    public static function setTransformPosition(
        int $transformId,
        float $x,
        float $y,
        float $z,
    ): void {
        throw new \LogicException('Bindings::setTransformPosition() must be provided by the native extension.');
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformRotation(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformRotation() must be provided by the native extension.');
    }

    public static function setTransformRotation(
        int $transformId,
        float $x,
        float $y,
        float $z,
    ): void {
        throw new \LogicException('Bindings::setTransformRotation() must be provided by the native extension.');
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformScale(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformScale() must be provided by the native extension.');
    }

    public static function setTransformScale(
        int $transformId,
        float $x,
        float $y,
        float $z,
    ): void {
        throw new \LogicException('Bindings::setTransformScale() must be provided by the native extension.');
    }

    // Direction vectors – returned as [x, y, z] and wrapped in Vector3 in PHP

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformForward(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformForward() must be provided by the native extension.');
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformUp(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformUp() must be provided by the native extension.');
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function getTransformRight(int $transformId): array
    {
        throw new \LogicException('Bindings::getTransformRight() must be provided by the native extension.');
    }
}
