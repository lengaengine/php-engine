<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * Draws a flat rectangular plane in a 3D scene, such as a floor or a ground tile.
 *
 * Set the plane's size with {@see PlaneRenderer::$width} and
 * {@see PlaneRenderer::$length}, or both at once with
 * {@see PlaneRenderer::setSize()}. Its look comes from its material; see
 * {@see Renderer::$material}.
 */
final class PlaneRenderer extends Renderer
{
    /**
     * Wraps an existing PlaneRenderer component.
     *
     * The engine creates PlaneRenderer wrappers. Use
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
        parent::__construct($gameObject, $componentId, 'PlaneRenderer');
    }

    /**
     * The plane's size along its local X axis, in world units.
     */
    #[Min(0)]
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 10.0);
        }

        set(float $value) {
            $this->setSize($value, $this->length);
        }
    }

    /**
     * The plane's size along its local Z axis, in world units.
     */
    #[Min(0)]
    public float $length {
        get {
            return (float) ($this->getState()['length'] ?? 10.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value);
        }
    }

    /**
     * Sets the plane's width and length at once.
     *
     * @param float $width The size along the plane's local X axis, in world units.
     * @param float $length The size along the plane's local Z axis, in world units.
     */
    public function setSize(float $width, float $length): void
    {
        NativeEngine::call('plane_renderer_set_size', $this->componentId, $width, $length);
    }

    /**
     * @return array{
     *     width?: float,
     *     length?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     length?: float,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('plane_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
