<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Internal\ColorBridge;
use function is_array;

/**
 * Emits and animates many small particles for effects such as dust, sparks, smoke, fire and magic.
 *
 * Each ParticleSystem is one 2D or 3D emitter on a GameObject. A GameObject
 * can have several for layered effects. Author detailed modules, curves,
 * gradients, bursts, sub-emitters and renderer settings in the editor or in
 * a reusable `.particle.json` profile. Use scripts for playback, bursts,
 * profile and texture swaps and simple runtime tuning.
 *
 * When an editor-authored field uses a curve, gradient or random range, the
 * simple properties here read back a single preview value.
 *
 * ```php
 * $dust = $this->gameObject->getComponent(ParticleSystem::class);
 * $dust->clear();
 * $dust->emit(14);
 * ```
 */
final class ParticleSystem extends Component
{
    /**
     * Wraps an existing ParticleSystem component.
     *
     * The engine creates ParticleSystem wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'ParticleSystem');
    }

    /**
     * Whether the system is playing and emitting particles.
     *
     * Turns `true` after {@see ParticleSystem::play()} and `false` after
     * {@see ParticleSystem::stop()}.
     */
    public bool $isPlaying {
        get {
            return (bool) ($this->getState()['isPlaying'] ?? false);
        }
    }

    /**
     * How many particles are alive right now.
     *
     * Use it to wait until an effect has finished, for example after
     * {@see ParticleSystem::stop()}.
     */
    public int $aliveParticleCount {
        get {
            return (int) ($this->getState()['aliveParticleCount'] ?? 0);
        }
    }

    /**
     * Which space particles move in: `Local`, `World` or `Custom`.
     *
     * In `Local` space particles move with the GameObject, which suits an aura
     * that follows a character. In `World` space particles stay where they
     * were emitted, which suits a trail of smoke. `Custom` moves them with the
     * Transform of the object named by
     * {@see ParticleSystem::$customSimulationSpaceId}, and acts as `Local`
     * while that object cannot be found. Defaults to `World`.
     */
    public string $simulationSpace {
        get {
            return (string) ($this->getState()['simulationSpace'] ?? 'World');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_simulation_space', $this->componentId, $value);
        }
    }

