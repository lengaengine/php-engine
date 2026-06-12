<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Quaternion;
use Lenga\Engine\Core\Vector3;
use PHPUnit\Framework\TestCase;

final class QuaternionTest extends TestCase
{
    public function testEulerAndAxisAngleFactoriesCreateExpectedRotations(): void
    {
        $yaw = Quaternion::euler(0.0, 90.0, 0.0);
        $axisYaw = Quaternion::angleAxis(90.0, Vector3::up());

        self::assertEqualsWithDelta($axisYaw->x, $yaw->x, 0.000001);
        self::assertEqualsWithDelta($axisYaw->y, $yaw->y, 0.000001);
        self::assertEqualsWithDelta($axisYaw->z, $yaw->z, 0.000001);
        self::assertEqualsWithDelta($axisYaw->w, $yaw->w, 0.000001);
    }

    public function testEulerAnglesPropertyCanReadAndWriteRotation(): void
    {
        $rotation = Quaternion::identity();
        $rotation->eulerAngles = new Vector3(0.0, 90.0, 0.0);

        self::assertEqualsWithDelta(90.0, $rotation->eulerAngles->y, 0.000001);
    }

    public function testFromToRotationRotatesOneDirectionToAnother(): void
    {
        $rotation = Quaternion::fromToRotation(Vector3::back(), Vector3::right());
        $rotated = $rotation->rotateVector(Vector3::back());

        self::assertEqualsWithDelta(1.0, $rotated->x, 0.000001);
        self::assertEqualsWithDelta(0.0, $rotated->y, 0.000001);
        self::assertEqualsWithDelta(0.0, $rotated->z, 0.000001);
    }

    public function testLookRotationMatchesLengaForwardConvention(): void
    {
        $identity = Quaternion::lookRotation(Vector3::back());

        self::assertEqualsWithDelta(0.0, Quaternion::angle(Quaternion::identity(), $identity), 0.000001);
    }

    public function testInterpolationAndRotateTowards(): void
    {
        $from = Quaternion::identity();
        $to = Quaternion::angleAxis(90.0, Vector3::up());

        $halfLerp = Quaternion::lerp($from, $to, 0.5);
        $halfSlerp = Quaternion::slerp($from, $to, 0.5);
        $step = Quaternion::rotateTowards($from, $to, 30.0);

        self::assertEqualsWithDelta(45.0, Quaternion::angle($from, $halfLerp), 1.0);
        self::assertEqualsWithDelta(45.0, Quaternion::angle($from, $halfSlerp), 0.000001);
        self::assertEqualsWithDelta(30.0, Quaternion::angle($from, $step), 0.000001);
    }

    public function testAngleAxisRoundTripAndDot(): void
    {
        $rotation = Quaternion::angleAxis(60.0, Vector3::up());
        $angleAxis = $rotation->toAngleAxis();

        self::assertEqualsWithDelta(60.0, $angleAxis['angle'], 0.000001);
        self::assertEqualsWithDelta(1.0, $angleAxis['axis']->y, 0.000001);
        self::assertEqualsWithDelta(1.0, Quaternion::dot($rotation->normalized, $rotation->normalized), 0.000001);
    }

    public function testSettersEqualityStringAndIndexAccess(): void
    {
        $rotation = new Quaternion();
        $rotation->set(1.0, 2.0, 3.0, 4.0);

        self::assertTrue($rotation->equals(new Quaternion(1.0, 2.0, 3.0, 4.0)));
        self::assertSame(1.0, $rotation[0]);
        self::assertSame(2.0, $rotation['y']);
        self::assertSame(3.0, $rotation[2]);
        self::assertSame(4.0, $rotation['w']);
        self::assertSame('(1, 2, 3, 4)', (string) $rotation);

        $rotation['w'] = 8.0;

        self::assertSame(8.0, $rotation->w);
    }

    public function testMutableDirectionFactories(): void
    {
        $rotation = Quaternion::identity();
        $rotation->setFromToRotation(Vector3::back(), Vector3::right());

        self::assertEqualsWithDelta(0.0, Quaternion::angle(
            Quaternion::fromToRotation(Vector3::back(), Vector3::right()),
            $rotation,
        ), 0.000001);

        $rotation->setLookRotation(Vector3::back());

        self::assertEqualsWithDelta(0.0, Quaternion::angle(Quaternion::identity(), $rotation), 0.000001);
    }
}
