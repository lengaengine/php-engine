<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Describes one contact between two 2D colliders.
 *
 * The engine passes a Collision2D to the collision and trigger methods of
 * your Behaviours, such as `onCollisionEnter2D(Collision2D $collision)`, and the
 * `getContacts()` methods of colliders return a list of them. "Self" is the GameObject that received the call or
 * owns the queried collider; "other" is the object it touched.
 *
 * ```php
 * public function onCollisionEnter2D(Collision2D $collision): void
 * {
 *     if ($collision->gameObject?->compareTag('Enemy')) {
 *         Debug::log('Hit ' . $collision->gameObject->name . ' at ' . $collision->point);
 *     }
 * }
 * ```
 */
final class Collision2D
{
    /**
     * Creates a contact description.
     *
     * @internal The engine creates contact descriptions for collision callbacks and contact queries.
     *
     * @param GameObject|null $gameObjectValue The other GameObject.
     * @param Component|null $colliderValue The other collider.
     * @param GameObject|null $otherGameObjectValue The other GameObject, for {@see Collision2D::$otherGameObject}.
     * @param Component|null $otherColliderValue The other collider, for {@see Collision2D::$otherCollider}.
     * @param Vector3|null $pointValue Contact point in world space.
     * @param Vector3|null $normalValue Contact normal in world space.
     * @param Vector3|null $relativeVelocityValue Velocity of the other collider relative to this one.
     * @param float $separationValue Distance between the colliders; negative while they overlap.
     * @param bool $isTriggerValue True when the contact involves a trigger collider.
     * @param GameObject|null $selfGameObjectValue The GameObject that received the call.
     * @param Component|null $selfColliderValue The receiving GameObject's collider.
     */
    public function __construct(
        private ?GameObject $gameObjectValue = null,
        private ?Component $colliderValue = null,
        private ?GameObject $otherGameObjectValue = null,
        private ?Component $otherColliderValue = null,
        private ?Vector3 $pointValue = null,
        private ?Vector3 $normalValue = null,
        private ?Vector3 $relativeVelocityValue = null,
        private float $separationValue = 0.0,
        private bool $isTriggerValue = false,
        private ?GameObject $selfGameObjectValue = null,
        private ?Component $selfColliderValue = null,
    ) {
    }

    /**
     * The other GameObject in the contact: the one this GameObject touched, or null when the engine reports none.
     */
    public ?GameObject $gameObject {
        get {
            return $this->gameObjectValue;
        }
    }

    /**
     * The other GameObject's collider in the contact, such as a {@see BoxCollider2D}.
     */
    public ?Component $collider {
        get {
            return $this->colliderValue;
        }
    }

    /**
     * The GameObject that received the callback or owns the queried collider.
     */
    public ?GameObject $selfGameObject {
        get {
            return $this->selfGameObjectValue;
        }
    }

    /**
     * This GameObject's collider that took part in the contact.
     */
    public ?Component $selfCollider {
        get {
            return $this->selfColliderValue;
        }
    }

    /**
     * The other GameObject in the contact. Same as {@see Collision2D::$gameObject}.
     *
     * @deprecated Use {@see Collision2D::$gameObject}.
     */
    public ?GameObject $otherGameObject {
        get {
            return $this->otherGameObjectValue;
        }
    }

    /**
     * The other GameObject's collider. Same as {@see Collision2D::$collider}.
     *
     * @deprecated Use {@see Collision2D::$collider}.
     */
    public ?Component $otherCollider {
        get {
            return $this->otherColliderValue;
        }
    }

    /**
     * Where the colliders touch, in world space. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $point {
        get {
            return $this->pointValue ?? new Vector3();
        }
    }

    /**
     * Unit-length direction of the contact, in world space, pointing from this collider toward the other.
     *
     * In a 2D scene positive Y points down the screen, so a normal with a
     * large positive Y means the other collider is below, as when landing on
     * the ground. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $normal {
        get {
            return $this->normalValue ?? new Vector3();
        }
    }

    /**
     * Velocity of the other collider relative to this one, in world units per second.
     *
     * It is the other collider's velocity minus this collider's velocity. Its
     * length tells you how hard the two met. Defaults to `(0, 0, 0)`.
     */
    public Vector3 $relativeVelocity {
        get {
            return $this->relativeVelocityValue ?? new Vector3();
        }
    }

    /**
     * Distance between the two colliders at the contact, in world units.
     *
     * Negative while they overlap, by the depth of the overlap.
     */
    public float $separation {
        get {
            return $this->separationValue;
        }
    }

    /**
     * Whether the contact involves a trigger collider, so the colliders overlap without blocking each other.
     */
    public bool $isTrigger {
        get {
            return $this->isTriggerValue;
        }
    }

