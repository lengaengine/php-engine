<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Exceptions\LengaRuntimeUnavailableException;
use Lenga\Engine\SceneManagement\Scene;
use LogicException;
use PHPUnit\Framework\TestCase;

final class NativeRuntimeBoundaryTest extends TestCase
{
    public function testNativeEngineReportsUnavailableOutsideLengaRuntime(): void
    {
        self::assertFalse(NativeEngine::isAvailable());

        $this->expectException(LengaRuntimeUnavailableException::class);
        $this->expectExceptionMessage('plain PHP process');

        NativeEngine::call('scene_get_active');
    }

    public function testNativeEngineRejectsPhysicalNativeNames(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('logical binding names');

        NativeEngine::call('lenga' . '_internal_scene_get_active');
    }

    public function testPublicApisFailCleanlyOutsideLengaRuntime(): void
    {
        $this->expectException(LengaRuntimeUnavailableException::class);
        $this->expectExceptionMessage('Lenga native runtime');

        Scene::getActive();
    }
}
