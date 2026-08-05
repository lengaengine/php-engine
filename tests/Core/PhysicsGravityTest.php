<?php

declare(strict_types=1);

namespace {
    /**
     * @return array{x: float, y: float}
     */
    function lenga_internal_physics2d_get_gravity(): array
    {
        return $GLOBALS['lenga_physics_gravity_test_state']['gravity2D'];
    }

    function lenga_internal_physics2d_set_gravity(float $x, float $y): bool
    {
        $GLOBALS['lenga_physics_gravity_test_state']['gravity2D'] = ['x' => $x, 'y' => $y];

        return true;
    }

    /**
     * @return array{x: float, y: float, z: float}
     */
    function lenga_internal_physics3d_get_gravity(): array
    {
        return $GLOBALS['lenga_physics_gravity_test_state']['gravity3D'];
    }

    function lenga_internal_physics3d_set_gravity(float $x, float $y, float $z): bool
    {
        $GLOBALS['lenga_physics_gravity_test_state']['gravity3D'] = ['x' => $x, 'y' => $y, 'z' => $z];

        return true;
    }
}

namespace Lenga\Engine\Tests\Core {
    use Lenga\Engine\Core\Physics2D;
    use Lenga\Engine\Core\Physics3D;
    use Lenga\Engine\Core\Vector2;
    use Lenga\Engine\Core\Vector3;
    use PHPUnit\Framework\TestCase;

    final class PhysicsGravityTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_physics_gravity_test_state'] = [
                'gravity2D' => ['x' => 0.0, 'y' => -9.8],
                'gravity3D' => ['x' => 0.0, 'y' => -9.8, 'z' => 0.0],
            ];
        }

        public function testPhysics2DGravityRoundTripsThroughNativeRuntime(): void
        {
            Physics2D::setGravity(new Vector2(1.25, -4.5));

            $gravity = Physics2D::getGravity();

            self::assertSame(1.25, $gravity->x);
            self::assertSame(-4.5, $gravity->y);
        }

        public function testPhysics3DGravityRoundTripsThroughNativeRuntime(): void
        {
            Physics3D::setGravity(new Vector3(0.5, -9.8, 2.0));

            $gravity = Physics3D::getGravity();

            self::assertSame(0.5, $gravity->x);
            self::assertSame(-9.8, $gravity->y);
            self::assertSame(2.0, $gravity->z);
        }
    }
}
