<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;

/**
 * Draws a 3D mesh loaded from a mesh asset.
 *
 * Assign the mesh in the Inspector or load one with
 * {@see MeshRenderer::loadMesh()}. Use {@see Renderer::$material} to change how
 * it looks. To draw an imported model with animations, use
 * {@see ModelRenderer}.
 */
final class MeshRenderer extends Renderer
{
    /**
     * Wraps the mesh renderer component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'MeshRenderer');
    }

    /**
     * The project-relative path of the mesh asset this renderer draws.
     *
     * Read-only. Use {@see MeshRenderer::loadMesh()} to change it. Defaults to `''`.
     */
    public string $meshPath {
        get {
            return (string) ($this->getState()['meshPath'] ?? '');
        }
    }

    /**
     * Loads a mesh asset and draws it with this renderer.
     *
     * @param string $meshPath The project-relative path of the mesh asset.
     * @return bool `true` when the mesh loaded.
     */
    public function loadMesh(string $meshPath): bool
    {
        return NativeEngine::call('mesh_renderer_load_mesh', $this->componentId, $meshPath);
    }

    /**
     * @return array{
     *     meshPath?: string,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     meshPath?: string,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('mesh_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
