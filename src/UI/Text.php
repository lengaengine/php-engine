<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Internal\ColorBridge;

final class Text extends UIElement
{
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_text_set_text', $this->getId(), $value);
        }
    }

    public float $fontSize {
        get {
            return (float) ($this->getState()['fontSize'] ?? 24.0);
        }

        set(float $value) {
            NativeEngine::call('ui_text_set_font_size', $this->getId(), $value);
        }
    }

    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('ui_text_set_font_path', $this->getId(), $value);
        }
    }

    public bool $useSdf {
        get {
            return (bool) ($this->getState()['useSdf'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('ui_text_set_use_sdf', $this->getId(), $value);
        }
    }

    public float $sdfOutlineWidth {
        get {
            return (float) ($this->getState()['sdfOutlineWidth'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_text_set_sdf_outline_width', $this->getId(), $value);
        }
    }

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
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
     */
    public function setColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call('ui_text_set_color', $this->getId(), $color['r'], $color['g'], $color['b'], $color['a']);
    }

    /**
     * Gets the text outline color as a Color value.
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
     * @return array{x:float, y:float}
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
