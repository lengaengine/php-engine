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

    public static function uiTextNumberTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
        int $decimals = 0,
        string $prefix = '',
        string $suffix = '',
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiTextNumberTo(
            self::requireUIElementId($target),
            $to,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
            $decimals,
            $prefix,
            $suffix,
        );

        return new TweenHandle($id);
    }

    public static function uiPropertyTo(
        UIElement $target,
        string $property,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        $id = Bindings::tweenUiScalarTo(
            self::requireUIElementId($target),
            $property,
            $to,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    public static function uiValueTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'value', $to, $duration, $options);
    }

    public static function uiFontSizeTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'fontSize', $to, $duration, $options);
    }

    public static function uiOutlineWidthTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'outlineWidth', $to, $duration, $options);
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
        string $property = 'color',
    ): TweenHandle {
        $options ??= TweenOptions::make();
        [$red, $green, $blue, $alpha] = self::normalizeColor($to);
        $id = Bindings::tweenUiColorTo(
            self::requireUIElementId($target),
            $property,
            $red,
            $green,
            $blue,
            $alpha,
            $duration,
            $options->delay,
            $options->easingFunction->value,
            $options->useUnscaledTime,
            $options->relative,
        );

        return new TweenHandle($id);
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiTextColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'textColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiBackgroundColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'backgroundColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiFillColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'fillColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiHandleColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'handleColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiOutlineColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'outlineColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiHoverColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'hoverColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiPressedColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'pressedColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiDisabledColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'disabledColor');
    }

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to
     */
    public static function uiCheckmarkColorTo(
        UIElement $target,
        array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'checkmarkColor');
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

    /**
     * @param array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $color
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private static function normalizeColor(array $color): array
    {
        $red = $color['r'] ?? $color[0] ?? 255;
        $green = $color['g'] ?? $color[1] ?? 255;
        $blue = $color['b'] ?? $color[2] ?? 255;
        $alpha = $color['a'] ?? $color[3] ?? 255;

        return [
            (float) \max(0, \min(255, $red)),
            (float) \max(0, \min(255, $green)),
            (float) \max(0, \min(255, $blue)),
            (float) \max(0, \min(255, $alpha)),
        ];
    }
}
