<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Range;

final class ModelRenderer extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'ModelRenderer');
    }

    public string $modelPath {
        get {
            return (string) ($this->getState()['modelPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_model_renderer_load_model($this->componentId, $value);
        }
    }

    public string $animationPath {
        get {
            return (string) ($this->getState()['animationPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_model_renderer_set_animation_path($this->componentId, $value);
        }
    }

    public string $materialPath {
        get {
            return (string) ($this->getState()['materialPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_model_renderer_set_material_path($this->componentId, $value);
        }
    }

    #[Min(0)]
    public int $animationIndex {
        get {
            return (int) ($this->getState()['animationIndex'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_model_renderer_set_animation_index($this->componentId, $value);
        }
    }

    public bool $playOnAwake {
        get {
            return (bool) ($this->getState()['playOnAwake'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_model_renderer_set_play_on_awake($this->componentId, $value);
        }
    }

    public bool $loop {
        get {
            return (bool) ($this->getState()['loop'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_model_renderer_set_loop($this->componentId, $value);
        }
    }

    #[Min(0)]
    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_model_renderer_set_speed($this->componentId, $value);
        }
    }

    #[Range(1, 120)]
    public float $playbackFps {
        get {
            return (float) ($this->getState()['playbackFps'] ?? 30.0);
        }

        set(float $value) {
            \lenga_internal_model_renderer_set_playback_fps($this->componentId, $value);
        }
    }

    public function loadModel(string $modelPath): bool
    {
        return \lenga_internal_model_renderer_load_model($this->componentId, $modelPath);
    }

    public function loadAnimations(string $animationPath): bool
    {
        return \lenga_internal_model_renderer_set_animation_path($this->componentId, $animationPath);
    }

    public function getAnimationCount(): int
    {
        return (int) ($this->getState()['animationCount'] ?? 0);
    }

    /**
     * @return list<string>
     */
    public function getAnimationNames(): array
    {
        /** @var list<string>|mixed $names */
        $names = $this->getState()['animationNames'] ?? [];
        return \is_array($names) ? array_values(array_map(static fn ($value) => (string) $value, $names)) : [];
    }

    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    public function play(): bool
    {
        return \lenga_internal_model_renderer_play($this->componentId);
    }

    public function stop(): bool
    {
        return \lenga_internal_model_renderer_stop($this->componentId);
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getColor(): array
    {
        /** @var array{color?: array{r?: int, g?: int, b?: int, a?: int}} $state */
        $state = $this->getState();
        $color = $state['color'] ?? [];

        return [
            'r' => (int) ($color['r'] ?? 255),
            'g' => (int) ($color['g'] ?? 255),
            'b' => (int) ($color['b'] ?? 255),
            'a' => (int) ($color['a'] ?? 255),
        ];
    }

    public function setColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        \lenga_internal_model_renderer_set_color($this->componentId, $red, $green, $blue, $alpha);
    }

    /**
     * @return array{
     *     modelPath?: string,
     *     materialPath?: string,
     *     animationPath?: string,
     *     animationIndex?: int,
     *     animationCount?: int,
     *     animationNames?: list<string>,
     *     playOnAwake?: bool,
     *     loop?: bool,
     *     speed?: float,
     *     playbackFps?: float,
     *     isPlaying?: bool,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     modelPath?: string,
         *     materialPath?: string,
         *     animationPath?: string,
         *     animationIndex?: int,
         *     animationCount?: int,
         *     animationNames?: list<string>,
         *     playOnAwake?: bool,
         *     loop?: bool,
         *     speed?: float,
         *     playbackFps?: float,
         *     isPlaying?: bool,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_model_renderer_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
