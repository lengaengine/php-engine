<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class SpriteRenderer extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SpriteRenderer');
    }

    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($value, $this->height);
        }
    }

    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value);
        }
    }

    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            \lenga_internal_sprite_renderer_set_sorting_layer($this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_sprite_renderer_set_order_in_layer($this->componentId, $value);
        }
    }

    public string $texturePath {
        get {
            return (string) ($this->getState()['texturePath'] ?? '');
        }
    }

    public Vector2 $pivot {
        get {
            $pivot = $this->getState()['pivot'] ?? [];
            return new Vector2(
                (float) ($pivot['x'] ?? 0.5),
                (float) ($pivot['y'] ?? 0.5),
            );
        }

        set (Vector2 $value) {
            \lenga_internal_sprite_renderer_set_pivot($this->componentId, $value->x, $value->y);
        }
    }

    public bool $flipX {
        get {
            return (bool) ($this->getState()['flipX'] ?? false);
        }

        set (bool $value) {
            \lenga_internal_sprite_renderer_set_flip_x($this->componentId, $value);
        }
    }

    public bool $flipY {
        get {
            return (bool) ($this->getState()['flipY'] ?? false);
        }

        set (bool $value) {
            \lenga_internal_sprite_renderer_set_flip_y($this->componentId, $value);
        }
    }

    public function setSize(float $width, float $height): void
    {
        \lenga_internal_sprite_renderer_set_size($this->componentId, $width, $height);
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getColor(): array
    {
        /** @var array{color?: array{r?: int, g?: int, b?: int, a?: int}} $state */
        $state = $this->getState();
        $color = $state['color'] ?? [];

        return [
            'r' => (int) ($color['r'] ?? 255),
            'g' => (int) ($color['g'] ?? 255),
            'b' => (int) ($color['b'] ?? 255),
            'a' => (int) ($color['a'] ?? 255),
        ];
    }

    public function setColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_sprite_renderer_set_color(
            $this->componentId,
            $red,
            $green,
            $blue,
            $alpha,
        );
    }

    public function loadTexture(string $texturePath): bool
    {
        return \lenga_internal_sprite_renderer_load_texture($this->componentId, $texturePath);
    }

    /**
     * @return array{
     *     width?: float,
     *     height?: float,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     enabled?: bool,
     *     texturePath?: string,
     *     pivot?: array{x?: float, y?: float},
     *     color?: array{r?: int, g?: int, b?: int, a?: int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     height?: float,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     enabled?: bool,
         *     texturePath?: string,
         *     pivot?: array{x?: float, y?: float},
         *     color?: array{r?: int, g?: int, b?: int, a?: int}
         * } $state
         */
        $state = \lenga_internal_sprite_renderer_get_state($this->componentId);

        return $state;
    }
}
