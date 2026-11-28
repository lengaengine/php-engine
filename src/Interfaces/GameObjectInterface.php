<?php

declare(strict_types=1);

namespace Lenga\Engine\Interfaces;

/**
 * Lists the members of a scene object: its name, active state, hierarchy and components.
 *
 * {@see \Lenga\Engine\Core\GameObject} does not implement this interface, so
 * `$value instanceof GameObjectInterface` is false for a GameObject. Use
 * `GameObject` as the type in your scripts.
 */
interface GameObjectInterface
{
    /** The object's name, as shown in the Hierarchy. */
    public string $name { get; set; }

    /** Whether the object is active and all of its parents are active too. */
    public bool $activeInHierarchy { get; }

    /** The object's own active setting, whatever its parents' settings are. */
    public bool $activeSelf { get; set; }

    /** The Transform that holds the object's position, rotation and scale. */
    public TransformInterface $transform { get; }

    /**
     * Turns the object on or off.
     *
     * @param bool $value True to activate the object, false to deactivate it.
     */
    public function setActive(bool $value): void;

    /** Returns the object's own active setting. */
    public function isActiveSelf(): bool;

    /** Returns whether the object and all of its parents are active. */
    public function isActiveInHierarchy(): bool;

    /** Returns the scene the object belongs to, or null when it has none. */
    public function getScene(): ?SceneInterface;

    /** Returns the object's parent, or null for an object at the top of the hierarchy. */
    public function getParent(): ?self;

    /**
     * Returns the object's direct children.
     *
     * @return list<self>
     */
    public function getChildren(): array;

    /** Returns the number of direct children. */
    public function childCount(): int;

    /**
     * Moves the object under a new parent.
     *
     * @param self|null $parent The new parent, or null to move the object to the top of the hierarchy.
     * @param bool $worldPositionStays True to keep the object where it is in the world, false to keep its local position, rotation and scale relative to the new parent.
     * @return bool True when the parent changed.
     */
    public function setParent(?self $parent, bool $worldPositionStays = true): bool;

    /**
     * Checks whether the object has a component of a type.
     *
     * @param string $type Component class, such as `Rigidbody2D::class`, or the short name of a built-in component.
     */
    public function hasComponent(string $type): bool;

    /**
     * Returns the first component of the requested type, or null when the object has none.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type Component class, such as `Rigidbody2D::class`, or the short name of a built-in component.
     * @return TComponent|null
     */
    public function getComponent(string $type): object|null;

    /**
     * Adds a component of the requested type to the object and returns it.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type Component class, such as `Rigidbody2D::class`, or the short name of a built-in component.
     * @return TComponent
     */
    public function addComponent(string $type): object;

    /** Removes the object and its children from the scene. */
    public function destroy(): void;
}
