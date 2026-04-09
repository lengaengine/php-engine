<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

final class Image extends UIElement
{
    public string $spritePath {
        get {
            return (string) ($this->getState()['spritePath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_ui_image_set_sprite_path($this->getId(), $value);
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
        \lenga_internal_ui_image_set_color($this->getId(), $red, $green, $blue, $alpha);
    }
}
