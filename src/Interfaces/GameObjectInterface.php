<?php

declare(strict_types=1);

namespace Lenga\Engine\Interfaces;

interface GameObjectInterface
{
    public string $name { get; set; }
    public bool $activeInHierarchy { get; }
    public bool $activeSelf { get; set; }
    public TransformInterface $transform { get; }

    public function setActive(bool $value): void;
    public function isActiveSelf(): bool;
    public function isActiveInHierarchy(): bool;
    public function getScene(): ?SceneInterface;
    public function getParent(): ?self;
    public function getChildren(): array;
    public function childCount(): int;
    public function setParent(?self $parent, bool $worldPositionStays = true): bool;
    public function hasComponent(string $type): bool;

    /**
     * Resolves the first component of the requested type.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent|null
     */
    public function getComponent(string $type): object|null;

    /**
     * Adds a component to this object.
     *
     * @template TComponent of object
     * @param class-string<TComponent>|non-empty-string $type
     * @return TComponent
     */
    public function addComponent(string $type): object;
    public function destroy(): void;
}
