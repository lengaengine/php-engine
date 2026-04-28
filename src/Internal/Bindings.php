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

    // ---------------------------------------------------------------------
    // Tweening
    // ---------------------------------------------------------------------

    public static function tweenMoveTo(
        int $transformId,
        float $x,
        float $y,
        float $z,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_move_to_by_transform_id(
            $transformId,
            $x,
            $y,
            $z,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenMoveLocalTo(
        int $transformId,
        float $x,
        float $y,
        float $z,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_move_local_to_by_transform_id(
            $transformId,
            $x,
            $y,
            $z,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenScaleTo(
        int $transformId,
        float $x,
        float $y,
        float $z,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_scale_to_by_transform_id(
            $transformId,
            $x,
            $y,
            $z,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenRotateTo(
        int $transformId,
        float $x,
        float $y,
        float $z,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_rotate_to_by_transform_id(
            $transformId,
            $x,
            $y,
            $z,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenRotateLocalTo(
        int $transformId,
        float $x,
        float $y,
        float $z,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_rotate_local_to_by_transform_id(
            $transformId,
            $x,
            $y,
            $z,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiAnchorMinTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_anchor_min_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiAnchorMaxTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_anchor_max_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiPivotTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_pivot_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiAnchoredPositionTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_anchored_position_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiSizeDeltaTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_size_delta_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiScaleTo(
        int $elementId,
        float $x,
        float $y,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_scale_to_by_element_id(
            $elementId,
            $x,
            $y,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenUiRotationTo(
        int $elementId,
        float $rotation,
        float $duration,
        float $delay,
        string $ease,
        bool $useUnscaledTime,
        bool $relative,
    ): int {
        return \lenga_internal_tween_ui_rotation_to_by_element_id(
            $elementId,
            $rotation,
            $duration,
            $delay,
            $ease,
            $useUnscaledTime,
            $relative,
        );
    }

    public static function tweenPause(int $tweenId): bool
    {
        return \lenga_internal_tween_pause($tweenId);
    }

    public static function tweenResume(int $tweenId): bool
    {
        return \lenga_internal_tween_resume($tweenId);
    }

    public static function tweenCancel(int $tweenId): bool
    {
        return \lenga_internal_tween_cancel($tweenId);
    }

    public static function tweenIsComplete(int $tweenId): bool
    {
        return \lenga_internal_tween_is_complete($tweenId);
    }

    public static function tweenIsPlaying(int $tweenId): bool
    {
        return \lenga_internal_tween_is_playing($tweenId);
    }

    public static function tweenExists(int $tweenId): bool
    {
        return \lenga_internal_tween_exists($tweenId);
    }
}