    /**
     * The scene object id of the GameObject whose Transform particles move with in `Custom` simulation space.
     *
     * Only used when {@see ParticleSystem::$simulationSpace} is `Custom`. An
     * empty string means no object is set.
     */
    public string $customSimulationSpaceId {
        get {
            return (string) ($this->getState()['customSimulationSpaceId'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_custom_simulation_space', $this->componentId, $value);
        }
    }

    /**
     * Which clock the simulation follows: `Scaled` or `Unscaled`.
     *
     * `Scaled` follows the game's time scale, so particles slow down in slow
     * motion and freeze when the game is paused. `Unscaled` keeps them moving
     * at normal speed, for example for effects in a pause menu. Defaults to
     * `Scaled`.
     */
    public string $deltaTime {
        get {
            return (string) ($this->getState()['deltaTime'] ?? 'Scaled');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_delta_time', $this->componentId, $value);
        }
    }

    /**
     * How the GameObject's scale affects the effect: `Hierarchy`, `Local` or `Shape`.
     *
     * `Hierarchy` uses the scale of the GameObject and its parents. `Local`
     * uses only the GameObject's own scale. `Shape` applies scale only to the
     * positions particles spawn at. Defaults to `Hierarchy`.
     */
    public string $scalingMode {
        get {
            return (string) ($this->getState()['scalingMode'] ?? 'Hierarchy');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_scaling_mode', $this->componentId, $value);
        }
    }

    /**
     * Where the emitter's speed is read from: `Transform` or `Rigidbody`.
     *
     * The emitter's speed drives emission over distance, the Lifetime by
     * Emitter Speed module and the Inherit Velocity module. `Transform`
     * measures how far the GameObject moves each step. `Rigidbody` uses the
     * velocity of its rigid body. Defaults to `Transform`.
     */
    public string $emitterVelocityMode {
        get {
            return (string) ($this->getState()['emitterVelocityMode'] ?? 'Transform');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_emitter_velocity_mode', $this->componentId, $value);
        }
    }

    /**
     * What happens to the GameObject once the system has stopped and its last particle has died: `None`, `Disable` or `Destroy`.
     *
     * Use `Destroy` for one-shot effects such as an explosion spawned from a
     * prefab, so the GameObject removes itself when the effect is over.
     * Defaults to `None`.
     */
    public string $stopAction {
        get {
            return (string) ($this->getState()['stopAction'] ?? 'None');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_stop_action', $this->componentId, $value);
        }
    }

    /**
     * What particles do at the end of their lifetime: `Disabled`, `PauseUntilReplaced` or `LoopUntilReplaced`.
     *
     * `Disabled` lets particles die. The other modes keep them alive until a
     * new particle needs their slot once the system reaches its maximum
     * particle count. `PauseUntilReplaced` holds each particle at its last
     * moment. `LoopUntilReplaced` replays the part of its lifetime between
     * {@see ParticleSystem::$ringBufferLoopRangeMin} and
     * {@see ParticleSystem::$ringBufferLoopRangeMax}. Use them for effects
     * such as footprints or bullet holes that stay until replaced. Defaults to
     * `Disabled`.
     */
    public string $ringBufferMode {
        get {
            return (string) ($this->getState()['ringBufferMode'] ?? 'Disabled');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_ring_buffer_mode', $this->componentId, $value);
        }
    }

    /**
     * The start of the replayed part of a particle's lifetime in `LoopUntilReplaced` mode, from 0 to 1.
     *
     * `0` is the particle's birth and `1` its end of life. Defaults to `0.0`.
     */
    public float $ringBufferLoopRangeMin {
        get {
            return (float) ($this->getState()['ringBufferLoopRangeMin'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_ring_buffer_loop_range_min', $this->componentId, $value);
        }
    }

    /**
     * The end of the replayed part of a particle's lifetime in `LoopUntilReplaced` mode, from 0 to 1.
     *
     * `0` is the particle's birth and `1` its end of life. Defaults to `1.0`.
     */
    public float $ringBufferLoopRangeMax {
        get {
            return (float) ($this->getState()['ringBufferLoopRangeMax'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_ring_buffer_loop_range_max', $this->componentId, $value);
        }
    }

    /**
     * How long the system's most recent simulation step took, in milliseconds of real time.
     *
     * Use it with {@see ParticleSystem::$renderSubmissions} to find effects
     * that cost too much. It is one value for the whole system.
     */
    public float $simulationTimeMs {
        get {
            return (float) ($this->getState()['simulationTimeMs'] ?? 0.0);
        }
    }

    /**
     * How many draw commands the system sent in the most recent frame.
     *
     * Fewer is cheaper. Use it to find effects that cost too much.
     */
    public int $renderSubmissions {
        get {
            return (int) ($this->getState()['renderSubmissions'] ?? 0);
        }
    }

    /**
     * Whether the system is a `2D` or a `3D` effect.
     *
     * Defaults to `2D`.
     */
    public string $dimension {
        get {
            return (string) ($this->getState()['dimension'] ?? '2D');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_dimension', $this->componentId, $value);
        }
    }

    /**
     * How many particles the system emits per second while it plays.
     */
    public float $emissionRate {
        get {
            return (float) ($this->getState()['emissionRate'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_emission_rate', $this->componentId, $value);
        }
    }

    /**
     * How long each particle lives, in seconds.
     */
    public float $lifetime {
        get {
            return (float) ($this->getState()['lifetime'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_lifetime', $this->componentId, $value);
        }
    }

    /**
     * How fast particles move when they are emitted, in world units per second.
     */
    public float $startSpeed {
        get {
            return (float) ($this->getState()['startSpeed'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_start_speed', $this->componentId, $value);
        }
    }

    /**
     * The size of each particle when it is emitted, in world units.
     */
    public float $startSize {
        get {
            return (float) ($this->getState()['startSize'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_start_size', $this->componentId, $value);
        }
    }

    /**
     * The size each particle reaches at the end of its life, in world units.
     */
    public float $endSize {
        get {
            return (float) ($this->getState()['endSize'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_end_size', $this->componentId, $value);
        }
    }

    /**
     * The shape particles are emitted from.
     *
     * 2D systems use shapes such as `Point`, `Line`, `Circle`, `Rectangle` and
     * `Cone2D`. 3D systems use shapes such as `Sphere`, `Hemisphere`, `Box`
     * and `Cone3D`. Set the shape's size and angles in the editor.
     */
    public string $shapeType {
        get {
            return (string) ($this->getState()['shapeType'] ?? 'Cone2D');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_shape_type', $this->componentId, $value);
        }
    }

    /**
     * The name of the sorting layer a 2D system is drawn in.
     *
     * Sorting layers decide which 2D renderers are drawn in front of others.
     * Defaults to `Default`.
     */
    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_sorting_layer', $this->componentId, $value);
        }
    }

    /**
     * A 2D system's drawing order within its sorting layer.
     *
     * Higher values are drawn in front of lower values in the same layer.
     * Defaults to `0`.
     */
    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('particle_system_set_order_in_layer', $this->componentId, $value);
        }
    }

    /**
     * The project-relative path of the texture particles are drawn with.
     *
     * Change it with {@see ParticleSystem::loadTexture()}. An empty string
     * means the system has no texture assigned.
     */
    public string $texturePath {
        get {
            return (string) ($this->getState()['texturePath'] ?? '');
        }
    }

    /**
     * The project-relative path of the `.particle.json` profile the system uses.
     *
     * Change it with {@see ParticleSystem::loadProfile()}. An empty string
     * means the system uses its own settings.
     */
    public string $profilePath {
        get {
            return (string) ($this->getState()['profilePath'] ?? '');
        }
    }

    /**
     * The colour particles have when they are emitted.
     *
     * Setting it from a script gives the system a simple constant colour
     * setup in place of authored random colours or gradients.
     */
    public Color $startColor {
        get => $this->readColor('startColor', Color::fromRGBA(255, 216, 96));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call(
                'particle_system_set_start_color',
                $this->componentId,
                $color['r'],
                $color['g'],
                $color['b'],
                $color['a'],
            );
        }
    }

    /**
     * Gets the start colour as red, green, blue and alpha values from 0 to 255.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getStartColor(): array
    {
        return $this->startColor->toRGBA();
    }

    /**
     * Sets the colour particles have when they are emitted.
     *
     * Does the same as assigning {@see ParticleSystem::$startColor}.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A Color, an array of channels, or the red channel from 0 to 255.
     * @param int|null $green The green channel from 0 to 255, when `$red` is a number.
     * @param int|null $blue The blue channel from 0 to 255, when `$red` is a number.
     * @param int $alpha The alpha channel from 0 to 255, when `$red` is a number.
     */
    public function setStartColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call(
            'particle_system_set_start_color',
            $this->componentId,
            $color['r'],
            $color['g'],
            $color['b'],
            $color['a'],
        );
    }

    /**
     * The colour particles blend toward over their lifetime.
     *
     * Give it an alpha of `0` to fade particles out as they die. Setting it
     * from a script gives the system a simple colour setup in place of an
     * authored gradient.
     */
    public Color $endColor {
        get => $this->readColor('endColor', Color::fromRGBA(255, 216, 96, 0));

        set(Color $value) {
            $color = $value->toRGBA();
            NativeEngine::call(
                'particle_system_set_end_color',
                $this->componentId,
                $color['r'],
                $color['g'],
                $color['b'],
                $color['a'],
            );
        }
    }

    /**
     * Gets the end colour as red, green, blue and alpha values from 0 to 255.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getEndColor(): array
    {
        return $this->endColor->toRGBA();
    }

    /**
     * Sets the colour particles blend toward over their lifetime.
     *
     * Does the same as assigning {@see ParticleSystem::$endColor}.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red A Color, an array of channels, or the red channel from 0 to 255.
     * @param int|null $green The green channel from 0 to 255, when `$red` is a number.
     * @param int|null $blue The blue channel from 0 to 255, when `$red` is a number.
     * @param int $alpha The alpha channel from 0 to 255, when `$red` is a number.
     */
    public function setEndColor(Color|array|int $red, ?int $green = null, ?int $blue = null, int $alpha = 255): void
    {
        $color = ColorBridge::toNative($red, $green, $blue, $alpha);
        NativeEngine::call(
            'particle_system_set_end_color',
            $this->componentId,
            $color['r'],
            $color['g'],
            $color['b'],
            $color['a'],
        );
    }

    /**
     * Starts the system emitting, or resumes it after {@see ParticleSystem::pause()}.
     *
     * Does nothing while the component is disabled.
     */
    public function play(): void
    {
        NativeEngine::call('particle_system_play', $this->componentId);
    }

    /**
     * Freezes the system and its living particles where they are.
     *
     * Call {@see ParticleSystem::play()} to continue.
     */
    public function pause(): void
    {
        NativeEngine::call('particle_system_pause', $this->componentId);
    }

    /**
     * Removes every particle and starts the system again from the beginning.
     *
     * @param bool $keepSeed Whether to repeat the same random sequence as the previous run. `false` picks a new one.
     */
    public function restart(bool $keepSeed = false): void
    {
        NativeEngine::call('particle_system_restart', $this->componentId, $keepSeed);
    }

    /**
     * Advances the simulation by a number of seconds at once.
     *
     * Use it to warm an effect up, so a fire is already burning when the
     * level appears. Long times are simulated in steps of at most 1/30 of a
     * second.
     *
     * @param float $seconds How far to advance, in seconds. Negative values count as `0`.
     * @param bool $restart Whether to remove every particle and start from the beginning before advancing.
     */
    public function simulate(float $seconds, bool $restart = false): void
    {
        NativeEngine::call('particle_system_simulate', $this->componentId, $seconds, $restart);
    }

    /**
     * Stops emitting new particles.
     *
     * Living particles finish their lifetime unless `$clear` is `true`. Once
     * the last one dies, {@see ParticleSystem::$stopAction} runs.
     *
     * @param bool $clear Whether to remove living particles immediately.
     */
    public function stop(bool $clear = false): void
    {
        NativeEngine::call('particle_system_stop', $this->componentId, $clear);
    }

    /**
     * Removes every living particle without stopping the system.
     */
    public function clear(): void
    {
        NativeEngine::call('particle_system_clear', $this->componentId);
    }

    /**
     * Emits a number of particles at once.
     *
     * The burst spawns from the system's shape at the GameObject's current
     * position, rotation and scale, so move the GameObject first to place it.
     * Use it for one-off effects such as landing dust or a hit spark. Does
     * nothing while the component is disabled.
     *
     * The burst fills free particle slots first. When the system is at its
     * maximum particle count, new particles replace the living ones with the
     * least lifetime left.
     *
     * @param int $count How many particles to emit.
     * @param array<string, mixed> $options Ignored. Leave it empty.
     */
    public function emit(int $count, array $options = []): void
    {
        unset($options);
        NativeEngine::call('particle_system_emit', $this->componentId, $count);
    }

    /**
     * Lists the collisions particles had with colliders this frame.
     *
     * Events are recorded only while the Collision module's Send Collision
     * Messages option is on, or the system has a Collision sub-emitter slot.
     * Each event is an array with `particleId`, `point`, `normal`, `velocity`
     * (the particle's velocity as it hit), `approachSpeed`, `colliderId` and
     * `colliderName`. The list holds a limited number of events and is
     * rebuilt every frame, so read it once per `update()`.
     *
     * @return list<array{particleId: int, point: array{x: float, y: float, z: float}, normal: array{x: float, y: float, z: float}, velocity: array{x: float, y: float, z: float}, approachSpeed: float, colliderId: string, colliderName: string}>
     */
    public function getCollisionEvents(): array
    {
        $events = NativeEngine::call('particle_system_get_collision_events', $this->componentId);

        return is_array($events) ? $events : [];
    }

    /**
     * Lists the trigger events particles produced this frame.
     *
     * Events are recorded for Trigger module conditions whose action is
     * `Callback`. Each event is an array with `particleId`, `position`,
     * `velocity`, `condition` (`Inside`, `Outside`, `Enter` or `Exit`) and
     * `colliderMask`, whose bits say which colliders in the Trigger module's
     * list were involved. The list holds a limited number of events and is
     * rebuilt every frame, so read it once per `update()`.
     *
     * @return list<array{particleId: int, position: array{x: float, y: float, z: float}, velocity: array{x: float, y: float, z: float}, condition: string, colliderMask: int}>
     */
    public function getTriggerEvents(): array
    {
        $events = NativeEngine::call('particle_system_get_trigger_events', $this->componentId);

        return is_array($events) ? $events : [];
    }

    /**
     * Fires the system's Manual sub-emitter slots once for every living particle.
     *
     * Each Manual slot emits its configured number of particles into its
     * target system at each particle's position, using the slot's inherit
     * settings and emit probability. Slots with other event types, such as
     * Birth or Death, are ignored.
     *
     * @param int $slotIndex The index of one sub-emitter slot, or `-1` to fire every Manual slot.
     */
    public function triggerSubEmitter(int $slotIndex = -1): void
    {
        NativeEngine::call('particle_system_trigger_sub_emitter', $this->componentId, $slotIndex);
    }

    /**
     * Changes the texture particles are drawn with.
     *
     * @param string $texturePath The project-relative path of the texture, for example `Assets/Textures/Spark.png`.
     * @return bool `true` when the texture loaded, `false` when it could not be loaded.
     */
    public function loadTexture(string $texturePath): bool
    {
        return NativeEngine::call('particle_system_load_texture', $this->componentId, $texturePath);
    }

    /**
     * Replaces the system's settings with a reusable `.particle.json` profile.
     *
     * Use it to swap one effect for another on the same emitter, for example
     * to change a torch flame's colour scheme.
     *
     * @param string $profilePath The project-relative path of the profile, for example `Assets/Particles/Sparks.particle.json`.
     * @return bool `true` when the profile loaded, `false` when it could not be loaded.
     */
    public function loadProfile(string $profilePath): bool
    {
        return NativeEngine::call('particle_system_load_profile', $this->componentId, $profilePath);
    }

    /**
     * Gets a snapshot of the system's current settings and playback state.
     *
     * Use it for debug overlays, runtime UI or tools that need a value with no
     * property of its own, such as `maxParticles`, `looping`, `duration` or
     * `renderMode`. `renderMode` is the renderer mode authored in the
     * editor, such as `Billboard`, `StretchedBillboard`,
     * `HorizontalBillboard`, `VerticalBillboard` or `Mesh`. Keys can be
     * missing; the array is empty when the engine reports no state.
     *
     * @return array{
     *     maxParticles?: int,
     *     dimension?: string,
     *     schemaVersion?: int,
     *     emissionRate?: float,
     *     rateOverDistance?: float,
     *     lifetime?: float,
     *     duration?: float,
     *     simulationSpeed?: float,
     *     startSpeed?: float,
     *     startSize?: float,
     *     endSize?: float,
     *     startRotation?: float,
     *     flipRotation?: float,
     *     angularVelocity?: float,
     *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     endColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     gravity?: array{x?: float, y?: float},
     *     emissionAngle?: float,
     *     spreadAngle?: float,
     *     shapeType?: string,
     *     shapeRadius?: float,
     *     shapeRadiusThickness?: float,
     *     shapeArc?: float,
     *     shapeArcMode?: string,
     *     shapeArcSpread?: float,
     *     shapeLength?: float,
     *     shapeEmitFrom?: string,
     *     shapePosition?: array{x?: float, y?: float, z?: float},
     *     shapeRotation?: array{x?: float, y?: float, z?: float},
     *     shapeScale?: array{x?: float, y?: float, z?: float},
     *     shapeBoxSize?: array{x?: float, y?: float, z?: float},
     *     shapeAlignToDirection?: bool,
     *     shapeRandomizeDirection?: float,
     *     shapeSpherizeDirection?: float,
     *     shapeRandomizePosition?: float,
     *     renderMode?: string,
     *     looping?: bool,
     *     playOnAwake?: bool,
     *     isPlaying?: bool,
     *     isPaused?: bool,
     *     aliveParticleCount?: int,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     texturePath?: string,
     *     profilePath?: string
     * }
     */
    public function getState(): array
    {
        /** @var array<string, mixed> $state */
        $state = NativeEngine::call('particle_system_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }

    private function readColor(string $key, Color $fallback): Color
    {
        return ColorBridge::fromState($this->getState()[$key] ?? null, $fallback);
    }
}
