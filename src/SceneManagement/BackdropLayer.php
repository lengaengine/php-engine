<?php

declare(strict_types=1);

namespace Lenga\Engine\SceneManagement;

use Lenga\Engine\Core\Vector2;

final class BackdropLayer
{
    public function __construct(private readonly int $indexValue)
    {
    }

    public int $index {
        get {
            return $this->indexValue;
        }
    }

    public string $imagePath {
        get {
            return (string) ($this->getState()['imagePath'] ?? '');
        }
    }

    public string $space {
        get {
            return (string) ($this->getState()['space'] ?? 'Screen');
        }
    }

    public string $depthPreset {
        get {
            return (string) ($this->getState()['depthPreset'] ?? 'Custom');
        }
    }

    public Vector2 $offset {
        get {
            $state = $this->getState()['offset'] ?? [];

            return new Vector2(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_scene_backdrop_layer_set_offset($this->indexValue, $value->x, $value->y);
        }
    }

    public Vector2 $repeat {
        get {
            $state = $this->getState()['repeat'] ?? [];

            return new Vector2(
                (float) ($state['x'] ?? 1.0),
                (float) ($state['y'] ?? 1.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_scene_backdrop_layer_set_repeat($this->indexValue, $value->x, $value->y);
        }
    }

    public Vector2 $parallax {
        get {
            $state = $this->getState()['parallax'] ?? [];

            return new Vector2(
                (float) ($state['x'] ?? 0.0),
                (float) ($state['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_scene_backdrop_layer_set_parallax($this->indexValue, $value->x, $value->y);
        }
    }

    public static function fromNativeData(array $data): self
    {
        return new self((int) ($data['index'] ?? 0));
    }

    public function translateOffset(Vector2 $delta): bool
    {
        return \lenga_internal_scene_backdrop_layer_translate_offset($this->indexValue, $delta->x, $delta->y);
    }

    /**
     * @return array{
     *     index?: int,
     *     imagePath?: string,
     *     space?: string,
     *     depthPreset?: string,
     *     repeat?: array{x?: float|int, y?: float|int},
     *     parallax?: array{x?: float|int, y?: float|int},
     *     offset?: array{x?: float|int, y?: float|int}
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     index?: int,
         *     imagePath?: string,
         *     space?: string,
         *     depthPreset?: string,
         *     repeat?: array{x?: float|int, y?: float|int},
         *     parallax?: array{x?: float|int, y?: float|int},
         *     offset?: array{x?: float|int, y?: float|int}
         * } $state
         */
        $state = \lenga_internal_scene_get_backdrop_layer_state($this->indexValue);

        return \is_array($state) ? $state : [];
    }
}
