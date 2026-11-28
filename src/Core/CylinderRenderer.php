<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * Draws a 3D cylinder at the GameObject's position.
 *
 * Give the top and bottom different radii for a tapered cylinder or a
 * cone. The cylinder stands upright along the GameObject's Y axis and moves,
 * rotates and scales with its Transform. Set its look with the material
 * inherited from {@see Renderer}. Drawing a cylinder does not make it
 * collide; add a {@see CylinderCollider3D} for that.
 */
final class CylinderRenderer extends Renderer
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
        parent::__construct($gameObject, $componentId, 'CylinderRenderer');
    }

    /**
     * Position of the cylinder's bottom end relative to the GameObject, in local units.
     *
     * The cylinder rises along the GameObject's Y axis from this point.
     * Defaults to `(0, 0, 0)`.
     */
    public Vector3 $position {
        get {
            $position = $this->getState()['position'] ?? [];

            return new Vector3(
                (float) ($position['x'] ?? 0.0),
                (float) ($position['y'] ?? 0.0),
                (float) ($position['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('cylinder_renderer_set_position', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Radius of the top end, in local units. Zero draws a cone with its point at the top.
     *
     * Defaults to `1`.
     */
    #[Min(0)]
    public float $topRadius {
        get {
            return (float) ($this->getState()['topRadius'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('cylinder_renderer_set_top_radius', $this->componentId, $value);
        }
    }

    /**
     * Radius of the bottom end, in local units. Defaults to `1`.
     */
    #[Min(0)]
    public float $bottomRadius {
        get {
            return (float) ($this->getState()['bottomRadius'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('cylinder_renderer_set_bottom_radius', $this->componentId, $value);
        }
    }

    /**
     * Height of the cylinder from end to end, in local units. Defaults to `4`.
     */
    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 4.0);
        }

        set(float $value) {
            NativeEngine::call('cylinder_renderer_set_height', $this->componentId, $value);
        }
    }

    /**
     * Number of segments around the cylinder. More segments look rounder and cost more to draw.
     *
     * The Inspector accepts 3 or more. Defaults to `16`.
     */
    #[Min(3)]
    public int $totalSlices {
        get {
            return (int) ($this->getState()['totalSlices'] ?? 16);
        }

        set(int $value) {
            NativeEngine::call('cylinder_renderer_set_total_slices', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     position?: array{x?: float, y?: float, z?: float},
     *     topRadius?: float,
     *     bottomRadius?: float,
     *     height?: float,
     *     totalSlices?: int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     position?: array{x?: float, y?: float, z?: float},
         *     topRadius?: float,
         *     bottomRadius?: float,
         *     height?: float,
         *     totalSlices?: int,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('cylinder_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
