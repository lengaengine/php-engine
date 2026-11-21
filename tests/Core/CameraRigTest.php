<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\CameraRig;
use Lenga\Engine\Core\Component;
use Lenga\Engine\Core\Transform;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Core\Vector3;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

final class CameraRigTest extends TestCase
{
    public function testCameraRigIsAComponent(): void
    {
        self::assertTrue(is_subclass_of(CameraRig::class, Component::class));
    }

    public function testCameraRigExposesItsSettingsWithTheTypesTheEngineReads(): void
    {
        $expected = [
            'target' => Transform::class,
            'targetId' => 'string',
            'offset' => Vector3::class,
            'dampingTime' => Vector3::class,
            'maxSpeed' => 'float',
            'deadZone' => Vector2::class,
            'lookaheadDistance' => 'float',
            'lookaheadDampingTime' => 'float',
            'boundsEnabled' => 'bool',
            'boundsMin' => Vector2::class,
            'boundsMax' => Vector2::class,
            'targetSize' => 'float',
            'zoomDampingTime' => 'float',
        ];

        foreach ($expected as $name => $type) {
            $propertyType = (new ReflectionProperty(CameraRig::class, $name))->getType();
            self::assertInstanceOf(ReflectionNamedType::class, $propertyType, $name);
            self::assertSame($type, $propertyType->getName(), $name);
        }

        $target = (new ReflectionProperty(CameraRig::class, 'target'))->getType();
        self::assertTrue($target !== null && $target->allowsNull(), 'target accepts null to follow nothing');
    }

    public function testShakeTakesAmplitudeDurationFrequencyAndRoll(): void
    {
        $parameters = (new ReflectionMethod(CameraRig::class, 'shake'))->getParameters();

        self::assertSame(['amplitude', 'duration', 'frequency', 'rollDegrees'], array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            $parameters,
        ));
        self::assertSame(18.0, $parameters[2]->getDefaultValue());
        self::assertSame(0.0, $parameters[3]->getDefaultValue());
    }

    public function testSnapToTargetTakesNoArguments(): void
    {
        self::assertSame(0, (new ReflectionMethod(CameraRig::class, 'snapToTarget'))->getNumberOfParameters());
    }
}
