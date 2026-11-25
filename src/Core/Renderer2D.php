<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Internal\ColorBridge;

/**
 * Base class for 2D renderers with a per-renderer tint color multiplied with
 * the assigned material color, matching Unity's SpriteRenderer.color.
 */
abstract class Renderer2D extends Renderer
{
    /**
     * Per-renderer tint color multiplied with the renderer's material color.
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
