<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Internal\ColorBridge;

/**
 * A UI element that draws a picture or a colored rectangle, such as an icon, a health heart or a panel background.
 *
 * It inherits layout, visibility and hierarchy from {@see UIElement}. Change
 * {@see Image::$color} to tint or fade it, or tween it with
 * {@see \Lenga\Engine\Tweening\Tween::uiColorTo()}.
 */
final class Image extends UIElement
{
    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = 'Image';

    /**
     * Project-relative path to the image file the element draws, such as `Assets/Sprites/UI/Heart.png`.
     *
     * The image is stretched to fill the element's rectangle. When the path is
     * empty or the file cannot be loaded, the element draws a rectangle in
     * {@see Image::$color} instead. Defaults to an empty string.
     */
    public string $spritePath {
        get {
            return (string) ($this->getState()['spritePath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_image_set_sprite_path', $this->getId(), $value);
        }
    }

    /**
     * The tint applied to this image.
     *
     * White shows the image unchanged; a lower alpha makes it transparent. Defaults to white.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_image_set_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the image tint as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getColor(): array
    {
        return $this->color->toRGBA();
    }

    /**
     * Sets the image tint.
     *
     * Same as assigning {@see Image::$color}, with byte channels or an array also accepted.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A {@see Color}, an array of 0 to 255 channels, or the red channel from 0 to 255 when you pass the channels separately.
     * @param int|null $green The green channel from 0 to 255. Required when `$red` is an int.
     * @param int|null $blue The blue channel from 0 to 255. Required when `$red` is an int.
     * @param int $alpha The alpha channel from 0 (transparent) to 255 (opaque). Used only when `$red` is an int.
     * @throws \InvalidArgumentException When `$red` is an int and `$green` or `$blue` is missing.
     */
    public function setColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_image_set_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }
}
