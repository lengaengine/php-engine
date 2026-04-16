<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

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
            \lenga_internal_particle_system_set_sorting_layer($this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_particle_system_set_order_in_layer($this->componentId, $value);
        }
    }

    public string $texturePath {
        get {
            return (string) ($this->getState()['texturePath'] ?? '');
        }
    }

    public function play(): void
    {
        \lenga_internal_particle_system_play($this->componentId);
    }

    public function stop(bool $clear = false): void
    {
        \lenga_internal_particle_system_stop($this->componentId, $clear);
    }

    public function clear(): void
    {
        \lenga_internal_particle_system_clear($this->componentId);
    }

    public function emit(int $count): void
    {
        \lenga_internal_particle_system_emit($this->componentId, $count);
    }

    public function loadTexture(string $texturePath): bool
    {
        return \lenga_internal_particle_system_load_texture($this->componentId, $texturePath);
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
        $state = \lenga_internal_particle_system_get_state($this->componentId);

        return \is_array($state) ? $state : [];
    }
}
