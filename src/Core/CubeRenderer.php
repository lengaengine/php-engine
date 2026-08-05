<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

final class CubeRenderer extends Renderer
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CubeRenderer');
    }

    #[Min(0)]
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($value, $this->height, $this->length);
        }
    }

    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value, $this->length);
        }
    }

    #[Min(0)]
    public float $length {
        get {
            return (float) ($this->getState()['length'] ?? 1.0);
        }

        set(float $value) {
            $this->setSize($this->width, $this->height, $value);
        }
    }

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
