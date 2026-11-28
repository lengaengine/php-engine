<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Makes sure the components a Behaviour depends on exist on the same GameObject.
 *
 * Put the attribute on the Behaviour class and list the component classes it
 * needs. List several in one attribute or repeat the attribute:
 *
 * ```php
 * use Lenga\Engine\Attributes\RequireComponent;
 * use Lenga\Engine\Core\Behaviour;
 * use Lenga\Engine\Core\BoxCollider2D;
 * use Lenga\Engine\Core\Rigidbody2D;
 *
 * #[RequireComponent(Rigidbody2D::class, BoxCollider2D::class)]
 * final class PlayerController extends Behaviour
 * {
 *     public function fixedUpdate(): void
 *     {
 *         $body = $this->gameObject->getComponent(Rigidbody2D::class);
 *         // $body is always present here.
 *     }
 * }
 * ```
 *
 * When the Behaviour is attached to a GameObject, from the editor or with
 * {@see \Lenga\Engine\Core\GameObject::addComponent()}, Lenga adds each
 * listed component the GameObject does not have yet. Components that are
 * already there are left as they are. A listed class can be a built-in
 * component or another Behaviour script. If a required component cannot be
 * added, Lenga logs an error naming the Behaviour, the GameObject and the
 * missing component, and attaching the Behaviour fails with a
 * `RuntimeException`.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequireComponent
{
    /**
     * The class names of the required components, in the order they were listed.
     *
     * @var list<string>
     */
    public readonly array $componentTypes;

    /**
     * Creates the attribute with the components the Behaviour needs.
     *
     * @param string ...$componentTypes Component classes, usually written as `Rigidbody2D::class`. Fully qualified class names and the short names of built-in components also work.
     */
    public function __construct(string ...$componentTypes)
    {
        $this->componentTypes = $componentTypes;
    }
}
