<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Internal\ColorBridge;
use function is_array;

final class ParticleSystem extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'ParticleSystem');
    }

    public bool $isPlaying {
        get {
            return (bool) ($this->getState()['isPlaying'] ?? false);
        }
    }

    public int $aliveParticleCount {
        get {
            return (int) ($this->getState()['aliveParticleCount'] ?? 0);
        }
    }

    /**
     * Simulation space: 'Local', 'World', or 'Custom'. Custom simulates in
     * the Transform of the scene object referenced by
     * {@see self::$customSimulationSpaceId} and falls back to Local while
     * that reference cannot be resolved.
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
     * Scene object id whose Transform defines the Custom simulation space.
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
     * Simulation clock: 'Scaled' follows the global time scale, 'Unscaled'
     * keeps simulating through pauses and slow motion.
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
     * How Transform scale affects the effect: 'Hierarchy', 'Local', or
     * 'Shape' (scale only affects spawn positions).
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
     * Emitter velocity source used by distance emission, Lifetime by Emitter
     * Speed, and Inherit Velocity: 'Transform' or 'Rigidbody'.
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
     * Lifecycle action once a stopped system has no live particles: 'None',
     * 'Disable', or 'Destroy'.
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
     * Ring buffer keep-alive behavior: 'Disabled', 'PauseUntilReplaced', or
     * 'LoopUntilReplaced'.
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
     * Normalized (0..1) start of the loop range used by
     * 'LoopUntilReplaced'.
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
     * Normalized (0..1) end of the loop range used by 'LoopUntilReplaced'.
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
     * Wall-clock cost of the most recent simulation step in milliseconds.
     * Aggregate profiling snapshot; there is never per-particle bridge
     * traffic.
     */
    public float $simulationTimeMs {
        get {
            return (float) ($this->getState()['simulationTimeMs'] ?? 0.0);
        }
    }

    /**
     * Render commands submitted by the most recent render pass.
     */
    public int $renderSubmissions {
        get {
            return (int) ($this->getState()['renderSubmissions'] ?? 0);
        }
    }

    public string $dimension {
        get {
            return (string) ($this->getState()['dimension'] ?? '2D');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_dimension', $this->componentId, $value);
        }
    }

    public float $emissionRate {
        get {
            return (float) ($this->getState()['emissionRate'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_emission_rate', $this->componentId, $value);
        }
    }

    public float $lifetime {
        get {
            return (float) ($this->getState()['lifetime'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_lifetime', $this->componentId, $value);
        }
    }

    public float $startSpeed {
        get {
            return (float) ($this->getState()['startSpeed'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_start_speed', $this->componentId, $value);
        }
    }

    public float $startSize {
        get {
            return (float) ($this->getState()['startSize'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_start_size', $this->componentId, $value);
        }
    }

    public float $endSize {
        get {
            return (float) ($this->getState()['endSize'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('particle_system_set_end_size', $this->componentId, $value);
        }
    }

    public string $shapeType {
        get {
            return (string) ($this->getState()['shapeType'] ?? 'Cone2D');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_shape_type', $this->componentId, $value);
        }
    }

    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('particle_system_set_sorting_layer', $this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('particle_system_set_order_in_layer', $this->componentId, $value);
        }
    }

    public string $texturePath {
        get {
            return (string) ($this->getState()['texturePath'] ?? '');
        }
    }

    public string $profilePath {
        get {
            return (string) ($this->getState()['profilePath'] ?? '');
        }
    }

    /**
     * The color particles use when they are spawned.
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
     * Gets the particle start color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getStartColor(): array
    {
        return $this->startColor->toRGBA();
    }

    /**
     * Sets the color particles use when they are spawned.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
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
     * The color particles blend toward over their lifetime.
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
     * Gets the particle end color as byte-channel RGBA values.
     *
     * @return array{r: int, g: int, b: int, a: int}
     */
    public function getEndColor(): array
    {
        return $this->endColor->toRGBA();
    }

    /**
     * Sets the color particles blend toward over their lifetime.
     *
     * @param Color|array{r?: int|float, g?: int|float, b?: int|float, a?: int|float, 0?: int|float, 1?: int|float, 2?: int|float, 3?: int|float}|int $red
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

    public function play(): void
    {
        NativeEngine::call('particle_system_play', $this->componentId);
    }

    public function pause(): void
    {
        NativeEngine::call('particle_system_pause', $this->componentId);
    }

    public function restart(bool $keepSeed = false): void
    {
        NativeEngine::call('particle_system_restart', $this->componentId, $keepSeed);
    }

    public function simulate(float $seconds, bool $restart = false): void
    {
        NativeEngine::call('particle_system_simulate', $this->componentId, $seconds, $restart);
    }

    public function stop(bool $clear = false): void
    {
        NativeEngine::call('particle_system_stop', $this->componentId, $clear);
    }

    public function clear(): void
    {
        NativeEngine::call('particle_system_clear', $this->componentId);
    }

    /**
     * Emits a burst immediately.
     *
     * @param array<string, mixed> $options Reserved for per-burst overrides such as position and velocity.
     */
    public function emit(int $count, array $options = []): void
    {
        unset($options);
        NativeEngine::call('particle_system_emit', $this->componentId, $count);
    }

    /**
     * Fires the system's Manual sub-emitter slots for every live particle.
     *
     * Each Manual slot spawns its configured emit count into its target
     * system at each particle's position, applying the slot's inheritance
     * flags and emit probability.
     *
     * @param int $slotIndex Index of one sub-emitter slot, or -1 to fire
     *                       every Manual slot.
     */
    public function triggerSubEmitter(int $slotIndex = -1): void
    {
        NativeEngine::call('particle_system_trigger_sub_emitter', $this->componentId, $slotIndex);
    }

    public function loadTexture(string $texturePath): bool
    {
        return NativeEngine::call('particle_system_load_texture', $this->componentId, $texturePath);
    }

    public function loadProfile(string $profilePath): bool
    {
        return NativeEngine::call('particle_system_load_profile', $this->componentId, $profilePath);
    }

    /**
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
