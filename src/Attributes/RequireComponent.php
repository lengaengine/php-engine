<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Declares components that must exist on the same GameObject as a behaviour.
 *
 * When a behaviour with this attribute is attached, Lenga automatically adds
 * any missing required components through the normal component creation path.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequireComponent
{
    /**
     * @var list<string>
     */
    public readonly array $componentTypes;

    /**
     * @param string ...$componentTypes Fully-qualified class names, ::class constants, or native component class names.
     */
    public function __construct(string ...$componentTypes)
    {
        $this->componentTypes = $componentTypes;
    }
}
