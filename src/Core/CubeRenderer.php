<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * Draws a 3D box at the GameObject's position.
 *
 * The box moves, rotates and scales with the GameObject's Transform. Set
 * its look with the material inherited from {@see Renderer}. Drawing a box
 * does not make it collide; add a {@see BoxCollider3D} for that.
 */
final class CubeRenderer extends Renderer
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CubeRenderer');
    }

    /**
     * Size of the box along its X axis, in local units. Defaults to `1`.
     */
    #[Min(0)]
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($value, $this->height, $this->length);
        }
    }

    /**
     * Size of the box along its Y axis, in local units. Defaults to `1`.
     */
    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value, $this->length);
        }
    }

    /**
     * Size of the box along its Z axis, in local units. Defaults to `1`.
     */
    #[Min(0)]
    public float $length {
        get {
            return (float) ($this->getState()['length'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($this->width, $this->height, $value);
        }
    }

    /**
     * Sets all three sizes of the box in one call.
     *
     * @param float $width Size along the X axis, in local units.
     * @param float $height Size along the Y axis, in local units.
     * @param float $length Size along the Z axis, in local units.
     */
    public function setSize(float $width, float $height, float $length): void
    {
        NativeEngine::call('cube_renderer_set_size', $this->componentId, $width, $height, $length);
    }

    /**
     * @return array{
     *     width?: float,
     *     height?: float,
     *     length?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     height?: float,
         *     length?: float,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('cube_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
