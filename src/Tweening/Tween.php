<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

use Lenga\Engine\Core\Transform;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Internal\Bindings;
use Lenga\Engine\UI\RectTransform;
use Lenga\Engine\UI\UIElement;

final class Tween
{
    public static function moveTo(
        Transform $transform,
        Vector3 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenMoveTo(
            self::requireTransformId($transform),
            $to->x,
            $to->y,
            $to->z,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function moveLocalTo(
        Transform $transform,
        Vector3 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenMoveLocalTo(
            self::requireTransformId($transform),
            $to->x,
            $to->y,
            $to->z,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function scaleTo(
        Transform $transform,
        Vector3 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenScaleTo(
            self::requireTransformId($transform),
            $to->x,
            $to->y,
            $to->z,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function rotateTo(
        Transform $transform,
        Vector3 $toEulerAngles,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenRotateTo(
            self::requireTransformId($transform),
            $toEulerAngles->x,
            $toEulerAngles->y,
            $toEulerAngles->z,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function rotateLocalTo(
        Transform $transform,
        Vector3 $toEulerAngles,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenRotateLocalTo(
            self::requireTransformId($transform),
            $toEulerAngles->x,
            $toEulerAngles->y,
            $toEulerAngles->z,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiMoveTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::anchoredPositionTo($target, $to, $duration, $options);
    }

    public static function anchoredPositionTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiAnchoredPositionTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiScaleTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiScaleTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiSizeTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiSizeDeltaTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiRotateTo(
        UIElement|RectTransform $target,
        float $toDegrees,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiRotationTo(
            self::requireUIElementId($target),
            $toDegrees,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiAnchorMinTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiAnchorMinTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiAnchorMaxTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiAnchorMaxTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiPivotTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiPivotTo(
            self::requireUIElementId($target),
            $to->x,
            $to->y,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    private static function requireTransformId(Transform $transform): int
    {
        $transformId = $transform->getNativeId();
        if ($transformId === null) {
            throw new \LogicException('Tweening requires a native Transform instance.');
        }

        return $transformId;
    }

    private static function requireUIElementId(UIElement|RectTransform $target): int
    {
        return $target instanceof UIElement ? $target->getId() : $target->getElementId();
    }
}
