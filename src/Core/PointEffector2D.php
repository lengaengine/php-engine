<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class PointEffector2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PointEffector2D');
    }

    public float $forceMagnitude {
        get {
            return (float) ($this->getState()['forceMagnitude'] ?? 10.0);
        }

        set(float $value) {
            \lenga_internal_point_effector2d_set_force_magnitude($this->componentId, $value);
        }
    }

    public float $distanceScale {
        get {
            return (float) ($this->getState()['distanceScale'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_point_effector2d_set_distance_scale($this->componentId, $value);
        }
    }

    public bool $inverseSquared {
        get {
            return (bool) ($this->getState()['inverseSquared'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_point_effector2d_set_inverse_squared($this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     forceMagnitude?: float|int,
     *     distanceScale?: float|int,
     *     inverseSquared?: bool,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     forceMagnitude?: float|int,
         *     distanceScale?: float|int,
         *     inverseSquared?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_point_effector2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
