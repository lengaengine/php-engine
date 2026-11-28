<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Enumerations\SpriteDrawMode;

use function is_array;

/**
 * 2D renderer that draws a texture, such as a character, prop or background, at the GameObject's position.
 *
 * Assign the texture in the Inspector or with {@see SpriteRenderer::loadTexture()}. The
 * sprite is drawn at its {@see SpriteRenderer::$width} by {@see SpriteRenderer::$height}
 * size around its {@see SpriteRenderer::$pivot}, then moved, rotated and scaled by the
 * GameObject's {@see Transform}. Tint it with {@see Renderer2D::$color}, mirror it with
 * {@see SpriteRenderer::$flipX} and {@see SpriteRenderer::$flipY}, and control which
 * sprites draw on top with {@see SpriteRenderer::$sortingLayer} and
 * {@see SpriteRenderer::$orderInLayer}. A {@see SpriteAnimation} on the same GameObject
 * changes the texture frame by frame.
 *
 * ```php
 * $renderer = $this->gameObject->getComponent(SpriteRenderer::class);
 * $renderer->flipX = $moveInput < 0.0;
 * ```
 */
final class SpriteRenderer extends Renderer2D
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SpriteRenderer');
    }

    /**
     * Width the sprite is drawn at, before the Transform's scale is applied.
     *
     * Setting it keeps the current height. Use {@see SpriteRenderer::setSize()} to change
     * both at once. Defaults to `0.0` when the size cannot be read.
     */
    public float $width {
        get {
            return (float) ($this->getState()['width'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($value, $this->height);
        }
    }

    /**
     * Height the sprite is drawn at, before the Transform's scale is applied.
     *
     * Setting it keeps the current width. Use {@see SpriteRenderer::setSize()} to change
     * both at once. Defaults to `0.0` when the size cannot be read.
     */
    public float $height {
        get {
            return (float) ($this->getState()['height'] ?? 0.0);
        }

        set(float $value) {
            $this->setSize($this->width, $value);
        }
    }

    /**
     * Name of the sorting layer the sprite draws in.
     *
     * Sorting layers are drawn in the order set in the project settings, so sprites in a
     * later layer appear on top. Assigning an empty string selects `Default`. Defaults to
     * `Default`.
     */
    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('sprite_renderer_set_sorting_layer', $this->componentId, $value);
        }
    }

    /**
     * Draw order within the sorting layer.
     *
     * Sprites with a higher value draw on top of sprites with a lower value in the same
     * {@see SpriteRenderer::$sortingLayer}. Defaults to `0`.
     */
    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('sprite_renderer_set_order_in_layer', $this->componentId, $value);
        }
    }

    /**
     * Path of the texture file the sprite draws. Read-only.
     *
     * Change the texture with {@see SpriteRenderer::loadTexture()}. Empty when no texture
     * file is loaded, for example while a {@see SpriteAnimation} supplies the frames.
     * Defaults to an empty string.
     */
    public string $texturePath {
        get {
            return (string) ($this->getState()['texturePath'] ?? '');
        }
    }

    /**
     * Point of the sprite that sits on the GameObject's position, as a fraction of its size.
     *
     * `(0.5, 0.5)` is the center. Both components are clamped to the `0` to `1` range.
     * Reading it returns a new Vector2, so assign a whole vector to change it. Defaults
     * to `(0.5, 0.5)`.
     */
    public Vector2 $pivot {
        get {
            $pivot = $this->getState()['pivot'] ?? [];
            return new Vector2(
                (float) ($pivot['x'] ?? 0.5),
                (float) ($pivot['y'] ?? 0.5),
            );
        }

        set (Vector2 $value) {
            NativeEngine::call('sprite_renderer_set_pivot', $this->componentId, $value->x, $value->y);
        }
    }

    /**
     * Whether the sprite is mirrored horizontally.
     *
     * Use it to face a character left or right without changing the Transform's scale.
     * Defaults to `false`.
     */
    public bool $flipX {
        get {
            return (bool) ($this->getState()['flipX'] ?? false);
        }

        set (bool $value) {
            NativeEngine::call('sprite_renderer_set_flip_x', $this->componentId, $value);
        }
    }

    /**
     * Whether the sprite is mirrored vertically. Defaults to `false`.
     */
    public bool $flipY {
        get {
            return (bool) ($this->getState()['flipY'] ?? false);
        }

        set (bool $value) {
            NativeEngine::call('sprite_renderer_set_flip_y', $this->componentId, $value);
        }
    }

    /**
     * How the sprite fills its size: stretched (Simple) or repeated every
     * tile size (Tiled).
     */
    public SpriteDrawMode $drawMode {
        get {
            $value = (string) ($this->getState()['drawMode'] ?? SpriteDrawMode::Simple->value);
            return SpriteDrawMode::tryFrom($value) ?? SpriteDrawMode::Simple;
        }

        set(SpriteDrawMode $value) {
            NativeEngine::call('sprite_renderer_set_draw_mode', $this->componentId, $value->value);
        }
    }

    /**
     * One tile's size in world units, before the Transform's scale, used
     * while the draw mode is Tiled.
     */
    public Vector2 $tileSize {
        get {
            $tileSize = $this->getState()['tileSize'] ?? [];
            return new Vector2(
                (float) ($tileSize['x'] ?? 1.0),
                (float) ($tileSize['y'] ?? 1.0),
            );
        }

        set(Vector2 $value) {
            NativeEngine::call('sprite_renderer_set_tile_size', $this->componentId, $value->x, $value->y);
        }
    }

    /**
     * Sets the size the sprite is drawn at, before the Transform's scale is applied.
     *
     * @param float $width New value for {@see SpriteRenderer::$width}.
     * @param float $height New value for {@see SpriteRenderer::$height}.
     */
    public function setSize(float $width, float $height): void
    {
        NativeEngine::call('sprite_renderer_set_size', $this->componentId, $width, $height);
    }

    /**
     * Loads a texture file and draws it as the sprite.
     *
     * On success, {@see SpriteRenderer::$texturePath} changes to the new path and the
     * whole texture is drawn. If the file cannot be loaded, a warning is logged and the
     * sprite draws no texture until another one loads.
     *
     * @param string $texturePath Project-relative path of the image file to load.
     * @return bool `true` if the texture loaded, `false` if the path is empty or the file
     *     could not be loaded.
     */
    public function loadTexture(string $texturePath): bool
    {
        return NativeEngine::call('sprite_renderer_load_texture', $this->componentId, $texturePath);
    }

    /**
     * @return array{
     *     width?: float,
     *     height?: float,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     enabled?: bool,
     *     texturePath?: string,
     *     pivot?: array{x?: float, y?: float},
     *     drawMode?: string,
     *     tileSize?: array{x?: float, y?: float},
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     width?: float,
         *     height?: float,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     enabled?: bool,
         *     texturePath?: string,
         *     pivot?: array{x?: float, y?: float},
         *     drawMode?: string,
         *     tileSize?: array{x?: float, y?: float},
         * } $state
         */
        $state = NativeEngine::call('sprite_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
