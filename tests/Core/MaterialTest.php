<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use InvalidArgumentException;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\Material;
use Lenga\Engine\Core\Vector2;
use PHPUnit\Framework\TestCase;

final class MaterialTest extends TestCase
{
    public function testMaterialDefaultsMatchStandardRenderSurface(): void
    {
        $material = new Material();

        self::assertNull($material->assetPath);
        self::assertSame('Material', $material->name);
        self::assertSame(Material::SHADER_LIT, $material->shader);
        self::assertSame(Material::RENDERING_OPAQUE, $material->renderingMode);
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255], $material->baseColor->toRGBA());
        self::assertSame(0.0, $material->metallic);
        self::assertSame(0.5, $material->smoothness);
        self::assertSame(1.0, $material->tiling->x);
        self::assertSame(1.0, $material->tiling->y);
        self::assertSame(0.0, $material->offset->x);
        self::assertSame(0.0, $material->offset->y);
    }

    public function testMaterialCanReferenceAnAsset(): void
    {
        $material = Material::fromAssetPath('Assets/Materials/RollerBall.material.json');

        self::assertSame('Assets/Materials/RollerBall.material.json', $material->assetPath);
        self::assertSame('Assets/Materials/RollerBall.material.json', (string) $material);
    }

    public function testColorAliasUpdatesBaseColor(): void
    {
        $material = new Material();

        $material->color = Color::cyan();

        self::assertSame(['r' => 0, 'g' => 255, 'b' => 255, 'a' => 255], $material->baseColor->toRGBA());
        self::assertSame($material->baseColor, $material->color);
        self::assertTrue($material->hasCustomBaseColor());
    }

    public function testRendererOwnedMaterialNotifiesWhenMutableSurfaceStateChanges(): void
    {
        $calls = [];
        $material = Material::fromAssetPath('Assets/Materials/RollerBall.material.json');
        $material->bindChangeListener(static function (Material $changedMaterial) use (&$calls): void {
            $calls[] = [
                'assetPath' => $changedMaterial->assetPath,
                'color' => $changedMaterial->color->toRGBA(),
            ];
        });

        $material->color = Color::cyan();
        $material->assetPath = 'Assets/Materials/Updated.material.json';

        self::assertSame([
            [
                'assetPath' => 'Assets/Materials/RollerBall.material.json',
                'color' => ['r' => 0, 'g' => 255, 'b' => 255, 'a' => 255],
            ],
            [
                'assetPath' => 'Assets/Materials/Updated.material.json',
                'color' => ['r' => 0, 'g' => 255, 'b' => 255, 'a' => 255],
            ],
        ], $calls);
    }

    public function testMaterialSerializesShaderAndTextureState(): void
    {
        $material = new Material(
            assetPath: 'Assets/Materials/Ground.material.json',
            name: 'Ground',
            shader: Material::SHADER_UNLIT,
            renderingMode: Material::RENDERING_TRANSPARENT,
            shaderAssetPath: 'Assets/Shaders/Default.shader.json',
            baseColor: Color::fromRGBA(10, 20, 30, 40),
            albedo: 'Assets/Images/ground.png',
            metallic: 0.25,
            smoothness: 0.75,
            normalMap: 'Assets/Images/ground-normal.png',
            heightMap: 'Assets/Images/ground-height.png',
            occlusion: 'Assets/Images/ground-ao.png',
            emission: 0.5,
            detailMask: 'Assets/Images/detail-mask.png',
            tiling: new Vector2(2.0, 3.0),
            offset: new Vector2(0.25, 0.5),
            detailAlbedo: 'Assets/Images/detail.png',
            detailNormalMap: 'Assets/Images/detail-normal.png',
            secondaryTiling: new Vector2(4.0, 5.0),
            secondaryOffset: new Vector2(0.75, 0.125),
            secondaryUvSet: Material::UV1,
        );

        $roundTripped = unserialize(serialize($material), ['allowed_classes' => [Material::class, Color::class, Vector2::class]]);

        self::assertInstanceOf(Material::class, $roundTripped);
        self::assertSame('Ground', $roundTripped->name);
        self::assertSame(Material::SHADER_UNLIT, $roundTripped->shader);
        self::assertSame(Material::RENDERING_TRANSPARENT, $roundTripped->renderingMode);
        self::assertSame('Assets/Images/ground.png', $roundTripped->albedo);
        self::assertSame(['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40], $roundTripped->baseColor->toRGBA());
        self::assertSame(2.0, $roundTripped->tiling->x);
        self::assertSame(3.0, $roundTripped->tiling->y);
        self::assertSame(Material::UV1, $roundTripped->secondaryUvSet);
    }

    public function testMaterialRejectsInvalidEnums(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Material(shader: 'UnknownShader');
    }

    public function testMaterialRequiresNonEmptyAssetPath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Material::fromAssetPath('   ');
    }
}
