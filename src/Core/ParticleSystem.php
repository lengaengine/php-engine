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

    public function stop(bool $clear = false): void
    {
        NativeEngine::call('particle_system_stop', $this->componentId, $clear);
    }

    public function clear(): void
    {
        NativeEngine::call('particle_system_clear', $this->componentId);
    }

    public function emit(int $count): void
    {
        NativeEngine::call('particle_system_emit', $this->componentId, $count);
    }

    public function loadTexture(string $texturePath): bool
    {
        return NativeEngine::call('particle_system_load_texture', $this->componentId, $texturePath);
    }

    /**
     * @return array{
     *     maxParticles?: int,
     *     emissionRate?: float,
     *     lifetime?: float,
     *     startSpeed?: float,
     *     startSize?: float,
     *     endSize?: float,
     *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     endColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     gravity?: array{x?: float, y?: float},
     *     emissionAngle?: float,
     *     spreadAngle?: float,
     *     looping?: bool,
     *     playOnAwake?: bool,
     *     isPlaying?: bool,
     *     aliveParticleCount?: int,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     texturePath?: string
     * }
     */
    public function getState(): array
    {
        /** @var array{
         *     maxParticles?: int,
         *     emissionRate?: float,
         *     lifetime?: float,
         *     startSpeed?: float,
         *     startSize?: float,
         *     endSize?: float,
         *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     endColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     gravity?: array{x?: float, y?: float},
         *     emissionAngle?: float,
         *     spreadAngle?: float,
         *     looping?: bool,
         *     playOnAwake?: bool,
         *     isPlaying?: bool,
         *     aliveParticleCount?: int,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     texturePath?: string
         * } $state
         */
        $state = NativeEngine::call('particle_system_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }

    private function readColor(string $key, Color $fallback): Color
    {
        return ColorBridge::fromState($this->getState()[$key] ?? null, $fallback);
    }
}
