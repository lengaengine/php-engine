<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;

final class RectangleRenderer extends Renderer
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
            NativeEngine::call('rectangle_renderer_set_sorting_layer', $this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('rectangle_renderer_set_order_in_layer', $this->componentId, $value);
        }
    }

    public function setSize(float $width, float $height): void
    {
        NativeEngine::call('rectangle_renderer_set_size', $this->componentId, $width, $height);
    }

    /**
     * @return array{
     *     width?: float,
     *     height?: float,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     height?: float,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('rectangle_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
