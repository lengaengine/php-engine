<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use InvalidArgumentException;
use Lenga\Engine\Interfaces\RendererInterface;
use Lenga\Engine\Internal\ColorBridge;
use function array_map;
use function array_values;
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
     * The material of the renderer's first slot, as Unity's Renderer.material.
     *
     * Reading gives the material the slot draws with: its material asset, or
     * the renderer's default material in the renderer's colour when the slot
     * has none. Assigning sets the first slot and leaves the others; null
     * leaves the first slot to the default material, in the colour it has.
     */
    public ?Material $material {
        get {
            $materials = $this->materials;

            return $materials[0] ?? null;
        }

        set(?Material $value) {
            if ($value === null) {
                NativeEngine::call('renderer_set_material', $this->componentId, 0, ['assetPath' => '', 'document' => null]);

                return;
            }

            NativeEngine::call('renderer_set_material', $this->componentId, 0, $value->toRendererSlot());
            $this->bindRendererMaterial($value, 0);
        }
    }

    /**
     * The materials of the renderer's slots, as Unity's Renderer.materials.
     *
     * Reading gives one Material for each slot, with the values the slot
     * draws with: its asset path, '' read as null for a slot without one, and
     * every field. Assigning gives the renderer one slot for each Material,
     * which draws with all of the Material's fields over its asset's; see
     * {@see Material::toRendererSlot()}. Assigning a field of a Material read
     * or assigned here changes its slot.
     *
     * @var list<Material>
     */
    public array $materials {
        get {
            $slots = NativeEngine::call('renderer_get_materials', $this->componentId);
            $materials = [];
            foreach (is_array($slots) ? array_values($slots) : [] as $slot => $description) {
                if (is_array($description)) {
                    $materials[] = $this->bindRendererMaterial(Material::fromRendererSlot($description), $slot);
                }
            }

            return $materials;
        }

        set(array $value) {
            $materials = [];
            foreach (array_values($value) as $material) {
                if (!$material instanceof Material) {
                    throw new InvalidArgumentException('Renderer materials must be Material instances.');
                }

                $materials[] = $material;
            }

            NativeEngine::call(
                'renderer_set_materials',
                $this->componentId,
                array_map(static fn (Material $material): array => $material->toRendererSlot(), $materials),
            );
            foreach ($materials as $slot => $material) {
                $this->bindRendererMaterial($material, $slot);
            }
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

    private function bindRendererMaterial(Material $material, int $slot): Material
    {
        return $material->bindChangeListener(function (Material $changedMaterial) use ($slot): void {
            NativeEngine::call('renderer_set_material', $this->componentId, $slot, $changedMaterial->toRendererSlot());
        });
    }

    private function rendererBindingPrefix(): string
    {
        $prefix = preg_replace('/(?<!^)[A-Z]/', '_$0', $this->type);

        return strtolower(is_string($prefix) ? $prefix : $this->type);
    }
}
