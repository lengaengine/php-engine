<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class AreaEffector2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'AreaEffector2D');
    }

    public bool $useGlobalAngle {
        get {
            return (bool) ($this->getState()['useGlobalAngle'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_area_effector2d_set_use_global_angle($this->componentId, $value);
        }
    }

    public float $forceAngle {
        get {
            return (float) ($this->getState()['forceAngle'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_area_effector2d_set_force_angle($this->componentId, $value);
        }
    }

    public float $forceMagnitude {
        get {
            return (float) ($this->getState()['forceMagnitude'] ?? 10.0);
        }

        set(float $value) {
            \lenga_internal_area_effector2d_set_force_magnitude($this->componentId, $value);
        }
    }

    public float $drag {
        get {
            return (float) ($this->getState()['drag'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_area_effector2d_set_drag($this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     useGlobalAngle?: bool,
     *     forceAngle?: float|int,
     *     forceMagnitude?: float|int,
     *     drag?: float|int,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     useGlobalAngle?: bool,
         *     forceAngle?: float|int,
         *     forceMagnitude?: float|int,
         *     drag?: float|int,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_area_effector2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
