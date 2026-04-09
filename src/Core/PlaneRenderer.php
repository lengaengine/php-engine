<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class PlaneRenderer extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PlaneRenderer');
    }

    #[Min(0)]
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 10.0);
        }

        set(float $value) {
            $this->setSize($value, $this->length);
        }
    }

    #[Min(0)]
    public float $length {
        get {
            return (float) ($this->getState()['length'] ?? 10.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value);
        }
    }

    public function setSize(float $width, float $length): void
    {
        \lenga_internal_plane_renderer_set_size($this->componentId, $width, $length);
    }

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_plane_renderer_set_material_path($this->componentId, $value);
        }
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
        \lenga_internal_plane_renderer_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     width?: float,
     *     length?: float,
     *     materialPath?: string,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     length?: float,
         *     materialPath?: string,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_plane_renderer_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
