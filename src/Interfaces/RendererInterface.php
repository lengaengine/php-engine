<?php

declare(strict_types=1);

namespace Lenga\Engine\Interfaces;

/**
 * The type every renderer component has, the components that draw something in the scene.
 *
 * {@see \Lenga\Engine\Core\Renderer} implements it, so sprite, mesh, model
 * and other renderers share it. Use it as a parameter or property type when
 * code should accept any renderer. Passing it to
 * {@see \Lenga\Engine\Core\GameObject::getComponent()} returns the first
 * renderer on the GameObject.
 *
 * ```php
 * use Lenga\Engine\Core\Behaviour;
 * use Lenga\Engine\Interfaces\RendererInterface;
 *
 * final class HideOnStart extends Behaviour
 * {
 *     public function start(): void
 *     {
 *         $renderer = $this->gameObject->getComponent(RendererInterface::class);
 *         if ($renderer !== null) {
 *             $renderer->enabled = false;
 *         }
 *     }
 * }
 * ```
 */
interface RendererInterface extends ComponentInterface
{
}
