<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;

/**
 * Draws a filled rectangle in a 2D scene.
 *
 * Use it for blocks, bars, backgrounds and placeholder art. Set its size with
 * {@see RectangleRenderer::$width} and {@see RectangleRenderer::$height}, or
 * both at once with {@see RectangleRenderer::setSize()}, and tint it with
 * {@see Renderer2D::$color}.
 */
final class RectangleRenderer extends Renderer2D
{
    /**
     * Wraps an existing RectangleRenderer component.
     *
     * The engine creates RectangleRenderer wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'RectangleRenderer');
    }

    /**
     * The rectangle's width, in world units.
     */
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($value, $this->height);
        }
    }

    /**
     * The rectangle's height, in world units.
     */
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value);
        }
    }

    /**
     * The name of the sorting layer the rectangle is drawn in.
     *
     * Sorting layers decide which 2D renderers are drawn in front of others.
     * Defaults to `Default`.
     */
    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('rectangle_renderer_set_sorting_layer', $this->componentId, $value);
        }
    }

    /**
     * The rectangle's drawing order within its sorting layer.
     *
     * Renderers with a higher order are drawn in front of those with a lower
     * order in the same layer. Defaults to `0`.
     */
    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('rectangle_renderer_set_order_in_layer', $this->componentId, $value);
        }
    }

    /**
     * Sets the rectangle's width and height at once.
     *
     * @param float $width The width, in world units.
     * @param float $height The height, in world units.
     */
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
