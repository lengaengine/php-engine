<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class SpriteAnimation extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'SpriteAnimation');
    }

    public string $clipPath {
        get {
            return (string) ($this->getState()['clipPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_sprite_animation_set_clip_path($this->componentId, $value);
        }
    }

    public string $controllerPath {
        get {
            return (string) ($this->getState()['controllerPath'] ?? '');
        }

        set(string $value) {
            \lenga_internal_sprite_animation_set_controller_path($this->componentId, $value);
        }
    }

    public string $state {
        get {
            return (string) ($this->getState()['state'] ?? '');
        }

        set(string $value) {
            \lenga_internal_sprite_animation_set_state_name($this->componentId, $value);
        }
    }

    public bool $playOnAwake {
        get {
            return (bool) ($this->getState()['playOnAwake'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_sprite_animation_set_play_on_awake($this->componentId, $value);
        }
    }

    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 1.0);
        }

        set(float $value) {
            \lenga_internal_sprite_animation_set_speed($this->componentId, $value);
        }
    }

    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    public function play(): bool
    {
        return \lenga_internal_sprite_animation_play($this->componentId);
    }

    public function stop(): bool
    {
        return \lenga_internal_sprite_animation_stop($this->componentId);
    }

    public function getBool(string $parameterName): bool
    {
        return \lenga_internal_sprite_animation_get_bool($this->componentId, $parameterName);
    }

    public function setBool(string $parameterName, bool $value): bool
    {
        return \lenga_internal_sprite_animation_set_bool($this->componentId, $parameterName, $value);
    }

    public function getInt(string $parameterName): int
    {
        return \lenga_internal_sprite_animation_get_int($this->componentId, $parameterName);
    }

    public function setInt(string $parameterName, int $value): bool
    {
        return \lenga_internal_sprite_animation_set_int($this->componentId, $parameterName, $value);
    }

    public function getFloat(string $parameterName): float
    {
        return \lenga_internal_sprite_animation_get_float($this->componentId, $parameterName);
    }

    public function setFloat(string $parameterName, float $value): bool
    {
        return \lenga_internal_sprite_animation_set_float($this->componentId, $parameterName, $value);
    }

    public function setTrigger(string $parameterName): bool
    {
        return \lenga_internal_sprite_animation_set_trigger($this->componentId, $parameterName);
    }

    public function resetTrigger(string $parameterName): bool
    {
        return \lenga_internal_sprite_animation_reset_trigger($this->componentId, $parameterName);
    }

    /**
     * @return array{
     *     clipPath?: string,
     *     controllerPath?: string,
     *     state?: string,
     *     playOnAwake?: bool,
     *     speed?: float,
     *     isPlaying?: bool,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     clipPath?: string,
         *     controllerPath?: string,
         *     state?: string,
         *     playOnAwake?: bool,
         *     speed?: float,
         *     isPlaying?: bool,
         *     enabled?: bool
         * } $state
         */
        $state = \lenga_internal_sprite_animation_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
