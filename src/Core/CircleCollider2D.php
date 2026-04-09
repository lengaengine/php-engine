<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class CircleCollider2D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CircleCollider2D');
    }

    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_circle_collider2d_set_radius($this->componentId, $value);
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
            \lenga_internal_circle_collider2d_set_offset($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_circle_collider2d_set_is_trigger($this->componentId, $value);
        }
    }

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_circle_collider2d_set_material_path($this->componentId, $value);
        }
    }

    public PhysicsMaterial2D $material {
        get {
            $state = $this->getState();
            /** @var array{
             *     friction?: float|int,
             *     bounciness?: float|int,
             *     frictionCombine?: string,
             *     bounceCombine?: string
             * } $materialState
             */
            $materialState = \is_array($state['material'] ?? null) ? $state['material'] : [];

            return PhysicsMaterial2D::fromArray(
                $materialState,
                (string) ($state['materialPath'] ?? ''),
            );
        }

        set(PhysicsMaterial2D $value) {
            if ($value->assetPath !== '') {
                \lenga_internal_circle_collider2d_set_material_path($this->componentId, $value->assetPath);
                return;
            }

            \lenga_internal_circle_collider2d_set_material(
                $this->componentId,
                $value->friction,
                $value->bounciness,
                $value->frictionCombine,
                $value->bounceCombine,
            );
        }
    }

    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return \lenga_internal_circle_collider2d_is_touching(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );
    }

    /**
     * @return list<Collision2D>
     */
    public function getContacts(bool $includeTriggers = true, ?int $layerMask = null): array
    {
        $results = \lenga_internal_circle_collider2d_get_contacts(
            $this->componentId,
            $includeTriggers,
            $layerMask ?? Physics2D::ALL_LAYERS,
        );

        if (!\is_array($results)) {
            return [];
        }

        $contacts = [];
        foreach ($results as $result) {
            if (\is_array($result)) {
                $contacts[] = Collision2D::fromNativeData($result);
                continue;
            }

            if ($result instanceof Collision2D) {
                $contacts[] = $result;
            }
        }

        return $contacts;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     radius?: float|int,
     *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
     *     materialPath?: string,
     *     material?: array{
     *         friction?: float|int,
     *         bounciness?: float|int,
     *         frictionCombine?: string,
     *         bounceCombine?: string
     *     }
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     isTrigger?: bool,
         *     radius?: float|int,
         *     offset?: array{x?: float|int, y?: float|int, z?: float|int},
         *     materialPath?: string,
         *     material?: array{
         *         friction?: float|int,
         *         bounciness?: float|int,
         *         frictionCombine?: string,
         *         bounceCombine?: string
         *     }
         * } $state
         */
        $state = \lenga_internal_circle_collider2d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