    /**
     * Builds a Collision2D from the contact data the engine reports.
     *
     * @internal The engine calls this to build the contacts it passes to scripts.
     *
     * @param array{
     *     selfGameObject?: array<string, mixed>,
     *     selfCollider?: mixed,
     *     gameObject?: array<string, mixed>,
     *     collider?: mixed,
     *     otherGameObject?: array<string, mixed>,
     *     otherCollider?: mixed,
     *     point?: array{x?: float|int, y?: float|int, z?: float|int},
     *     normal?: array{x?: float|int, y?: float|int, z?: float|int},
     *     relativeVelocity?: array{x?: float|int, y?: float|int, z?: float|int},
     *     separation?: float|int,
     *     isTrigger?: bool
     * } $data Contact data from the engine.
     */
    public static function fromNativeData(array $data): self
    {
        $selfGameObject = \is_array($data['selfGameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['selfGameObject'])
            : null;

        $gameObject = \is_array($data['gameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['gameObject'])
            : null;

        $otherGameObject = \is_array($data['otherGameObject'] ?? null)
            ? GameObject::fromNativeLookupData($data['otherGameObject'])
            : null;

        $selfCollider = GameObject::wrapNativeComponentLookupData($data['selfCollider'] ?? null);
        $collider = GameObject::wrapNativeComponentLookupData($data['collider'] ?? null);
        $otherCollider = GameObject::wrapNativeComponentLookupData($data['otherCollider'] ?? null);

        return new self(
            $gameObject,
            $collider instanceof Component ? $collider : null,
            $otherGameObject,
            $otherCollider instanceof Component ? $otherCollider : null,
            self::vectorFromArray($data['point'] ?? null),
            self::vectorFromArray($data['normal'] ?? null),
            self::vectorFromArray($data['relativeVelocity'] ?? null),
            (float) ($data['separation'] ?? 0.0),
            (bool) ($data['isTrigger'] ?? false),
            $selfGameObject,
            $selfCollider instanceof Component ? $selfCollider : null,
        );
    }

    public function __serialize(): array
    {
        return [
            'selfGameObject' => $this->selfGameObjectValue?->__serialize(),
            'selfCollider' => $this->selfColliderValue?->__serialize(),
            'gameObject' => $this->gameObjectValue?->__serialize(),
            'collider' => $this->colliderValue?->__serialize(),
            'otherGameObject' => $this->otherGameObjectValue?->__serialize(),
            'otherCollider' => $this->otherColliderValue?->__serialize(),
            'point' => self::vectorToArray($this->pointValue),
            'normal' => self::vectorToArray($this->normalValue),
            'relativeVelocity' => self::vectorToArray($this->relativeVelocityValue),
            'separation' => $this->separationValue,
            'isTrigger' => $this->isTriggerValue,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->selfGameObjectValue = \is_array($data['selfGameObject'] ?? null)
            ? GameObject::fromSerializedReference($data['selfGameObject'])
            : null;
        $this->gameObjectValue = \is_array($data['gameObject'] ?? null)
            ? GameObject::fromSerializedReference($data['gameObject'])
            : null;
        $this->otherGameObjectValue = \is_array($data['otherGameObject'] ?? null)
            ? GameObject::fromSerializedReference($data['otherGameObject'])
            : null;
        $this->selfColliderValue = self::componentFromSerializedData($data['selfCollider'] ?? null);
        $this->colliderValue = self::componentFromSerializedData($data['collider'] ?? null);
        $this->otherColliderValue = self::componentFromSerializedData($data['otherCollider'] ?? null);
        $this->pointValue = self::vectorFromArray($data['point'] ?? null);
        $this->normalValue = self::vectorFromArray($data['normal'] ?? null);
        $this->relativeVelocityValue = self::vectorFromArray($data['relativeVelocity'] ?? null);
        $this->separationValue = (float) ($data['separation'] ?? 0.0);
        $this->isTriggerValue = (bool) ($data['isTrigger'] ?? false);
    }

    private static function componentFromSerializedData(mixed $data): ?Component
    {
        if (!\is_array($data)) {
            return null;
        }

        $gameObjectData = \is_array($data['gameObject'] ?? null) ? $data['gameObject'] : [];
        $gameObject = GameObject::fromSerializedReference($gameObjectData);
        if (!$gameObject instanceof GameObject) {
            return null;
        }

        $componentType = isset($data['componentType']) && \is_string($data['componentType'])
            ? $data['componentType']
            : '';
        if ($componentType === '') {
            return null;
        }

        $component = $componentType === 'Transform'
            ? $gameObject->transform
            : $gameObject->getComponent($componentType);

        return $component instanceof Component ? $component : null;
    }

    /**
     * @param array{x?: float|int, y?: float|int, z?: float|int}|mixed $data
     */
    private static function vectorFromArray(mixed $data): ?Vector3
    {
        if (!\is_array($data)) {
            return null;
        }

        return new Vector3(
            (float) ($data['x'] ?? 0.0),
            (float) ($data['y'] ?? 0.0),
            (float) ($data['z'] ?? 0.0),
        );
    }

    /**
     * @return array{x: float, y: float, z: float}|null
     */
    private static function vectorToArray(?Vector3 $value): ?array
    {
        if (!$value instanceof Vector3) {
            return null;
        }

        return [
            'x' => $value->x,
            'y' => $value->y,
            'z' => $value->z,
        ];
    }
}
