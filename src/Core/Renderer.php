<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;
use Lenga\Engine\Interfaces\RendererInterface;
use Lenga\Engine\Internal\ColorBridge;
use LogicException;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function is_array;
use function is_string;
use function preg_replace;
use function strtolower;

/**
 * Base class for components that draw visible scene geometry.
 *
 * The renderer API owns material assignment while each concrete renderer
 * provides the strategy for drawing a specific kind of geometry.
 */
abstract class Renderer extends Component implements RendererInterface
{
    /**
     * The first material assigned to this renderer, or null when no material is assigned.
     */
    public ?Material $material {
        get {
            $materials = $this->materials;

            return $materials[0] ?? null;
        }

        set(?Material $value) {
            $this->materials = $value === null ? [] : [$value];
        }
    }

    /**
     * The materials assigned to this renderer.
     *
     * @var list<Material>
     */
    public array $materials {
        get {
            $state = $this->readRendererState();
            $materialPaths = $this->getMaterialPathsFromState($state);
            if ($materialPaths !== []) {
                return array_values(array_map(
                    fn (string $materialPath): Material => $this->bindRendererMaterial(Material::fromAssetPath($materialPath)),
                    $materialPaths,
                ));
            }

            if (isset($state['color']) && is_array($state['color'])) {
                return [$this->bindRendererMaterial(Material::fromRendererState($state))];
            }

            return [];
        }

        set(array $value) {
            $materials = [];
            foreach (array_values($value) as $material) {
                if (!$material instanceof Material) {
                    throw new InvalidArgumentException('Renderer materials must be Material instances.');
                }

                $materials[] = $this->bindRendererMaterial($material);
            }

            $this->applyMaterials($materials);
        }
    }

    /**
     * Assigns materials to this renderer.
     *
     * @param list<Material> $materials
     */
    public function setMaterials(array $materials): void
    {
        $this->materials = $materials;
    }

    /**
     * Assigns the primary material to this renderer.
     */
    public function setMaterial(?Material $material): void
    {
        $this->material = $material;
    }

    /**
     * Gets the primary material color as byte-channel RGBA values.
     *
     * @deprecated Use `$renderer->material?->color` or assign a `Material` with the desired base color.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getColor(): array
    {
        $state = $this->readRendererState();
        if (isset($state['color']) && is_array($state['color'])) {
            return Color::fromRGBAArray($state['color'])->toRGBA();
        }

        return ($this->material?->color ?? Color::white())->toRGBA();
    }

    /**
     * Sets the primary material color.
     *
     * @deprecated Assign `$renderer->material = Material::fromColor(...)` instead.
     */
    public function setColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);

        $this->material = Material::fromColor(Color::fromRGBA($color['r'], $color['g'], $color['b'], $color['a']));
    }

    /**
     * Reads the renderer state array from the native runtime.
     *
     * @return array<string, mixed>
     */
    protected function readRendererState(): array
    {
        $state = NativeEngine::call($this->rendererBindingPrefix() . '_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }

    /**
     * @param list<Material> $materials
     */
    private function applyMaterials(array $materials): void
    {
        $materialPaths = [];
        foreach ($materials as $material) {
            if ($material->assetPath !== null && $material->assetPath !== '') {
                $materialPaths[] = $material->assetPath;
            }
        }

        $singleMaterialBinding = $this->rendererBindingPrefix() . '_set_material_path';
        $hasSingleMaterialBinding = NativeEngine::hasFunction($singleMaterialBinding);

        if ($materialPaths === []) {
            if ($hasSingleMaterialBinding) {
                NativeEngine::call($singleMaterialBinding, $this->componentId, '');
            }

            if ($materials !== []) {
                $this->applyMaterialColor($materials[0]);
            }

            return;
        }

        if (count($materialPaths) === 1) {
            if (!$hasSingleMaterialBinding) {
                throw new LogicException($this->type . ' cannot assign material assets because its native renderer material bridge is unavailable.');
            }

            NativeEngine::call($singleMaterialBinding, $this->componentId, $materialPaths[0]);
            if (isset($materials[0]) && $this->shouldApplyMaterialColor($materials[0])) {
                $this->applyMaterialColor($materials[0]);
            }
            return;
        }

        $multiMaterialBinding = $this->rendererBindingPrefix() . '_set_material_paths';
        if (NativeEngine::hasFunction($multiMaterialBinding)) {
            NativeEngine::call($multiMaterialBinding, $this->componentId, $materialPaths);
            if (isset($materials[0]) && $this->shouldApplyMaterialColor($materials[0])) {
                $this->applyMaterialColor($materials[0]);
            }
            return;
        }

        throw new LogicException($this->type . ' currently supports one material through the native renderer bridge.');
    }

    /**
     * @return list<string>
     */
    private function getMaterialPathsFromState(array $state): array
    {
        $materialPaths = $state['materialPaths'] ?? null;
        if (is_array($materialPaths)) {
            $paths = [];
            foreach ($materialPaths as $materialPath) {
                if (is_string($materialPath) && $materialPath !== '') {
                    $paths[] = $materialPath;
                }
            }

            return array_values(array_filter($paths, static fn (string $path): bool => $path !== ''));
        }

        $materialPath = $state['materialPath'] ?? '';
        if (!is_string($materialPath) || $materialPath === '') {
            return [];
        }

        return [$materialPath];
    }

    private function applyMaterialColor(Material $material): void
    {
        $setColorBinding = $this->rendererBindingPrefix() . '_set_color';
        if (!NativeEngine::hasFunction($setColorBinding)) {
            return;
        }

        $color = $material->baseColor->toRGBA();
        NativeEngine::call(
            $setColorBinding,
            $this->componentId,
            $color['r'],
            $color['g'],
            $color['b'],
            $color['a'],
        );
    }

    private function shouldApplyMaterialColor(Material $material): bool
    {
        return $material->assetPath === null || $material->hasCustomBaseColor();
    }

    private function bindRendererMaterial(Material $material): Material
    {
        return $material->bindChangeListener(function (Material $changedMaterial): void {
            $this->applyMaterials([$changedMaterial]);
        });
    }

    private function rendererBindingPrefix(): string
    {
        $prefix = preg_replace('/(?<!^)[A-Z]/', '_$0', $this->type);

        return strtolower(is_string($prefix) ? $prefix : $this->type);
    }
}
