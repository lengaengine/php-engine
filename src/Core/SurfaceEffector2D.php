<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class SurfaceEffector2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SurfaceEffector2D');
    }

    public bool $useGlobalDirection {
        get {
            return (bool) ($this->getState()['useGlobalDirection'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_surface_effector2d_set_use_global_direction($this->componentId, $value);
        }
    }

    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 5.0);
        }

        set(float $value) {
            \lenga_internal_surface_effector2d_set_speed($this->componentId, $value);
        }
    }

    public float $forceScale {
        get {
            return (float) ($this->getState()['forceScale'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_surface_effector2d_set_force_scale($this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     useGlobalDirection?: bool,
     *     speed?: float|int,
     *     forceScale?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     useGlobalDirection?: bool,
         *     speed?: float|int,
         *     forceScale?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_surface_effector2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
