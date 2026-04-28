<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Vector2;

/**
 * UI slider element with value range, interaction state, and visual part styling.
 */
final class Slider extends UIElement
{
    public bool $interactable {
        get {
            return (bool) ($this->getState()['interactable'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_ui_slider_set_interactable($this->getId(), $value);
        }
    }

    public bool $hovered {
        get {
            return (bool) ($this->getState()['hovered'] ?? false);
        }
    }

    public bool $focused {
        get {
            return (bool) ($this->getState()['focused'] ?? false);
        }
    }

    public bool $pressed {
        get {
            return (bool) ($this->getState()['pressed'] ?? false);
        }
    }

    public float $minValue {
        get {
            return (float) ($this->getState()['minValue'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_ui_slider_set_min_value($this->getId(), $value);
        }
    }

    public float $maxValue {
        get {
            return (float) ($this->getState()['maxValue'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_ui_slider_set_max_value($this->getId(), $value);
        }
    }

    public float $value {
        get {
            return (float) ($this->getState()['value'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_ui_slider_set_value($this->getId(), $value);
        }
    }

    public bool $wholeNumbers {
        get {
            return (bool) ($this->getState()['wholeNumbers'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_ui_slider_set_whole_numbers($this->getId(), $value);
        }
    }

    public bool $showHandle {
        get {
            return (bool) ($this->getState()['showHandle'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_ui_slider_set_show_handle($this->getId(), $value);
        }
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getBackgroundColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['backgroundColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 56),
            'g' => (int) ($value['g'] ?? 66),
            'b' => (int) ($value['b'] ?? 82),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    public function setBackgroundColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_slider_set_background_color($this->getId(), $red, $green, $blue, $alpha);
    }

    public string $backgroundImage {
        get {
            return (string) ($this->getState()['backgroundImagePath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_slider_set_background_image($this->getId(), $value);
        }
    }

    public Vector2 $backgroundSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['backgroundSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_slider_set_background_size($this->getId(), $value->x, $value->y);
        }
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getFillColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['fillColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 34),
            'g' => (int) ($value['g'] ?? 197),
            'b' => (int) ($value['b'] ?? 155),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    public function setFillColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_slider_set_fill_color($this->getId(), $red, $green, $blue, $alpha);
    }

    public string $fillImage {
        get {
            return (string) ($this->getState()['fillImagePath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_slider_set_fill_image($this->getId(), $value);
        }
    }

    public Vector2 $fillSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['fillSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_slider_set_fill_size($this->getId(), $value->x, $value->y);
        }
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getHandleColor(): array
    {
        /** @var array{r?: int, g?: int, b?: int, a?: int} $value */
        $value = $this->getState()['handleColor'] ?? [];

        return [
            'r' => (int) ($value['r'] ?? 255),
            'g' => (int) ($value['g'] ?? 255),
            'b' => (int) ($value['b'] ?? 255),
            'a' => (int) ($value['a'] ?? 255),
        ];
    }

    public function setHandleColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_ui_slider_set_handle_color($this->getId(), $red, $green, $blue, $alpha);
    }

    public string $handleImage {
        get {
            return (string) ($this->getState()['handleImagePath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_slider_set_handle_image($this->getId(), $value);
        }
    }

    public Vector2 $handleSize {
        get {
            /** @var array{x?: float, y?: float} $value */
            $value = $this->getState()['handleSize'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_slider_set_handle_size($this->getId(), $value->x, $value->y);
        }
    }
}
