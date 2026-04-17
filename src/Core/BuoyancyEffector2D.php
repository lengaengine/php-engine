<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class BuoyancyEffector2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'BuoyancyEffector2D');
    }

    public float $density {
        get {
            return (float) ($this->getState()['density'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_buoyancy_effector2d_set_density($this->componentId, $value);
        }
    }

    public float $linearDrag {
        get {
            return (float) ($this->getState()['linearDrag'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_buoyancy_effector2d_set_linear_drag($this->componentId, $value);
        }
    }

    public float $flowAngle {
        get {
            return (float) ($this->getState()['flowAngle'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_buoyancy_effector2d_set_flow_angle($this->componentId, $value);
        }
    }

    public float $flowMagnitude {
        get {
            return (float) ($this->getState()['flowMagnitude'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_buoyancy_effector2d_set_flow_magnitude($this->componentId, $value);
        }
    }

    public float $surfaceLevel {
        get {
            return (float) ($this->getState()['surfaceLevel'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_buoyancy_effector2d_set_surface_level($this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     density?: float|int,
     *     linearDrag?: float|int,
     *     flowAngle?: float|int,
     *     flowMagnitude?: float|int,
     *     surfaceLevel?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     density?: float|int,
         *     linearDrag?: float|int,
         *     flowAngle?: float|int,
         *     flowMagnitude?: float|int,
         *     surfaceLevel?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_buoyancy_effector2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
