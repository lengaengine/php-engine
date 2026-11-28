<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Internal\ColorBridge;

/**
 * A UI element that shows a line or block of text, such as a label, score counter, instruction or menu title.
 *
 * It inherits layout, visibility and hierarchy from {@see UIElement}. Update it
 * from a Behaviour that holds a reference to it:
 *
 * ```php
 * $this->scoreLabel = Scene::getActive()?->findCanvas('HUD')?->findTextByName('Score Label');
 * if ($this->scoreLabel !== null) {
 *     $this->scoreLabel->text = 'Score: ' . $this->score;
 * }
 * ```
 *
 * For crisp scalable text and outlines, use an SDF font: generate a
 * `.sdf-font.json` asset from a `.ttf` or `.otf` font in the editor, assign it
 * to {@see Text::$fontPath} and set {@see Text::$useSdf}.
 */
final class Text extends UIElement
{
    /** The string the element displays. Defaults to an empty string. */
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_text_set_text', $this->getId(), $value);
        }
    }

    /**
     * Font size in canvas pixels. The canvas scales it with the screen.
     *
     * Defaults to `24.0`.
     */
    public float $fontSize {
        get {
            return (float) ($this->getState()['fontSize'] ?? 24.0);
        }

        set(float $value) {
            NativeEngine::call('ui_text_set_font_size', $this->getId(), $value);
        }
    }

    /**
     * Project-relative path to the font asset, such as `Assets/Fonts/HUD.ttf` or `Assets/Fonts/HUD.sdf-font.json`.
     *
     * An empty string uses Lenga's default UI font.
     */
    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_text_set_font_path', $this->getId(), $value);
        }
    }

    /**
     * Whether the text renders with SDF, which stays crisp at any size.
     *
     * Use it with a `.sdf-font.json` font in {@see Text::$fontPath}. Defaults to `false`.
     */
    public bool $useSdf {
        get {
            return (bool) ($this->getState()['useSdf'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('ui_text_set_use_sdf', $this->getId(), $value);
        }
    }

    /**
     * Width of the outline around SDF text, in pixels. 0 draws no outline.
     *
     * Applies when {@see Text::$useSdf} is true. Defaults to `0.0`.
     */
    public float $sdfOutlineWidth {
        get {
            return (float) ($this->getState()['sdfOutlineWidth'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_text_set_sdf_outline_width', $this->getId(), $value);
        }
    }

    /**
     * Edge softness of SDF text and its outline, in pixels. Higher values give softer edges.
     *
     * Applies when {@see Text::$useSdf} is true. Defaults to `0.5`.
     */
    public float $sdfSoftness {
        get {
            return (float) ($this->getState()['sdfSoftness'] ?? 0.5);
        }

        set(float $value) {
            NativeEngine::call('ui_text_set_sdf_softness', $this->getId(), $value);
        }
    }

    /**
     * The text fill color.
     *
     * Defaults to white.
     */
    public Color $color {
        get => ColorBridge::fromState($this->getState()['color'] ?? null, Color::white());

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call('ui_text_set_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
        }
    }

    /**
     * Gets the text fill color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getColor(): array
    {
        return $this->color->toRGBA();
    }

    /**
     * Sets the text fill color.
     *
     * Same as assigning {@see Text::$color}, with byte channels or an array also accepted.
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
        NativeEngine::call('ui_text_set_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Gets the text outline color as a Color value.
     *
     * The outline applies to text drawn with a normal `.ttf` or `.otf` font.
     * Returns black when no outline color is set.
     */
    public function getOutlineTint(): Color
    {
        return ColorBridge::fromState($this->getState()['outlineColor'] ?? null, Color::black());
    }

    /**
     * Gets the text outline color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getOutlineColor(): array
    {
        return $this->getOutlineTint()->toRGBA();
    }

    /**
     * Gets how far the outline of normal `.ttf` or `.otf` text is drawn from the text, in canvas pixels.
     *
     * For SDF text, use {@see Text::$sdfOutlineWidth}.
     *
     * @return array{x:float, y:float} The horizontal and vertical offsets. Each defaults to `1.0`.
     */
    public function getOutlineDistance(): array
    {
        /** @var array{x?: float, y?: float} $value */
        $value = $this->getState()['outlineDistance'] ?? [];

        return [
            'x' => (float) ($value['x'] ?? 1.0),
            'y' => (float) ($value['y'] ?? 1.0),
        ];
    }
}
