<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\SpriteRenderer;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\SpriteDrawMode;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use ReflectionProperty;

final class SpriteDrawModeTest extends TestCase
{
    public function testDrawModeValuesAreTheNamesTheEngineReads(): void
    {
        self::assertSame(['Simple', 'Tiled'], array_map(
            static fn (SpriteDrawMode $mode): string => $mode->value,
            SpriteDrawMode::cases(),
        ));
    }

    public function testSpriteRendererExposesDrawModeAndTileSize(): void
    {
        $drawMode = (new ReflectionProperty(SpriteRenderer::class, 'drawMode'))->getType();
        $tileSize = (new ReflectionProperty(SpriteRenderer::class, 'tileSize'))->getType();

        self::assertInstanceOf(ReflectionNamedType::class, $drawMode);
        self::assertSame(SpriteDrawMode::class, $drawMode->getName());
        self::assertInstanceOf(ReflectionNamedType::class, $tileSize);
        self::assertSame(Vector2::class, $tileSize->getName());
    }
}
