<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use function is_array;

final class PlaneRenderer extends Renderer
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
