<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
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
