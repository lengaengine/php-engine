<?php

declare(strict_types=1);

namespace {
    /**
     * @return list<array{assetPath: string, document: array<string, mixed>}>
     */
    function lenga_internal_renderer_get_materials(int $componentId): array
    {
        return $GLOBALS['lenga_renderer_material_slot_test']['slots'][$componentId] ?? [];
    }

    /**
     * @param list<array<string, mixed>> $materials
     */
    function lenga_internal_renderer_set_materials(int $componentId, array $materials): bool
    {
        $GLOBALS['lenga_renderer_material_slot_test']['calls'][] = ['set_materials', $componentId, $materials];

        return true;
    }

    /**
     * @param array<string, mixed> $material
     */
    function lenga_internal_renderer_set_material(int $componentId, int $slot, array $material): bool
    {
        $GLOBALS['lenga_renderer_material_slot_test']['calls'][] = ['set_material', $componentId, $slot, $material];

        return true;
    }

    /**
     * @return array{r: int, g: int, b: int, a: int}|false
     */
    function lenga_internal_renderer_get_color(int $componentId): array|false
    {
        return $GLOBALS['lenga_renderer_material_slot_test']['colors'][$componentId] ?? false;
    }

    function lenga_internal_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha = 255): bool
    {
        $GLOBALS['lenga_renderer_material_slot_test']['colors'][$componentId] = [
            'r' => $red,
            'g' => $green,
            'b' => $blue,
            'a' => $alpha,
        ];
        $GLOBALS['lenga_renderer_material_slot_test']['calls'][] = ['set_color', $componentId, $red, $green, $blue, $alpha];

        return true;
    }
}

namespace Lenga\Engine\Tests\Core {
    use InvalidArgumentException;
    use Lenga\Engine\Core\CircleRenderer;
    use Lenga\Engine\Core\Color;
    use Lenga\Engine\Core\CubeRenderer;
    use Lenga\Engine\Core\GameObject;
    use Lenga\Engine\Core\Material;
    use Lenga\Engine\Core\ModelRenderer;
    use Lenga\Engine\Core\RectangleRenderer;
    use Lenga\Engine\Core\SpriteRenderer;
    use Lenga\Engine\Enumerations\MaterialBlendingMode;
    use Lenga\Engine\Enumerations\MaterialDepthTest;
    use Lenga\Engine\Enumerations\MaterialRenderFace;
    use Lenga\Engine\Enumerations\MaterialSurfaceType;
    use PHPUnit\Framework\TestCase;

    final class RendererMaterialSlotTest extends TestCase
    {
        private const int COMPONENT_ID = 41;

        protected function setUp(): void
        {
            $GLOBALS['lenga_renderer_material_slot_test'] = ['slots' => [], 'colors' => [], 'calls' => []];
        }

        public function testReadingGivesEverySlotWithTheValuesItDrawsWith(): void
        {
            $glass = new Material(
                name: 'Glass',
                surfaceType: MaterialSurfaceType::Transparent,
                blendingMode: MaterialBlendingMode::Premultiply,
                renderFace: MaterialRenderFace::Both,
                sortingPriority: 4,
                depthTest: MaterialDepthTest::Always,
            );
            $default = new Material(name: 'Default-Material', baseColor: Color::red());
            $this->nativeSlots([
                ['assetPath' => 'Assets/Glass.material.json', 'document' => $glass->toAssetDocument()],
                ['assetPath' => '', 'document' => $default->toAssetDocument()],
            ]);

            $materials = $this->model()->materials;

            self::assertCount(2, $materials);
            self::assertSame('Assets/Glass.material.json', $materials[0]->assetPath);
            self::assertSame(MaterialSurfaceType::Transparent, $materials[0]->surfaceType);
            self::assertSame(MaterialBlendingMode::Premultiply, $materials[0]->blendingMode);
            self::assertSame(MaterialRenderFace::Both, $materials[0]->renderFace);
            self::assertSame(4, $materials[0]->sortingPriority);
            self::assertSame(MaterialDepthTest::Always, $materials[0]->depthTest);
            self::assertNull($materials[1]->assetPath);
            self::assertSame(Color::red()->toRGBA(), $materials[1]->baseColor->toRGBA());
            self::assertSame('Glass', $this->model()->material?->name);
        }

        public function testAssigningAFieldOfAReadMaterialChangesItsSlot(): void
        {
            $glass = new Material(surfaceType: MaterialSurfaceType::Transparent);
            $this->nativeSlots([
                ['assetPath' => '', 'document' => (new Material())->toAssetDocument()],
                ['assetPath' => 'Assets/Glass.material.json', 'document' => $glass->toAssetDocument()],
            ]);

            $material = $this->model()->materials[1];
            $material->surfaceType = MaterialSurfaceType::Opaque;
            $material->alphaClipping = true;

            $calls = $this->calls();
            self::assertCount(2, $calls);
            [$name, $componentId, $slot, $description] = $calls[1];
            self::assertSame('set_material', $name);
            self::assertSame(self::COMPONENT_ID, $componentId);
            self::assertSame(1, $slot);
            self::assertSame('Assets/Glass.material.json', $description['assetPath']);
            self::assertSame('Opaque', $description['document']['material']['surfaceOptions']['surfaceType']);
            self::assertTrue($description['document']['material']['surfaceOptions']['alphaClipping']);
            self::assertSame($material->toAssetDocument(), $description['document'], 'a read material sends every field');
        }

