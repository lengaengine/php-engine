<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use InvalidArgumentException;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\HdrColor;
use Lenga\Engine\Core\Material;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\MaterialBlendingMode;
use Lenga\Engine\Enumerations\MaterialDepthTest;
use Lenga\Engine\Enumerations\MaterialDepthWrite;
use Lenga\Engine\Enumerations\MaterialRenderFace;
use Lenga\Engine\Enumerations\MaterialShaderMode;
use Lenga\Engine\Enumerations\MaterialSurfaceType;
use Lenga\Engine\Enumerations\MaterialUvSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypeError;
use function array_column;
use function log;

final class MaterialTest extends TestCase
{
    public function testMaterialDefaultsMatchStandardRenderSurface(): void
    {
        $material = new Material();

        self::assertNull($material->assetPath);
        self::assertSame('Material', $material->name);
        self::assertSame(MaterialShaderMode::Lit, $material->shader);
        self::assertSame(MaterialUvSet::UV0, $material->secondaryUvSet);
        self::assertSame(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255], $material->baseColor->toRGBA());
        self::assertSame(0.0, $material->metallic);
        self::assertSame(0.5, $material->smoothness);
        self::assertSame(1.0, $material->tiling->x);
        self::assertSame(1.0, $material->tiling->y);
        self::assertSame(0.0, $material->offset->x);
        self::assertSame(0.0, $material->offset->y);
    }

    public function testSurfaceOptionDefaultsMatchTheEngine(): void
    {
        $material = new Material();

        self::assertSame(MaterialSurfaceType::Opaque, $material->surfaceType);
        self::assertSame(MaterialBlendingMode::Alpha, $material->blendingMode);
        self::assertSame(MaterialRenderFace::Front, $material->renderFace);
        self::assertFalse($material->alphaClipping);
        self::assertSame(0.5, $material->alphaClipThreshold);
        self::assertSame(0, $material->sortingPriority);
        self::assertSame(MaterialDepthWrite::Auto, $material->depthWrite);
        self::assertSame(MaterialDepthTest::LessEqual, $material->depthTest);
        self::assertSame(-50, Material::MIN_SORTING_PRIORITY);
        self::assertSame(50, Material::MAX_SORTING_PRIORITY);
    }

    public function testEmissionDefaultsMatchTheEngine(): void
    {
        $material = new Material();

        self::assertFalse($material->emissionEnabled);
        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255], $material->emissionColor->color->toRGBA());
        self::assertSame(HdrColor::NEUTRAL_INTENSITY, $material->emissionColor->intensity);
        self::assertNull($material->emissionMap);
    }

    /**
     * @return array<string, array{list<\BackedEnum>, list<string>}>
     */
    public static function engineEnumNames(): array
    {
        return [
            'surface type' => [MaterialSurfaceType::cases(), ['Opaque', 'Transparent']],
            'blending mode' => [MaterialBlendingMode::cases(), ['Alpha', 'Premultiply', 'Additive', 'Multiply']],
            'render face' => [MaterialRenderFace::cases(), ['Front', 'Back', 'Both']],
            'depth write' => [MaterialDepthWrite::cases(), ['Auto', 'ForceEnabled', 'ForceDisabled']],
            'depth test' => [
                MaterialDepthTest::cases(),
                ['Never', 'Less', 'Equal', 'LessEqual', 'Greater', 'NotEqual', 'GreaterEqual', 'Always'],
            ],
            'shader' => [MaterialShaderMode::cases(), ['Lit', 'Unlit']],
            'uv set' => [MaterialUvSet::cases(), ['UV0', 'UV1']],
        ];
    }

    /**
     * The names and their order are those of the engine's name tables in
     * src/Rendering/MaterialRenderState.cpp and MaterialAsset.cpp.
     *
     * @param list<\BackedEnum> $cases
     * @param list<string> $names
     */
    #[DataProvider('engineEnumNames')]
    public function testEnumValuesAreTheEngineNames(array $cases, array $names): void
    {
        self::assertSame($names, array_column($cases, 'value'));
    }

    public function testDeprecatedShaderAndUvSetConstantsNameTheEnumCases(): void
    {
        self::assertSame(MaterialShaderMode::Lit, Material::SHADER_LIT);
        self::assertSame(MaterialShaderMode::Unlit, Material::SHADER_UNLIT);
        self::assertSame(MaterialUvSet::UV0, Material::UV0);
        self::assertSame(MaterialUvSet::UV1, Material::UV1);
    }

    public function testMaterialRejectsAShaderNameString(): void
    {
        $this->expectException(TypeError::class);

        new Material(shader: 'Unlit');
    }

    /**
     * @return array<string, array{string, MaterialSurfaceType, MaterialBlendingMode, bool}>
     */
    public static function renderingModes(): array
    {
        return [
            'Opaque' => [Material::RENDERING_OPAQUE, MaterialSurfaceType::Opaque, MaterialBlendingMode::Alpha, false],
            'Cutout' => [Material::RENDERING_CUTOUT, MaterialSurfaceType::Opaque, MaterialBlendingMode::Alpha, true],
            'Fade' => [Material::RENDERING_FADE, MaterialSurfaceType::Transparent, MaterialBlendingMode::Alpha, false],
            'Transparent' => [
                Material::RENDERING_TRANSPARENT,
                MaterialSurfaceType::Transparent,
                MaterialBlendingMode::Premultiply,
                false,
            ],
        ];
    }

    #[DataProvider('renderingModes')]
    public function testRenderingModeSetsSurfaceOptionsAsTheEngineConvertsThem(
        string $mode,
        MaterialSurfaceType $surfaceType,
        MaterialBlendingMode $blendingMode,
        bool $alphaClipping,
    ): void {
        $material = new Material(
            surfaceType: MaterialSurfaceType::Transparent,
            blendingMode: MaterialBlendingMode::Multiply,
            renderFace: MaterialRenderFace::Both,
            alphaClipping: true,
            alphaClipThreshold: 0.2,
            sortingPriority: 7,
            depthWrite: MaterialDepthWrite::ForceEnabled,
            depthTest: MaterialDepthTest::Always,
        );

        $material->renderingMode = $mode;

        self::assertSame($surfaceType, $material->surfaceType);
        self::assertSame($blendingMode, $material->blendingMode);
        self::assertSame($alphaClipping, $material->alphaClipping);
        self::assertSame($mode === Material::RENDERING_CUTOUT ? 0.5 : 0.2, $material->alphaClipThreshold);
        self::assertSame($mode, $material->renderingMode);
        self::assertSame(MaterialRenderFace::Both, $material->renderFace);
        self::assertSame(7, $material->sortingPriority);
        self::assertSame(MaterialDepthWrite::ForceEnabled, $material->depthWrite);
        self::assertSame(MaterialDepthTest::Always, $material->depthTest);
    }

    #[DataProvider('renderingModes')]
    public function testRenderingModeConstructorParameterSetsSurfaceOptions(
        string $mode,
        MaterialSurfaceType $surfaceType,
        MaterialBlendingMode $blendingMode,
        bool $alphaClipping,
    ): void {
        $material = new Material(renderingMode: $mode);

        self::assertSame($surfaceType, $material->surfaceType);
        self::assertSame($blendingMode, $material->blendingMode);
        self::assertSame($alphaClipping, $material->alphaClipping);
        self::assertSame($mode, $material->renderingMode);
    }

    public function testRenderingModeReadsAdditiveAndMultiplyAsTransparent(): void
    {
        self::assertSame(
            Material::RENDERING_TRANSPARENT,
            (new Material(surfaceType: MaterialSurfaceType::Transparent, blendingMode: MaterialBlendingMode::Additive))->renderingMode,
        );
        self::assertSame(
            Material::RENDERING_TRANSPARENT,
            (new Material(surfaceType: MaterialSurfaceType::Transparent, blendingMode: MaterialBlendingMode::Multiply))->renderingMode,
        );
    }

    public function testRenderingModeRejectsAnUnknownMode(): void
    {
        $material = new Material();

        $this->expectException(InvalidArgumentException::class);

        $material->renderingMode = 'Glass';
    }

    /**
     * @return array<string, array{float, ?string}>
     */
    public static function scalarEmissions(): array
    {
        return [
            'off without a base texture' => [0.0, null],
            'off with a base texture' => [0.0, 'Assets/Images/lamp.png'],
            'one without a base texture' => [1.0, null],
            'one with a base texture' => [1.0, 'Assets/Images/lamp.png'],
            'one and a half without a base texture' => [1.5, null],
            'one and a half with a base texture' => [1.5, 'Assets/Images/lamp.png'],
        ];
    }

    #[DataProvider('scalarEmissions')]
    public function testEmissionSetsEmissionAsTheEngineConvertsVersionOneFiles(float $emission, ?string $albedo): void
    {
        $material = new Material(baseColor: Color::fromRGBA(200, 100, 50, 128), albedo: $albedo);

        $material->emission = $emission;

        self::assertEqualsWithDelta($emission, $material->emission, 1e-12);
        if ($emission === 0.0) {
            self::assertFalse($material->emissionEnabled);
            self::assertSame(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255], $material->emissionColor->color->toRGBA());
            self::assertNull($material->emissionMap);

            return;
        }

        self::assertTrue($material->emissionEnabled);
        self::assertSame(['r' => 200, 'g' => 100, 'b' => 50, 'a' => 255], $material->emissionColor->color->toRGBA());
        self::assertEqualsWithDelta(log($emission, 2), $material->emissionColor->intensity, 1e-12);
        self::assertSame($albedo, $material->emissionMap);
    }

    #[DataProvider('scalarEmissions')]
    public function testEmissionConstructorParameterSetsEmission(float $emission, ?string $albedo): void
    {
        $material = new Material(baseColor: Color::fromRGBA(200, 100, 50), albedo: $albedo, emission: $emission);

        self::assertSame($emission > 0.0, $material->emissionEnabled);
        self::assertSame($emission > 0.0 ? $albedo : null, $material->emissionMap);
        self::assertEqualsWithDelta($emission, $material->emission, 1e-12);
    }

    public function testEmissionOffKeepsTheEmissionColourAndMap(): void
    {
        $material = new Material(albedo: 'Assets/Images/lamp.png', emission: 2.0);

        $material->emission = 0.0;

        self::assertFalse($material->emissionEnabled);
        self::assertSame(0.0, $material->emission);
        self::assertSame(1.0, $material->emissionColor->intensity);
        self::assertSame('Assets/Images/lamp.png', $material->emissionMap);
    }

    public function testEmissionReadsTheIntensityScaleWhenTheColourIsNotTheBaseColour(): void
    {
        $material = new Material(
            baseColor: Color::red(),
            emissionEnabled: true,
            emissionColor: new HdrColor(Color::blue(), 2.0),
        );

        self::assertSame(4.0, $material->emission);
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

    public function testMaterialWritesTheEngineMaterialFileLayout(): void
    {
        self::assertSame([
            'material' => [
                'name' => 'Ground',
                'version' => 2,
                'shader' => 'Unlit',
                'surfaceOptions' => [
                    'surfaceType' => 'Transparent',
                    'blendingMode' => 'Additive',
                    'renderFace' => 'Both',
                    'alphaClipping' => true,
                    'alphaClipThreshold' => 0.25,
                    'sortingPriority' => -3,
                    'depthWrite' => 'ForceDisabled',
                    'depthTest' => 'Greater',
                ],
            ],
            'baseColor' => '#0A141E28',
            'metallic' => 0.25,
            'smoothness' => 0.75,
            'emissionEnabled' => true,
            'emissionColor' => ['color' => '#FF800A', 'intensity' => 2.5],
            'shaderAssetPath' => 'Assets/Shaders/Default.shader.json',
            'baseTexturePath' => 'Assets/Images/ground.png',
            'normalMapPath' => 'Assets/Images/ground-normal.png',
            'heightMapPath' => 'Assets/Images/ground-height.png',
            'occlusionMapPath' => 'Assets/Images/ground-ao.png',
            'detailMaskPath' => 'Assets/Images/detail-mask.png',
            'detailAlbedoPath' => 'Assets/Images/detail.png',
            'detailNormalMapPath' => 'Assets/Images/detail-normal.png',
            'emissionMapPath' => 'Assets/Images/ground-emission.png',
            'tiling' => ['x' => 2.0, 'y' => 3.0],
            'offset' => ['x' => 0.25, 'y' => 0.5],
            'secondaryTiling' => ['x' => 4.0, 'y' => 5.0],
            'secondaryOffset' => ['x' => 0.75, 'y' => 0.125],
            'secondaryUvSet' => 'UV1',
        ], self::groundMaterial()->toAssetDocument());
    }

    public function testMaterialWritesEmptyStringsForTexturesThatAreNotSet(): void
    {
        $document = (new Material())->toAssetDocument();

        self::assertSame('#FFFFFF', $document['baseColor']);
        self::assertSame(['color' => '#000000', 'intensity' => 0.0], $document['emissionColor']);
        self::assertSame('', $document['baseTexturePath']);
        self::assertSame('', $document['emissionMapPath']);
        self::assertSame('', $document['shaderAssetPath']);
    }

    public function testMaterialReadsTheDocumentItWrites(): void
    {
        $material = Material::fromAssetDocument(
            self::groundMaterial()->toAssetDocument(),
            'Assets/Materials/Ground.material.json',
        );

        self::assertSame(self::groundMaterial()->toAssetDocument(), $material->toAssetDocument());
        self::assertSame('Assets/Materials/Ground.material.json', $material->assetPath);
        self::assertTrue($material->hasCustomBaseColor());
    }

    public function testMaterialDocumentReadsMissingAndUnknownValuesAsDefaults(): void
    {
        $material = Material::fromAssetDocument([
            'material' => [
                'version' => 2,
                'shader' => 'Toon',
                'surfaceOptions' => ['surfaceType' => 'Glass', 'sortingPriority' => 'high'],
            ],
            'emissionColor' => '#336699',
            'metallic' => 'shiny',
        ]);

        self::assertSame((new Material())->toAssetDocument()['material'], $material->toAssetDocument()['material']);
        self::assertSame(0.0, $material->metallic);
        self::assertSame(['r' => 51, 'g' => 102, 'b' => 153, 'a' => 255], $material->emissionColor->color->toRGBA());
        self::assertSame(HdrColor::NEUTRAL_INTENSITY, $material->emissionColor->intensity);
        self::assertFalse($material->hasCustomBaseColor());
    }

    public function testMaterialDocumentRejectsVersionOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Material::fromAssetDocument(['material' => ['version' => 1, 'renderingMode' => 'Fade']]);
    }

    public function testMaterialSerializesEveryField(): void
    {
        $roundTripped = unserialize(
            serialize(self::groundMaterial()),
            ['allowed_classes' => [Material::class, Color::class, HdrColor::class, Vector2::class]],
        );

        self::assertInstanceOf(Material::class, $roundTripped);
        self::assertSame('Assets/Materials/Ground.material.json', $roundTripped->assetPath);
        self::assertSame(self::groundMaterial()->toAssetDocument(), $roundTripped->toAssetDocument());
    }

    public function testMaterialRequiresNonEmptyAssetPath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Material::fromAssetPath('   ');
    }

    private static function groundMaterial(): Material
    {
        return new Material(
            assetPath: 'Assets/Materials/Ground.material.json',
            name: 'Ground',
            shader: MaterialShaderMode::Unlit,
            shaderAssetPath: 'Assets/Shaders/Default.shader.json',
            baseColor: Color::fromRGBA(10, 20, 30, 40),
            albedo: 'Assets/Images/ground.png',
            metallic: 0.25,
            smoothness: 0.75,
            normalMap: 'Assets/Images/ground-normal.png',
            heightMap: 'Assets/Images/ground-height.png',
            occlusion: 'Assets/Images/ground-ao.png',
            emissionEnabled: true,
            emissionColor: new HdrColor(Color::fromRGBA(255, 128, 10), 2.5),
            emissionMap: 'Assets/Images/ground-emission.png',
            detailMask: 'Assets/Images/detail-mask.png',
            tiling: new Vector2(2.0, 3.0),
            offset: new Vector2(0.25, 0.5),
            detailAlbedo: 'Assets/Images/detail.png',
            detailNormalMap: 'Assets/Images/detail-normal.png',
            secondaryTiling: new Vector2(4.0, 5.0),
            secondaryOffset: new Vector2(0.75, 0.125),
            secondaryUvSet: MaterialUvSet::UV1,
            surfaceType: MaterialSurfaceType::Transparent,
            blendingMode: MaterialBlendingMode::Additive,
            renderFace: MaterialRenderFace::Both,
            alphaClipping: true,
            alphaClipThreshold: 0.25,
            sortingPriority: -3,
            depthWrite: MaterialDepthWrite::ForceDisabled,
            depthTest: MaterialDepthTest::Greater,
        );
    }
}
