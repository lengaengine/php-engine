<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Range;
use function is_array;

/**
 * Draws an imported 3D model, such as a `.glb`, `.gltf` or `.iqm` file, and
 * plays the animations it contains.
 *
 * Assign the model in the Inspector or with {@see ModelRenderer::$modelPath}.
 * When the model file holds no animations, point
 * {@see ModelRenderer::$animationPath} at a file that does. Pick a clip with
 * {@see ModelRenderer::$animationIndex}, then call {@see ModelRenderer::play()}
 * or leave {@see ModelRenderer::$playOnAwake} on.
 *
 * Playback follows scaled game time, so pausing the game freezes the
 * animation.
 */
final class ModelRenderer extends Renderer
{
    /**
     * Wraps the model renderer component with the given id.
     *
     * @internal The engine creates component objects. Scripts get them from
     *     {@see GameObject::addComponent()} or {@see GameObject::getComponent()}.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'ModelRenderer');
    }

    /**
     * The project-relative path of the model asset to draw, such as
     * `Assets/Models/Knight.glb`.
     *
     * Setting it loads the model. Setting an empty string removes the model.
     * Defaults to `''`.
     */
    public string $modelPath {
        get {
            return (string) ($this->getState()['modelPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('model_renderer_load_model', $this->componentId, $value);
        }
    }

    /**
     * The project-relative path of a separate file to read animations from.
     *
     * Leave it empty to use the animations inside the model asset. Setting it
     * reloads the animations. Defaults to `''`.
     */
    public string $animationPath {
        get {
            return (string) ($this->getState()['animationPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('model_renderer_set_animation_path', $this->componentId, $value);
        }
    }

    /**
     * The zero-based index of the animation clip to play.
     *
     * Use {@see ModelRenderer::getAnimationNames()} to see which clip each
     * index holds. Setting it restarts the clip from its first frame. Values
     * past the last clip are clamped to the last clip. Defaults to `0`.
     */
    #[Min(0)]
    public int $animationIndex {
        get {
            return (int) ($this->getState()['animationIndex'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('model_renderer_set_animation_index', $this->componentId, $value);
        }
    }

    /**
     * Whether the animation starts playing as soon as the animations load,
     * such as when the scene starts or a new model or animation file is set.
     *
     * Defaults to `true`.
     */
    public bool $playOnAwake {
        get {
            return (bool) ($this->getState()['playOnAwake'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('model_renderer_set_play_on_awake', $this->componentId, $value);
        }
    }

    /**
     * Whether the animation starts again from the beginning when it reaches
     * its last frame. When it is `false`, the animation stops on the last frame.
     *
     * Defaults to `true`.
     */
    public bool $loop {
        get {
            return (bool) ($this->getState()['loop'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('model_renderer_set_loop', $this->componentId, $value);
        }
    }

    /**
     * The playback speed multiplier. `1.0` plays at normal speed, `2.0` twice
     * as fast and `0.0` holds the current frame.
     *
     * Negative values are treated as `0.0`. Defaults to `1.0`.
     */
    #[Min(0)]
    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('model_renderer_set_speed', $this->componentId, $value);
        }
    }

    /**
     * How many animation frames play per second at a speed of `1.0`.
     *
     * Match it to the frame rate the animation was made at. Values below `1`
     * are treated as `1`. Defaults to `30.0`.
     */
    #[Range(1, 120)]
    public float $playbackFps {
        get {
            return (float) ($this->getState()['playbackFps'] ?? 30.0);
        }

        set(float $value) {
            NativeEngine::call('model_renderer_set_playback_fps', $this->componentId, $value);
        }
    }

    /**
     * Loads a model asset and draws it with this renderer.
     *
     * Setting {@see ModelRenderer::$modelPath} does the same without the result.
     *
     * @param string $modelPath The project-relative path of the model asset.
     * @return bool `true` when the model loaded; `false` when the path is empty or the file could not be read.
     */
    public function loadModel(string $modelPath): bool
    {
        return NativeEngine::call('model_renderer_load_model', $this->componentId, $modelPath);
    }

    /**
     * Loads the animations of this renderer from a separate file.
     *
     * Setting {@see ModelRenderer::$animationPath} does the same without the
     * result. Pass an empty string to go back to the animations inside the
     * model asset.
     *
     * @param string $animationPath The project-relative path of the animation file.
     * @return bool `true` when the animations loaded.
     */
    public function loadAnimations(string $animationPath): bool
    {
        return NativeEngine::call('model_renderer_set_animation_path', $this->componentId, $animationPath);
    }

    /**
     * Returns how many animation clips are loaded, or `0` when there are none.
     */
    public function getAnimationCount(): int
    {
        return (int) ($this->getState()['animationCount'] ?? 0);
    }

    /**
     * Returns the names of the loaded animation clips.
     *
     * A clip without a name in the source file is listed as `Animation 1`,
     * `Animation 2` and so on, counting from 1.
     *
     * @return list<string> The clip names, in index order.
     */
    public function getAnimationNames(): array
    {
        /** @var list<string>|mixed $names */
        $names = $this->getState()['animationNames'] ?? [];
        return is_array($names) ? array_values(array_map(static fn ($value) => (string) $value, $names)) : [];
    }

    /**
     * Returns whether the animation is playing.
     */
    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    /**
     * Starts playing the current animation clip from its current frame.
     *
     * @return bool `true` when playback started; `false` when no valid animation is loaded.
     */
    public function play(): bool
    {
        return NativeEngine::call('model_renderer_play', $this->componentId);
    }

    /**
     * Stops the animation and returns it to its first frame.
     *
     * @return bool `true` when the renderer was found and stopped.
     */
    public function stop(): bool
    {
        return NativeEngine::call('model_renderer_stop', $this->componentId);
    }

    /**
     * @return array{
     *     modelPath?: string,
     *     animationPath?: string,
     *     animationIndex?: int,
     *     animationCount?: int,
     *     animationNames?: list<string>,
     *     playOnAwake?: bool,
     *     loop?: bool,
     *     speed?: float,
     *     playbackFps?: float,
     *     isPlaying?: bool,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     modelPath?: string,
         *     animationPath?: string,
         *     animationIndex?: int,
         *     animationCount?: int,
         *     animationNames?: list<string>,
         *     playOnAwake?: bool,
         *     loop?: bool,
         *     speed?: float,
         *     playbackFps?: float,
         *     isPlaying?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('model_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }
}
