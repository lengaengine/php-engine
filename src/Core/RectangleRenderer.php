<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class RectangleRenderer extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'RectangleRenderer');
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
            \lenga_internal_rectangle_renderer_set_sorting_layer($this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_rectangle_renderer_set_order_in_layer($this->componentId, $value);
        }
    }

    public function setSize(float $width, float $height): void
    {
        \lenga_internal_rectangle_renderer_set_size($this->componentId, $width, $height);
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
        \lenga_internal_rectangle_renderer_set_color(
            $this->componentId,
            $red,
            $green,
            $blue,
            $alpha,
        );
    }

    /**
     * @return array{
     *     width?: float,
     *     height?: float,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     enabled?: bool,
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
         *     color?: array{r?: int, g?: int, b?: int, a?: int}
         * } $state
         */
        $state = \lenga_internal_rectangle_renderer_get_state($this->componentId);

        return $state;
    }
}
