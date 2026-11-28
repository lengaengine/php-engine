<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Internal\ColorBridge;

/**
 * Base class for 2D renderers, which add a tint colour on top of their material.
 *
 * The tint in {@see Renderer2D::$color} is multiplied with the material's
 * colour, so several renderers can share one material and still have their
 * own colours.
 */
abstract class Renderer2D extends Renderer
{
    /**
     * The tint colour multiplied with this renderer's material colour.
     *
     * White leaves the material's colour unchanged. Lower the alpha to fade
     * the renderer out. Reads as white when the engine reports no tint.
     */
    public Color $color {
        get => ColorBridge::fromState(
            NativeEngine::call('renderer_get_color', $this->componentId),
            Color::white(),
        );

        set(Color $value) {
            $rgba = $value->toRGBA();
            NativeEngine::call(
                'renderer_set_color',
                $this->componentId,
                $rgba['r'],
                $rgba['g'],
                $rgba['b'],
                $rgba['a'],
            );
        }
    }
}
