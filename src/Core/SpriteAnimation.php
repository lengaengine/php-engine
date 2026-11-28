<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Plays a sprite animation clip, or runs a sprite animation controller, on a GameObject.
 *
 * Add it next to a {@see SpriteRenderer} and assign either a clip or a controller in the
 * Inspector. With a clip, the component plays that clip's frames. With a controller,
 * it moves between states based on parameters your scripts set: bools for lasting
 * conditions such as `isGrounded`, ints for steps such as `comboStep`, floats for
 * values such as `speed`, and triggers for one-shot moves such as `attack`. Parameter
 * names must match the controller exactly.
 *
 * ```php
 * private ?SpriteAnimation $animator = null;
 *
 * public function start(): void
 * {
 *     $this->animator = $this->gameObject->getComponent(SpriteAnimation::class);
 * }
 *
 * public function update(): void
 * {
 *     $speed = abs(Input::getAxis('Horizontal')) * 8.0;
 *     $this->animator?->setFloat('speed', $speed);
 *     $this->animator?->setBool('isRunning', $speed > 6.0);
 *
 *     if (Input::getButtonDown('Attack')) {
 *         $this->animator?->setTrigger('attack');
 *     }
 * }
 * ```
 *
 * The parameter setters return `false` when no controller is assigned, the controller
 * cannot be loaded, or it has no parameter of that name and type.
 */
final class SpriteAnimation extends Component
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
        parent::__construct($gameObject, $componentId, 'SpriteAnimation');
    }

    /**
     * Project-relative path of the animation clip to play when no controller is used.
     *
     * Assigning a clip clears {@see SpriteAnimation::$controllerPath}. If the animation was
     * playing, the new clip starts playing. Empty while a controller is assigned.
     * Defaults to an empty string.
     */
    public string $clipPath {
        get {
            return (string) ($this->getState()['clipPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('sprite_animation_set_clip_path', $this->componentId, $value);
        }
    }

    /**
     * Project-relative path of the sprite animation controller asset.
     *
     * Assigning a controller clears {@see SpriteAnimation::$clipPath} and loads the
     * controller's states and parameters. If the animation was playing, it keeps playing
     * with the controller. Assign an empty string to remove the controller. Defaults to
     * an empty string.
     */
    public string $controllerPath {
        get {
            return (string) ($this->getState()['controllerPath'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('sprite_animation_set_controller_path', $this->componentId, $value);
        }
    }

    /**
     * Name of the controller state that is playing.
     *
     * Assign a state name to switch to that state at once, without waiting for the
     * controller's transitions. The assignment is ignored when no controller is assigned
     * or the controller has no state with that name. Defaults to an empty string.
     */
    public string $state {
        get {
            return (string) ($this->getState()['state'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('sprite_animation_set_state_name', $this->componentId, $value);
        }
    }

    /**
     * Whether the animation starts playing on its own when the component starts.
     *
     * Turn it off to start the animation from a script with {@see SpriteAnimation::play()}.
     * Defaults to `true`.
     */
    public bool $playOnAwake {
        get {
            return (bool) ($this->getState()['playOnAwake'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('sprite_animation_set_play_on_awake', $this->componentId, $value);
        }
    }

    /**
     * Playback speed multiplier.
     *
     * `1.0` plays at the clip's authored frame rate, `2.0` plays twice as fast and `0.0`
     * holds the current frame. Negative values are stored as `0.0`. Defaults to `1.0`.
     */
    public float $speed {
        get {
            return (float) ($this->getState()['speed'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('sprite_animation_set_speed', $this->componentId, $value);
        }
    }

    /**
     * Returns whether the animation is playing.
     */
    public function isPlaying(): bool
    {
        return (bool) ($this->getState()['isPlaying'] ?? false);
    }

    /**
     * Starts or resumes playback from the current frame.
     *
     * @return bool `true` if playback started. `false` if the component is disabled or
     *     there is no clip with frames to play.
     */
    public function play(): bool
    {
        return NativeEngine::call('sprite_animation_play', $this->componentId);
    }

    /**
     * Stops playback.
     *
     * The sprite keeps showing the current frame, and {@see SpriteAnimation::play()}
     * continues from it.
     *
     * @return bool `true` when the component exists and playback stopped.
     */
    public function stop(): bool
    {
        return NativeEngine::call('sprite_animation_stop', $this->componentId);
    }

    /**
     * Returns the value of a bool controller parameter.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @return bool The parameter value, or `false` if the controller has no bool
     *     parameter with that name.
     */
    public function getBool(string $parameterName): bool
    {
        return NativeEngine::call('sprite_animation_get_bool', $this->componentId, $parameterName);
    }

    /**
     * Sets a bool controller parameter, for lasting conditions such as `isGrounded`.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @param bool $value New value.
     * @return bool `true` if the parameter was set, `false` if there is no controller or
     *     no bool parameter with that name.
     */
    public function setBool(string $parameterName, bool $value): bool
    {
        return NativeEngine::call('sprite_animation_set_bool', $this->componentId, $parameterName, $value);
    }

    /**
     * Returns the value of an int controller parameter.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @return int The parameter value, or `0` if the controller has no int parameter
     *     with that name.
     */
    public function getInt(string $parameterName): int
    {
        return NativeEngine::call('sprite_animation_get_int', $this->componentId, $parameterName);
    }

    /**
     * Sets an int controller parameter, for discrete steps such as `comboStep`.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @param int $value New value.
     * @return bool `true` if the parameter was set, `false` if there is no controller or
     *     no int parameter with that name.
     */
    public function setInt(string $parameterName, int $value): bool
    {
        return NativeEngine::call('sprite_animation_set_int', $this->componentId, $parameterName, $value);
    }

    /**
     * Returns the value of a float controller parameter.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @return float The parameter value, or `0.0` if the controller has no float
     *     parameter with that name.
     */
    public function getFloat(string $parameterName): float
    {
        return NativeEngine::call('sprite_animation_get_float', $this->componentId, $parameterName);
    }

    /**
     * Sets a float controller parameter, for values compared against thresholds such as `speed`.
     *
     * @param string $parameterName Name of the parameter in the controller.
     * @param float $value New value.
     * @return bool `true` if the parameter was set, `false` if there is no controller or
     *     no float parameter with that name.
     */
    public function setFloat(string $parameterName, float $value): bool
    {
        return NativeEngine::call('sprite_animation_set_float', $this->componentId, $parameterName, $value);
    }

    /**
     * Fires a trigger parameter for a one-shot transition such as an attack, hit or jump.
     *
     * The trigger stays set until a transition that uses it is taken, which clears it, or
     * until {@see SpriteAnimation::resetTrigger()} is called.
     *
     * @param string $parameterName Name of the trigger in the controller.
     * @return bool `true` if the trigger was set, `false` if there is no controller or no
     *     trigger with that name.
     */
    public function setTrigger(string $parameterName): bool
    {
        return NativeEngine::call('sprite_animation_set_trigger', $this->componentId, $parameterName);
    }

    /**
     * Clears a trigger that was set with {@see SpriteAnimation::setTrigger()} and not used yet.
     *
     * @param string $parameterName Name of the trigger in the controller.
     * @return bool `true` if the trigger was cleared, `false` if there is no controller or
     *     no trigger with that name.
     */
    public function resetTrigger(string $parameterName): bool
    {
        return NativeEngine::call('sprite_animation_reset_trigger', $this->componentId, $parameterName);
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
        $state = NativeEngine::call('sprite_animation_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}
