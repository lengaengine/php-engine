<?php

declare(strict_types=1);

namespace Lenga\Engine\Interfaces;

/**
 * The type every component has, built-in components and Behaviour scripts alike.
 *
 * {@see \Lenga\Engine\Core\Component} and {@see \Lenga\Engine\Core\Behaviour}
 * implement it. Use it as a parameter or property type when code should
 * accept any component. Passing it to
 * {@see \Lenga\Engine\Core\GameObject::getComponent()} returns the first
 * component on the GameObject.
 *
 * ```php
 * use Lenga\Engine\Interfaces\ComponentInterface;
 *
 * function describe(ComponentInterface $component): string
 * {
 *     return $component::class;
 * }
 * ```
 */
interface ComponentInterface
{
}
