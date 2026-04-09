<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class BoxCollider3D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'BoxCollider3D');
    }

    public Vector3 $size {
        get {
            $state = $this->getState()['size'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            \lenga_internal_box_collider3d_set_size($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    public Vector3 $offset {
        get {
            $state = $this->getState()['offset'] ?? [];

            return new Vector3(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
                (float) ($state['z'] ?? 0.0),
            );
        }

        set(Vector3 $value) {
            \lenga_internal_box_collider3d_set_offset($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_box_collider3d_set_is_trigger($this->componentId, $value);
        }
    }

    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return \lenga_internal_box_collider3d_is_touching(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );
    }

    /**
     * @return list<Collision3D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = \lenga_internal_box_collider3d_get_contacts(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics3D::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $contacts[] = Collision3D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision3D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     size?: array{x?: float|int, y?: float|int, z?: float|int},
     *     offset?: array{x?: float|int, y?: float|int, z?: float|int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     isTrigger?: bool,
         *     size?: array{x?: float|int, y?: float|int, z?: float|int},
         *     offset?: array{x?: float|int, y?: float|int, z?: float|int}
         * } $state
         */
        $state = \lenga_internal_box_collider3d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
