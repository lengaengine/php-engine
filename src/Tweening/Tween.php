<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\SpriteRenderer;
use Lenga\Engine\Core\Transform;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Internal\Bindings;
use Lenga\Engine\UI\RectTransform;
use Lenga\Engine\UI\UIElement;
use LogicException;
use function max;
use function min;

/**
 * Animates a value smoothly from its current state to a target over a set time.
 *
 * Use tweens for short, gameplay-driven motion: sliding a door open, pulsing a
 * pickup, moving a panel into view, or counting a score up. Call a method once
 * when something happens; Lenga then updates the value every frame until the
 * tween ends. Calling it every frame from `update()` starts a new tween each frame.
 *
 * Each tween starts from the value at the moment you call the method. Tune
 * delay, easing, time scale and relative motion with {@see TweenOptions}, and
 * keep the returned {@see TweenHandle} to pause, cancel or wait for the tween.
 * A tween writes values directly, so move physics-driven objects through the
 * physics API instead.
 *
 * If a tween cannot start, for example because the UI element does not have
 * the requested property, Lenga logs a warning and returns a handle whose
 * {@see TweenHandle::exists()} is false.
 *
 * @example examples/Tweening/Tween.php Pop a collected coin and count the score up on the HUD
 */
final class Tween
{
    /**
     * Moves a Transform to a world-space position.
     *
     * Use it when the destination is a fixed point in the scene, independent of the object's parent.
     *
     * @param Transform $transform The Transform to animate, usually `$this->transform`.
     * @param Vector3 $to The world-space end position, or the offset to move by when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     * @throws LogicException When the Transform is not part of the running scene.
     */
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

    /**
     * Moves a Transform to a position relative to its parent.
     *
     * Local space is usually the better choice for child objects, pickups and weapons,
     * because the motion stays correct when the parent moves.
     *
     * @param Transform $transform The Transform to animate, usually `$this->transform`.
     * @param Vector3 $to The local end position, or the offset to move by when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     * @throws LogicException When the Transform is not part of the running scene.
     */
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

    /**
     * Scales a Transform to a local scale.
     *
     * For 2D sprites, leave `z` at `1.0`.
     *
     * @param Transform $transform The Transform to animate, usually `$this->transform`.
     * @param Vector3 $to The local end scale, or the amount to add to the current scale when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     * @throws LogicException When the Transform is not part of the running scene.
     */
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

    /**
     * Rotates a Transform to world-space Euler angles in degrees.
     *
     * For 2D rotation, tween the `z` angle.
     *
     * @param Transform $transform The Transform to animate, usually `$this->transform`.
     * @param Vector3 $toEulerAngles The world-space end rotation in degrees, or the angles to rotate by when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     * @throws LogicException When the Transform is not part of the running scene.
     */
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

    /**
     * Rotates a Transform to Euler angles in degrees relative to its parent.
     *
     * For 2D rotation, tween the `z` angle. With relative options, `new Vector3(0.0, 0.0, 360.0)` spins the object one full turn.
     *
     * @param Transform $transform The Transform to animate, usually `$this->transform`.
     * @param Vector3 $toEulerAngles The local end rotation in degrees, or the angles to rotate by when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     * @throws LogicException When the Transform is not part of the running scene.
     */
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

