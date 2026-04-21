<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Core\Vector4;
use PHPUnit\Framework\TestCase;

final class VectorTypesTest extends TestCase
{
    public function testVector2CanConvertToVector4(): void
    {
        $vector = (new Vector2(1.0, 2.0))->toVector4(3.0, 4.0);

        self::assertSame(1.0, $vector->x);
        self::assertSame(2.0, $vector->y);
        self::assertSame(3.0, $vector->z);
        self::assertSame(4.0, $vector->w);
    }

    public function testVector3CanConvertToVector2AndVector4(): void
    {
        $vector = new Vector3(5.0, 6.0, 7.0);

        $asVector2 = $vector->toVector2();
        $asVector4 = $vector->toVector4(8.0);

        self::assertSame(5.0, $asVector2->x);
        self::assertSame(6.0, $asVector2->y);
        self::assertSame(7.0, $asVector4->z);
        self::assertSame(8.0, $asVector4->w);
    }

    public function testVector4CanConvertBackDown(): void
    {
        $vector = new Vector4(1.0, 2.0, 3.0, 4.0);

        $asVector2 = $vector->toVector2();
        $asVector3 = $vector->toVector3();

        self::assertSame(1.0, $asVector2->x);
        self::assertSame(2.0, $asVector2->y);
        self::assertSame(1.0, $asVector3->x);
        self::assertSame(2.0, $asVector3->y);
        self::assertSame(3.0, $asVector3->z);
    }

    public function testVector4CoreHelpers(): void
    {
        $vector = new Vector4(2.0, 0.0, 0.0, 0.0);
        $normalized = $vector->normalized;
        $projected = Vector4::project(new Vector4(2.0, 2.0, 0.0, 0.0), new Vector4(1.0, 0.0, 0.0, 0.0));

        self::assertEqualsWithDelta(2.0, $vector->magnitude, 1e-9);
        self::assertEqualsWithDelta(1.0, $normalized->x, 1e-9);
        self::assertEqualsWithDelta(0.0, $normalized->w, 1e-9);
        self::assertSame(['x' => 2.0, 'y' => 0.0, 'z' => 0.0, 'w' => 0.0], $projected->toArray());
    }

    public function testVector4BridgeBackedHelpers(): void
    {
        $lerped = Vector4::lerp(new Vector4(0.0, 0.0, 0.0, 0.0), new Vector4(8.0, 4.0, 2.0, 6.0), 0.25);
        $moved = Vector4::moveTowards(new Vector4(0.0, 0.0, 0.0, 0.0), new Vector4(4.0, 0.0, 0.0, 0.0), 2.5);
        $clamped = Vector4::clampMagnitude(new Vector4(3.0, 4.0, 0.0, 0.0), 2.0);
        $projected = Vector4::project(new Vector4(2.0, 2.0, 0.0, 0.0), new Vector4(1.0, 0.0, 0.0, 0.0));

        self::assertEqualsWithDelta(2.0, $lerped->x, 1e-9);
        self::assertEqualsWithDelta(1.0, $lerped->y, 1e-9);
        self::assertEqualsWithDelta(0.5, $lerped->z, 1e-9);
        self::assertEqualsWithDelta(1.5, $lerped->w, 1e-9);
        self::assertEqualsWithDelta(2.5, $moved->x, 1e-9);
        self::assertEqualsWithDelta(2.0, $clamped->magnitude, 1e-9);
        self::assertSame(["x" => 2.0, "y" => 0.0, "z" => 0.0, "w" => 0.0], $projected->toArray());
    }
}
