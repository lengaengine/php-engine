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
    public function getComponent(string $type): object|null;
    public function addComponent(string $type): object;
    public function destroy(): void;
}
