<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class PlatformEffector2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PlatformEffector2D');
    }

    public bool $useOneWay {
        get {
            return (bool) ($this->getState()['useOneWay'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_platform_effector2d_set_use_one_way($this->componentId, $value);
        }
    }

    public float $surfaceBuffer {
        get {
            return (float) ($this->getState()['surfaceBuffer'] ?? 4.0);
        }

        set(float $value) {
            \lenga_internal_platform_effector2d_set_surface_buffer($this->componentId, $value);
        }
    }

    /** @return array{useOneWay?: bool, surfaceBuffer?: float|int, enabled?: bool} */
    private function getState(): array
    {
        /** @var array{useOneWay?: bool, surfaceBuffer?: float|int, enabled?: bool} $state */
        $state = \lenga_internal_platform_effector2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
