<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Input;
use Lenga\Engine\Enumerations\TouchPhase;
use Lenga\Engine\Exceptions\LengaRuntimeUnavailableException;
use PHPUnit\Framework\TestCase;

final class InputTest extends TestCase
{
    protected function tearDown(): void
    {
        Input::syncTouchSnapshotFromNative([], false, false);
    }

    public function testNativeTouchSnapshotSyncsStaticProperties(): void
    {
        Input::syncTouchSnapshotFromNative([
            [
                'fingerId' => 4,
                'position' => ['x' => 12.5, 'y' => 24.0],
                'rawPosition' => ['x' => 12.5, 'y' => 24.0],
                'deltaPosition' => ['x' => 2.5, 'y' => -1.0],
                'deltaTime' => 0.016,
                'phase' => 'Moved',
                'pressure' => 1.0,
                'maximumPossiblePressure' => 1.0,
                'tapCount' => 1,
            ],
        ], true, false);

        self::assertSame(1, Input::$touchCount);
        self::assertTrue(Input::$touchSupported);
        self::assertFalse(Input::$touchPressureSupported);
        self::assertSame(4, Input::$touches[0]->fingerId);
        self::assertSame(12.5, Input::$touches[0]->position->x);
        self::assertSame(24.0, Input::$touches[0]->position->y);
        self::assertSame(TouchPhase::MOVED, Input::$touches[0]->phase);
    }

    public function testMouseButtonApiRequiresNativeRuntime(): void
    {
        $this->expectException(LengaRuntimeUnavailableException::class);

        Input::getMouseButton(0);
    }

    public function testGetTouchRequiresNativeRuntime(): void
    {
        $this->expectException(LengaRuntimeUnavailableException::class);

        Input::getTouch(0);
    }
}