    /**
     * Moves a UI element to an anchored position.
     *
     * Same as {@see Tween::anchoredPositionTo()}. Use it to slide panels, menus and labels into place.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$anchoredPosition}, in canvas pixels, or the offset when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiMoveTo(
        UIElement|RectTransform $target,
        Vector2 $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::anchoredPositionTo($target, $to, $duration, $options);
    }

    /**
     * Tweens a UI element's anchored position.
     *
     * The anchored position is the offset from the element's anchor to its pivot,
     * in canvas pixels. Positive `y` moves the element down.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$anchoredPosition}, or the offset when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's scale.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$scale}. `(1, 1)` is the normal size.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's size.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$sizeDelta}, in canvas pixels.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's rotation.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param float $toDegrees The end value for {@see RectTransform::$rotation}, in degrees.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's lower anchor.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$anchorMin}, in normalized parent coordinates.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's upper anchor.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$anchorMax}, in normalized parent coordinates.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a UI element's pivot.
     *
     * @param UIElement|RectTransform $target The UI element to animate, or its RectTransform.
     * @param Vector2 $to The end value for {@see RectTransform::$pivot}, in normalized element coordinates.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Counts the number shown in a Text or Button up or down to a new value.
     *
     * The tween reads the number currently in the text, ignoring the prefix and
     * suffix, and writes `prefix + number + suffix` every frame. Use it for
     * scores, coins and timers.
     *
     * ```php
     * Tween::uiTextNumberTo($this->scoreText, 1250, 0.3, decimals: 0, prefix: 'Score ');
     * ```
     *
     * @param UIElement $target A {@see Text} or {@see Button}.
     * @param float $to The number to count to, or the amount to add when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @param int $decimals Number of decimal places to show, from 0 to 6.
     * @param string $prefix Text shown before the number, such as `Score `.
     * @param string $suffix Text shown after the number, such as ` pts`.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a numeric UI property by name.
     *
     * The named helpers {@see Tween::uiValueTo()}, {@see Tween::uiFontSizeTo()} and
     * {@see Tween::uiOutlineWidthTo()} call this method. Supported names:
     *
     * - `value`: a {@see Slider}'s value
     * - `fontSize`: a {@see Text} or {@see Button}'s font size
     * - `outlineWidth`: a {@see Button}'s outline width
     * - `textNumber`: the number shown in a {@see Text} or {@see Button}; {@see Tween::uiTextNumberTo()} adds formatting
     *
     * @param UIElement $target The UI element to animate.
     * @param string $property The property name from the list above.
     * @param float $to The end value, or the amount to add when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
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

    /**
     * Tweens a Slider's value, for example to drain a health bar smoothly.
     *
     * The value stays within the slider's {@see Slider::$minValue} and {@see Slider::$maxValue}.
     *
     * @param UIElement $target A {@see Slider}.
     * @param float $to The end value, or the amount to add when the options are relative.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiValueTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'value', $to, $duration, $options);
    }

    /**
     * Tweens the font size of a Text or Button, for example for a quick title pop.
     *
     * @param UIElement $target A {@see Text} or {@see Button}.
     * @param float $to The end font size in canvas pixels.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiFontSizeTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'fontSize', $to, $duration, $options);
    }

    /**
     * Tweens a Button's outline width.
     *
     * @param UIElement $target A {@see Button}.
     * @param float $to The end outline width in canvas pixels. 0 hides the outline.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiOutlineWidthTo(
        UIElement $target,
        float $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiPropertyTo($target, 'outlineWidth', $to, $duration, $options);
    }

    /**
     * Tweens a sprite's colour, which tints its image: fade it out with a
     * transparent colour, or tint it red for a hit and back to white.
     *
     * @param SpriteRenderer $target The sprite to tint.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function spriteColorTo(
        SpriteRenderer $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        $options ??= TweenOptions::make();
        [$red, $green, $blue, $alpha] = self::normalizeColor($to);
        $id = Bindings::tweenSpriteColorTo(
            $target->getInstanceId(),
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
     * Tweens a UI element's main color, or another color property by name.
     *
     * With the default `color` property, the tween changes a {@see Text}'s text
     * color, an {@see Image}'s tint, a {@see Button}'s background color or a
     * {@see Slider}'s fill color. The named helpers such as
     * {@see Tween::uiTextColorTo()} call this method with their property name.
     *
     * ```php
     * Tween::uiColorTo($backdrop, ['r' => 40, 'g' => 40, 'b' => 40, 'a' => 210], 0.18);
     * ```
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @param string $property The color to animate: `color`, `textColor`, `backgroundColor`, `hoverColor`, `pressedColor`, `disabledColor`, `fillColor`, `handleColor`, `checkmarkColor` or `outlineColor`. The element must have that color.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiColorTo(
        UIElement $target,
        Color|array $to,
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
     * Tweens a Button's label color.
     *
     * For a {@see Text} element, use {@see Tween::uiColorTo()}.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiTextColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'textColor');
    }

    /**
     * Tweens the background color of a Button or the track color of a Slider.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiBackgroundColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'backgroundColor');
    }

    /**
     * Tweens the fill color of a Slider.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiFillColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'fillColor');
    }

    /**
     * Tweens the handle color of a Slider.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiHandleColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'handleColor');
    }

    /**
     * Tweens the outline color of a Text or Button.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiOutlineColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'outlineColor');
    }

    /**
     * Tweens the background color a Button shows while hovered or selected.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiHoverColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'hoverColor');
    }

    /**
     * Tweens the background color a Button shows while pressed.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiPressedColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'pressedColor');
    }

    /**
     * Tweens the background color a Button shows while it is not interactable.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiDisabledColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'disabledColor');
    }

    /**
     * Tweens the checkmark color of a toggle element.
     *
     * Scripts cannot get a toggle element yet: Lenga has no PHP class for toggles, so this method has no target it can animate.
     *
     * @param UIElement $target The UI element to animate.
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $to The end color, as a {@see Color} or an array of 0 to 255 channels. Missing array channels are 255.
     * @param float $duration Length of the tween in seconds. With 0 or less, the value jumps to the end on the next frame.
     * @param TweenOptions|null $options Delay, easing, time scale and relative motion. Null uses {@see TweenOptions::make()}.
     * @return TweenHandle A handle to pause, resume, cancel or wait for the tween.
     */
    public static function uiCheckmarkColorTo(
        UIElement $target,
        Color|array $to,
        float $duration,
        ?TweenOptions $options = null,
    ): TweenHandle {
        return self::uiColorTo($target, $to, $duration, $options, 'checkmarkColor');
    }

    private static function requireTransformId(Transform $transform): int
    {
        $transformId = $transform->getNativeId();
        if ($transformId === null) {
            throw new LogicException('Tweening requires a native Transform instance.');
        }

        return $transformId;
    }

    private static function requireUIElementId(UIElement|RectTransform $target): int
    {
        return $target instanceof UIElement ? $target->getId() : $target->getElementId();
    }

    /**
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float} $color
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private static function normalizeColor(Color|array $color): array
    {
        if ($color instanceof Color) {
            $channels = $color->toRGBA();

            return [
                (float) $channels['r'],
                (float) $channels['g'],
                (float) $channels['b'],
                (float) $channels['a'],
            ];
        }

        $red = $color['r'] ?? $color[0] ?? 255;
        $green = $color['g'] ?? $color[1] ?? 255;
        $blue = $color['b'] ?? $color[2] ?? 255;
        $alpha = $color['a'] ?? $color[3] ?? 255;

        return [
            (float) max(0, min(255, $red)),
            (float) max(0, min(255, $green)),
            (float) max(0, min(255, $blue)),
            (float) max(0, min(255, $alpha)),
        ];
    }
}
