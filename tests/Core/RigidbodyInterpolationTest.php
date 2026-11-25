<?php

declare(strict_types=1);

namespace {
    if (!function_exists('lenga_internal_rigidbody3d_get_state')) {
        /** @return array<string, mixed> */
        function lenga_internal_rigidbody3d_get_state(int $componentId): array
        {
            return $GLOBALS['lenga_rb_interp_test_state']['rb3d'][$componentId] ?? [];
        }
    }

    if (!function_exists('lenga_internal_rigidbody3d_set_interpolation')) {
        function lenga_internal_rigidbody3d_set_interpolation(int $componentId, string $mode): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb3d'][$componentId]['interpolation'] = $mode;
            return true;
        }
    }

    if (!function_exists('lenga_internal_rigidbody3d_move_position')) {
        function lenga_internal_rigidbody3d_move_position(int $componentId, float $x, float $y, float $z): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb3d_moves'][$componentId]['pos'] = [$x, $y, $z];
            return true;
        }
    }

    if (!function_exists('lenga_internal_rigidbody3d_move_rotation')) {
        function lenga_internal_rigidbody3d_move_rotation(int $componentId, float $x, float $y, float $z, float $w): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb3d_moves'][$componentId]['rot'] = [$x, $y, $z, $w];
            return true;
        }
    }

    if (!function_exists('lenga_internal_rigidbody2d_get_state')) {
        /** @return array<string, mixed> */
        function lenga_internal_rigidbody2d_get_state(int $componentId): array
        {
            return $GLOBALS['lenga_rb_interp_test_state']['rb2d'][$componentId] ?? [];
        }
    }

    if (!function_exists('lenga_internal_rigidbody2d_set_interpolation')) {
        function lenga_internal_rigidbody2d_set_interpolation(int $componentId, string $mode): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb2d'][$componentId]['interpolation'] = $mode;
            return true;
        }
    }

    if (!function_exists('lenga_internal_rigidbody2d_move_position')) {
        function lenga_internal_rigidbody2d_move_position(int $componentId, float $x, float $y): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb2d_moves'][$componentId]['pos'] = [$x, $y];
            return true;
        }
    }

    if (!function_exists('lenga_internal_rigidbody2d_move_rotation')) {
        function lenga_internal_rigidbody2d_move_rotation(int $componentId, float $angleDegrees): bool
        {
            $GLOBALS['lenga_rb_interp_test_state']['rb2d_moves'][$componentId]['rot'] = $angleDegrees;
            return true;
        }
    }
}

namespace Lenga\Engine\Tests\Core {
    use Lenga\Engine\Core\GameObject;
    use Lenga\Engine\Core\Quaternion;
    use Lenga\Engine\Core\Rigidbody2D;
    use Lenga\Engine\Core\Rigidbody3D;
    use Lenga\Engine\Core\Vector2;
    use Lenga\Engine\Core\Vector3;
    use Lenga\Engine\Enumerations\RigidbodyInterpolation;
    use PHPUnit\Framework\TestCase;
    use ReflectionClass;

    final class RigidbodyInterpolationTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_rb_interp_test_state'] = [
                'rb3d' => [101 => ['interpolation' => 'None']],
                'rb2d' => [202 => ['interpolation' => 'None']],
                'rb3d_moves' => [],
                'rb2d_moves' => [],
            ];
        }

        private function makeGameObjectStub(): GameObject
        {
            return (new ReflectionClass(GameObject::class))->newInstanceWithoutConstructor();
        }

        public function testRigidbody3DInterpolationAndMoveMethodsRoundTrip(): void
        {
            $rb = new Rigidbody3D($this->makeGameObjectStub(), 101);

            self::assertSame(RigidbodyInterpolation::None, $rb->interpolation);

            $rb->interpolation = RigidbodyInterpolation::Interpolate;
            self::assertSame(RigidbodyInterpolation::Interpolate, $rb->interpolation);

            $rb->interpolation = RigidbodyInterpolation::Extrapolate;
            self::assertSame(RigidbodyInterpolation::Extrapolate, $rb->interpolation);

            $rb->movePosition(new Vector3(1.5, -2.25, 10.5));
            $rb->moveRotation(new Quaternion(0.1, 0.2, 0.3, 0.9));

            self::assertSame([1.5, -2.25, 10.5], $GLOBALS['lenga_rb_interp_test_state']['rb3d_moves'][101]['pos']);
            self::assertSame([0.1, 0.2, 0.3, 0.9], $GLOBALS['lenga_rb_interp_test_state']['rb3d_moves'][101]['rot']);
        }

        public function testRigidbody2DInterpolationAndMoveMethodsRoundTrip(): void
        {
            $rb = new Rigidbody2D($this->makeGameObjectStub(), 202);

            self::assertSame(RigidbodyInterpolation::None, $rb->interpolation);

            $rb->interpolation = RigidbodyInterpolation::Interpolate;
            self::assertSame(RigidbodyInterpolation::Interpolate, $rb->interpolation);

            $rb->movePosition(new Vector2(3.5, -4.0));
            $rb->moveRotation(45.5);

            self::assertSame([3.5, -4.0], $GLOBALS['lenga_rb_interp_test_state']['rb2d_moves'][202]['pos']);
            self::assertSame(45.5, $GLOBALS['lenga_rb_interp_test_state']['rb2d_moves'][202]['rot']);
        }
    }
}
