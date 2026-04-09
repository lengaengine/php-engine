<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;

final class SphereCollider3D extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SphereCollider3D');
    }

    #[Min(0)]
    public float $radius {
        get {
            return (float) ($this->getState()['radius'] ?? 0.5);
        }

        set(float $value) {
            \lenga_internal_sphere_collider3d_set_radius($this->componentId, $value);
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
            \lenga_internal_sphere_collider3d_set_offset($this->componentId, $value->x, $value->y, $value->z);
        }
    }

    public bool $isTrigger {
        get {
            return (bool) ($this->getState()['isTrigger'] ?? false);
        }

        set(bool $value) {
            \lenga_internal_sphere_collider3d_set_is_trigger($this->componentId, $value);
        }
    }

    public function isTouching(bool $includeTriggers = true, ?int $layerMask = null): bool
    {
        return \lenga_internal_sphere_collider3d_is_touching(
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
        $results = \lenga_internal_sphere_collider3d_get_contacts(
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
     * @deprecated Use CharacterController::move() for Unity-style kinematic movement.
     */
    public function moveAndSlide(
        Vector3 $delta,
        float $skinWidth = 0.05,
        bool $includeTriggers = false,
        ?int $layerMask = null,
    ): KinematicMove3DResult {
        $transform = $this->gameObject->transform;
        $result = Physics3D::moveAndSlideSphere(
            $transform->position,
            $this->radius,
            $delta,
            $this->gameObject,
            $this->offset,
            $skinWidth,
            $includeTriggers,
            $layerMask,
        );

        $transform->position = $result->position;
        return $result;
    }

    /**
     * @return array{
     *     enabled?: bool,
     *     isTrigger?: bool,
     *     radius?: float|int,
     *     offset?: array{x?: float|int, y?: float|int, z?: float|int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     enabled?: bool,
         *     isTrigger?: bool,
         *     radius?: float|int,
         *     offset?: array{x?: float|int, y?: float|int, z?: float|int}
         * } $state
         */
        $state = \lenga_internal_sphere_collider3d_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
