<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class MeshRenderer extends Component
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

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_mesh_renderer_set_material_path($this->componentId, $value);
        }
    }

    public function loadMesh(string $meshPath): bool
    {
        return \lenga_internal_mesh_renderer_load_mesh($this->componentId, $meshPath);
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
        \lenga_internal_mesh_renderer_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     meshPath?: string,
     *     materialPath?: string,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     meshPath?: string,
         *     materialPath?: string,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_mesh_renderer_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
