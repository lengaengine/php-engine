<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

final class Text extends UIElement
{
    public string $text {
        get {
            return (string) ($this->getState()['text'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_text_set_text($this->getId(), $value);
        }
    }

    public float $fontSize {
        get {
            return (float) ($this->getState()['fontSize'] ?? 24.0);
        }

        set(float $value) {
            \lenga_internal_ui_text_set_font_size($this->getId(), $value);
        }
    }

    public string $fontPath {
        get {
            return (string) ($this->getState()['fontPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_text_set_font_path($this->getId(), $value);
        }
    }

    public bool $useSdf {
        get {
            return (bool) ($this->getState()['useSdf'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_ui_text_set_use_sdf($this->getId(), $value);
        }
    }

    public float $sdfOutlineWidth {
        get {
            return (float) ($this->getState()['sdfOutlineWidth'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_ui_text_set_sdf_outline_width($this->getId(), $value);
        }
    }

    public float $sdfSoftness {
        get {
            return (float) ($this->getState()['sdfSoftness'] ?? 0.5);
        }

        set(float $value) {
            \lenga_internal_ui_text_set_sdf_softness($this->getId(), $value);
        }
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['color'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 255),
            'g' => (int) ($value['g'] ?? 255),
            'b' => (int) ($value['b'] ?? 255),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    public function setColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_text_set_color($this->getId(), $red, $green, $blue, $alpha);
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getOutlineColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['outlineColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 0),
            'g' => (int) ($value['g'] ?? 0),
            'b' => (int) ($value['b'] ?? 0),
            'a' => (int) ($value['a'] ?? 255),
        ];
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