        public function testAssigningMaterialsDescribesEachSlot(): void
        {
            $reference = Material::fromAssetPath('Assets/Brick.material.json');
            $tint = Material::fromColor(Color::red());
            $inline = new Material(surfaceType: MaterialSurfaceType::Transparent);
            $tunedReference = new Material('Assets/Brick.material.json', metallic: 1.0);
            $tunedReference->sortingPriority = 3;

            $renderer = $this->model();
            $renderer->materials = [$reference, $tint, $inline, $tunedReference];

            $calls = $this->calls();
            self::assertCount(1, $calls);
            [$name, $componentId, $slots] = $calls[0];
            self::assertSame('set_materials', $name);
            self::assertSame(self::COMPONENT_ID, $componentId);
            self::assertSame(['assetPath' => 'Assets/Brick.material.json', 'document' => null], $slots[0], 'an asset reference sends its path alone');
            self::assertSame(['assetPath' => '', 'document' => null, 'baseColor' => '#FF0000'], $slots[1], 'a colour alone tints the default material');
            self::assertSame('', $slots[2]['assetPath']);
            self::assertSame($inline->toAssetDocument(), $slots[2]['document'], 'a script material sends every field');
            self::assertSame('Assets/Brick.material.json', $slots[3]['assetPath']);
            self::assertSame(
                ['material' => ['surfaceOptions' => ['sortingPriority' => 3]], 'metallic' => 1.0],
                $slots[3]['document'],
                'a reference sends the fields a script set over the asset',
            );

            $inline->depthTest = MaterialDepthTest::Greater;
            $calls = $this->calls();
            self::assertCount(2, $calls);
            self::assertSame(['set_material', self::COMPONENT_ID, 2], [$calls[1][0], $calls[1][1], $calls[1][2]], 'an assigned material keeps changing its slot');
            self::assertSame('Greater', $calls[1][3]['document']['material']['surfaceOptions']['depthTest']);
        }

        public function testTheFirstMaterialSetsTheFirstSlotOnly(): void
        {
            $renderer = $this->cube();
            $renderer->material = Material::fromAssetPath('Assets/Brick.material.json');
            $renderer->material = null;
            $renderer->setColor(Color::blue());

            self::assertSame([
                ['set_material', self::COMPONENT_ID, 0, ['assetPath' => 'Assets/Brick.material.json', 'document' => null]],
                ['set_material', self::COMPONENT_ID, 0, ['assetPath' => '', 'document' => null]],
                ['set_material', self::COMPONENT_ID, 0, ['assetPath' => '', 'document' => null, 'baseColor' => '#0000FF']],
            ], $this->calls());
        }

        public function testAnUnchangedDefaultSlotGoesBackAsTheDefaultMaterial(): void
        {
            $this->nativeSlots([
                ['assetPath' => '', 'document' => (new Material(baseColor: Color::green()))->toAssetDocument()],
            ]);
            $renderer = $this->model();

            $renderer->materials = $renderer->materials;

            self::assertSame(
                [['set_materials', self::COMPONENT_ID, [['assetPath' => '', 'document' => null, 'baseColor' => '#00FF00']]]],
                $this->calls(),
                'a slot read without a material stays without one, in its colour',
            );
        }

        public function testMaterialsMustBeMaterials(): void
        {
            $this->expectException(InvalidArgumentException::class);

            $this->cube()->materials = ['Assets/Brick.material.json'];
        }

        public function testTwoDRenderersReadAndWritePerRendererTintColor(): void
        {
            $sprite = new SpriteRenderer(new GameObject('Sprite'), self::COMPONENT_ID);
            self::assertSame(Color::white()->toRGBA(), $sprite->color->toRGBA());

            $unlocked = Color::fromRGBA(46, 204, 113, 255);
            $sprite->color = $unlocked;
            self::assertSame($unlocked->toRGBA(), $sprite->color->toRGBA());
            self::assertSame(
                [['set_color', self::COMPONENT_ID, 46, 204, 113, 255]],
                $this->calls(),
            );

            $rectangle = new RectangleRenderer(new GameObject('Portal'), self::COMPONENT_ID + 1);
            $rectangle->color = Color::red();
            self::assertSame(Color::red()->toRGBA(), $rectangle->color->toRGBA());

            $circle = new CircleRenderer(new GameObject('Orb'), self::COMPONENT_ID + 2);
            $circle->color = Color::blue();
            self::assertSame(Color::blue()->toRGBA(), $circle->color->toRGBA());
        }

        /**
         * @param list<array{assetPath: string, document: array<string, mixed>}> $slots
         */
        private function nativeSlots(array $slots): void
        {
            $GLOBALS['lenga_renderer_material_slot_test']['slots'][self::COMPONENT_ID] = $slots;
        }

        /**
         * @return list<list<mixed>>
         */
        private function calls(): array
        {
            return $GLOBALS['lenga_renderer_material_slot_test']['calls'];
        }

        private function model(): ModelRenderer
        {
            return new ModelRenderer(new GameObject('Model'), self::COMPONENT_ID);
        }

        private function cube(): CubeRenderer
        {
            return new CubeRenderer(new GameObject('Cube'), self::COMPONENT_ID);
        }
    }
}
