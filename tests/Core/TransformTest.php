<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Quaternion;
use Lenga\Engine\Core\Transform;
use Lenga\Engine\Core\Vector3;
use PHPUnit\Framework\TestCase;

final class TransformTest extends TestCase
{
    public function testRotateAroundOrbitsPositionAndRotationInFallbackMode(): void
    {
        $transform = new Transform(localPosition: new Vector3(1.0, 0.0, 0.0));

        $transform->rotateAround(Vector3::zero(), Vector3::up(), 180.0);

        self::assertFloatEquals(-1.0, $transform->position->x);
        self::assertFloatEquals(0.0, $transform->position->y);
        self::assertFloatEquals(0.0, $transform->position->z);

        $forward = $transform->forward;
        self::assertFloatEquals(0.0, $forward->x);
        self::assertFloatEquals(0.0, $forward->y);
        self::assertFloatEquals(1.0, $forward->z);
    }

    public function testTransformPointAndInverseTransformPointRoundTrip(): void
    {
        $transform = new Transform(
            localPosition: new Vector3(10.0, 0.0, 0.0),
            localScale: new Vector3(2.0, 3.0, 4.0),
        );

        $worldPoint = $transform->transformPoint(new Vector3(1.0, 2.0, 3.0));

        self::assertFloatEquals(12.0, $worldPoint->x);
        self::assertFloatEquals(6.0, $worldPoint->y);
        self::assertFloatEquals(12.0, $worldPoint->z);

        $localPoint = $transform->inverseTransformPoint($worldPoint);

        self::assertFloatEquals(1.0, $localPoint->x);
        self::assertFloatEquals(2.0, $localPoint->y);
        self::assertFloatEquals(3.0, $localPoint->z);
    }

    public function testTransformDirectionIgnoresPositionAndScale(): void
    {
        $transform = new Transform(
            localPosition: new Vector3(10.0, 20.0, 30.0),
            localEulerAngles: new Vector3(0.0, 90.0, 0.0),
            localScale: new Vector3(2.0, 3.0, 4.0),
        );

        $direction = $transform->transformDirection(Vector3::right());

        self::assertFloatEquals(0.0, $direction->x);
        self::assertFloatEquals(0.0, $direction->y);
        self::assertFloatEquals(-1.0, $direction->z);

        $localDirection = $transform->inverseTransformDirection($direction);
        self::assertFloatEquals(1.0, $localDirection->x);
        self::assertFloatEquals(0.0, $localDirection->y);
        self::assertFloatEquals(0.0, $localDirection->z);
    }

    public function testTransformVectorAppliesScaleButNotPosition(): void
    {
        $transform = new Transform(
            localPosition: new Vector3(10.0, 20.0, 30.0),
            localScale: new Vector3(2.0, 3.0, 4.0),
        );

        $worldVector = $transform->transformVector(new Vector3(1.0, 2.0, 3.0));

        self::assertFloatEquals(2.0, $worldVector->x);
        self::assertFloatEquals(6.0, $worldVector->y);
        self::assertFloatEquals(12.0, $worldVector->z);

        $localVector = $transform->inverseTransformVector($worldVector);
        self::assertFloatEquals(1.0, $localVector->x);
        self::assertFloatEquals(2.0, $localVector->y);
        self::assertFloatEquals(3.0, $localVector->z);
    }

    public function testQuaternionCanBeCreatedFromAxisAngle(): void
    {
        $rotation = Quaternion::fromAxisAngle(Vector3::up(), 90.0);
        $rotated = $rotation->rotateVector(Vector3::right());

        self::assertFloatEquals(0.0, $rotated->x);
        self::assertFloatEquals(0.0, $rotated->y);
        self::assertFloatEquals(-1.0, $rotated->z);
    }

    private static function assertFloatEquals(float $expected, float $actual): void
    {
        self::assertEqualsWithDelta($expected, $actual, 0.0001);
    }
}
