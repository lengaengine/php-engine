<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use function is_array;

final class MeshRenderer extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'MeshRenderer');
    }

    public string $meshPath {
        get {
            return (string) ($this->getState()['meshPath'] ?? '');
        }
    }

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
