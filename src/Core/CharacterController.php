<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class CharacterController extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CharacterController');
    }

    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            \lenga_internal_character_controller_set_radius($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 2.0);
        }

        set(float $value) {
            \lenga_internal_character_controller_set_height($this->componentId, $value);
        }
    }

    public Vector3 $center {
        get {
            $state = $this->getState()['center'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            \lenga_internal_character_controller_set_center($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    #[Min(0)]
    public float $skinWidth {
        get {
            return (float) ($this->getState()['skinWidth'] ?? 0.08);
        }

        set(float $value) {
            \lenga_internal_character_controller_set_skin_width($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $minMoveDistance {
        get {
            return (float) ($this->getState()['minMoveDistance'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_character_controller_set_min_move_distance($this->componentId, $value);
        }
    }

    public bool $detectCollisions {
        get {
            return (bool) ($this->getState()['detectCollisions'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_character_controller_set_detect_collisions($this->componentId, $value);
        }
    }

    public bool $isGrounded {
        get {
            return (bool) ($this->getState()['isGrounded'] ?? false);
        }
    }

    public Vector3 $velocity {
        get {
            $state = $this->getState()['velocity'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }
    }

    /**
     * Bitmask of the last collision flags returned by move().
     */
    public int $collisionFlags {
        get {
            return (int) ($this->getState()['collisionFlags'] ?? 0);
        }
    }

    public function move(Vector3 $motion): int
    {
        return (int) \lenga_internal_character_controller_move(
            $this->componentId,
            $motion->x,
            $motion->y,
            $motion->z,
        );
    }

    public function simpleMove(Vector3 $speed): bool
    {
        return (bool) \lenga_internal_character_controller_simple_move(
            $this->componentId,
            $speed->x,
            $speed->y,
            $speed->z,
        );
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     radius?: float|int,
     *     height?: float|int,
     *     center?: array{x?: float|int, y?: float|int, z?: float|int},
     *     skinWidth?: float|int,
     *     minMoveDistance?: float|int,
     *     detectCollisions?: bool,
     *     isGrounded?: bool,
     *     collisionFlags?: int,
     *     velocity?: array{x?: float|int, y?: float|int, z?: float|int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     radius?: float|int,
         *     height?: float|int,
         *     center?: array{x?: float|int, y?: float|int, z?: float|int},
         *     skinWidth?: float|int,
         *     minMoveDistance?: float|int,
         *     detectCollisions?: bool,
         *     isGrounded?: bool,
         *     collisionFlags?: int,
         *     velocity?: array{x?: float|int, y?: float|int, z?: float|int}
         * } $state
         */
        $state = \lenga_internal_character_controller_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
