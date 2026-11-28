<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

/**
 * Renderer that draws a 3D sphere.
 *
 * Add it to a GameObject to make it visible as a ball. The sphere follows the
 * GameObject's {@see Transform}. Set its look with {@see Renderer::$material} or
 * {@see Renderer::setColor()}, and its shape with {@see SphereRenderer::$center} and
 * {@see SphereRenderer::$radius}.
 *
 * ```php
 * $renderer = $this->gameObject->getComponent(SphereRenderer::class);
 * $renderer->radius = 0.5;
 * $renderer->setColor(255, 0, 0);
 * ```
 */
final class SphereRenderer extends Renderer
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
        parent::__construct($gameObject, $componentId, 'SphereRenderer');
    }

    /**
     * Local-space offset of the sphere's center from the GameObject's position.
     *
     * Reading it returns a new Vector3. Assign a whole vector to change it, for example
     * `$renderer->center = new Vector3(0.0, 0.5, 0.0)`. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $center {
        get {
            $center = $this->getState()['center'] ?? [];

            return new Vector3(
                (float) ($center['x'] ?? 0.0),
                (float) ($center['y'] ?? 0.0),
                (float) ($center['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            NativeEngine::call('sphere_renderer_set_center', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /**
     * Sphere radius in local units, before the GameObject's scale is applied.
     *
     * The Inspector does not accept values below `0`. Defaults to `1.0`.
     */
    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('sphere_renderer_set_radius', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     center?: array{x?: float, y?: float, z?: float},
     *     radius?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     center?: array{x?: float, y?: float, z?: float},
         *     radius?: float,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('sphere_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
