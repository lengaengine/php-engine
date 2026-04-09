<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Range;

final class Camera extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'Camera');
    }

    public bool $primary {
        get {
            return (bool) ($this->getState()['primary'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_camera_set_primary($this->componentId, $value);
        }
    }

    public string $projection {
        get {
            return (string) ($this->getState()['projection'] ?? 'Perspective');
        }

        set(string $value) {
            \lenga_internal_camera_set_projection($this->componentId, $value);
        }
    }

    #[Range(1, 179)]
    public float $fieldOfView {
        get {
            return (float) ($this->getState()['fieldOfView'] ?? 45.0);
        }

        set(float $value) {
            \lenga_internal_camera_set_field_of_view($this->componentId, $value);
        }
    }

    #[Min(0.001)]
    public float $orthographicSize {
        get {
            return (float) ($this->getState()['orthographicSize'] ?? 10.0);
        }

        set(float $value) {
            \lenga_internal_camera_set_orthographic_size($this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     primary?: bool,
     *     projection?: string,
     *     fieldOfView?: float,
     *     orthographicSize?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     primary?: bool,
         *     projection?: string,
         *     fieldOfView?: float,
         *     orthographicSize?: float,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_camera_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
