<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

final class TrailRenderer extends Component
{
    public const string ALIGNMENT_VIEW = 'View';
    public const string ALIGNMENT_TRANSFORM_Z = 'TransformZ';

    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'TrailRenderer');
    }

    public float $time {
        get {
            return (float) ($this->getState()['time'] ?? 5.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_time', $this->componentId, $value);
        }
    }

    public float $minVertexDistance {
        get {
            return (float) ($this->getState()['minVertexDistance'] ?? 0.1);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_min_vertex_distance', $this->componentId, $value);
        }
    }

    public bool $emitting {
        get {
            return (bool) ($this->getState()['emitting'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('trail_renderer_set_emitting', $this->componentId, $value);
        }
    }

    public bool $autodestruct {
        get {
            return (bool) ($this->getState()['autodestruct'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('trail_renderer_set_autodestruct', $this->componentId, $value);
        }
    }

    public float $startWidth {
        get {
            return (float) ($this->getState()['startWidth'] ?? 0.25);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_start_width', $this->componentId, $value);
        }
    }

    public float $endWidth {
        get {
            return (float) ($this->getState()['endWidth'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_end_width', $this->componentId, $value);
        }
    }

    public float $widthMultiplier {
        get {
            return (float) ($this->getState()['widthMultiplier'] ?? 1.0);
        }

        set(float $value) {
            NativeEngine::call('trail_renderer_set_width_multiplier', $this->componentId, $value);
        }
    }

    public string $alignment {
        get {
            return (string) ($this->getState()['alignment'] ?? self::ALIGNMENT_VIEW);
        }

        set(string $value) {
            NativeEngine::call('trail_renderer_set_alignment', $this->componentId, $value);
        }
    }

    public string $sortingLayer {
        get {
            return (string) ($this->getState()['sortingLayer'] ?? 'Default');
        }

        set(string $value) {
            NativeEngine::call('trail_renderer_set_sorting_layer', $this->componentId, $value);
        }
    }

    public int $orderInLayer {
        get {
            return (int) ($this->getState()['orderInLayer'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('trail_renderer_set_order_in_layer', $this->componentId, $value);
        }
    }

    public int $maxPoints {
        get {
            return (int) ($this->getState()['maxPoints'] ?? 512);
        }

        set(int $value) {
            NativeEngine::call('trail_renderer_set_max_points', $this->componentId, $value);
        }
    }

    public int $positionCount {
        get {
            return (int) ($this->getState()['positionCount'] ?? 0);
        }
    }

    public function clear(): void
    {
        NativeEngine::call('trail_renderer_clear', $this->componentId);
    }

    public function addPosition(Vector3 $position): void
    {
        NativeEngine::call(
            'trail_renderer_add_position',
            $this->componentId,
            $position->x,
            $position->y,
            $position->z,
        );
    }

    public function getPosition(int $index): ?Vector3
    {
        $position = NativeEngine::call('trail_renderer_get_position', $this->componentId, $index);
        if (!is_array($position)) {
            return null;
        }

        return new Vector3(
            (float) ($position['x'] ?? 0.0),
            (float) ($position['y'] ?? 0.0),
            (float) ($position['z'] ?? 0.0),
        );
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getStartColor(): array
    {
        return $this->readColor('startColor');
    }

    public function setStartColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        NativeEngine::call(
            'trail_renderer_set_start_color',
            $this->componentId,
            $red,
            $green,
            $blue,
            $alpha,
        );
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    public function getEndColor(): array
    {
        return $this->readColor('endColor');
    }

    public function setEndColor(int $red, int $green, int $blue, int $alpha = 255): void
    {
        NativeEngine::call(
            'trail_renderer_set_end_color',
            $this->componentId,
            $red,
            $green,
            $blue,
            $alpha,
        );
    }

    /**
     * @return array{
     *     time?: float,
     *     minVertexDistance?: float,
     *     emitting?: bool,
     *     autodestruct?: bool,
     *     startWidth?: float,
     *     endWidth?: float,
     *     widthMultiplier?: float,
     *     alignment?: string,
     *     sortingLayer?: string,
     *     orderInLayer?: int,
     *     positionCount?: int,
     *     maxPoints?: int,
     *     enabled?: bool,
     *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     endColor?: array{r?: int, g?: int, b?: int, a?: int}
     * }
     */
    public function getState(): array
    {
        /** @var array{
         *     time?: float,
         *     minVertexDistance?: float,
         *     emitting?: bool,
         *     autodestruct?: bool,
         *     startWidth?: float,
         *     endWidth?: float,
         *     widthMultiplier?: float,
         *     alignment?: string,
         *     sortingLayer?: string,
         *     orderInLayer?: int,
         *     positionCount?: int,
         *     maxPoints?: int,
         *     enabled?: bool,
         *     startColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     endColor?: array{r?: int, g?: int, b?: int, a?: int}
         * } $state
         */
        $state = NativeEngine::call('trail_renderer_get_state', $this->componentId);

        return is_array($state) ? $state : [];
    }

    /**
     * @return array{r:int, g:int, b:int, a:int}
     */
    private function readColor(string $key): array
    {
        $color = $this->getState()[$key] ?? [];
        if (!is_array($color)) {
            $color = [];
        }

        return [
            'r' => (int) ($color['r'] ?? 255),
            'g' => (int) ($color['g'] ?? 255),
            'b' => (int) ($color['b'] ?? 255),
            'a' => (int) ($color['a'] ?? 255),
        ];
    }
}
