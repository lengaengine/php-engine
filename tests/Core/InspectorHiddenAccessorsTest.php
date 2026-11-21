<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Attributes\HideInInspector;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Component;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * The editor reads the properties a script or component class inherits.
 * The public accessors of the engine's base classes are runtime handles: the
 * Inspector draws no field for them and a scene saves no value for them.
 */
final class InspectorHiddenAccessorsTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function baseClasses(): iterable
    {
        yield 'Behaviour' => [Behaviour::class];
        yield 'Component' => [Component::class];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('baseClasses')]
    public function testEveryPublicAccessorIsHiddenFromTheInspector(string $className): void
    {
        $reflection = new ReflectionClass($className);
        $publicProperties = array_filter(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            static fn (ReflectionProperty $property): bool =>
                !$property->isStatic() && $property->getDeclaringClass()->getName() === $className,
        );

        self::assertNotEmpty($publicProperties);
        foreach ($publicProperties as $property) {
            self::assertNotEmpty(
                $property->getAttributes(HideInInspector::class),
                "{$className}::\${$property->getName()} must carry #[HideInInspector].",
            );
        }
    }
}
