<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Vector2;

final class RectTransform
{
    public function __construct(private readonly int $elementId)
    {
    }

    public Vector2 $anchorMin {
        get {
            $state = $this->getState();
            $value = $state['anchorMin'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_anchor_min($this->elementId, $value->x, $value->y);
        }
    }

    public Vector2 $anchorMax {
        get {
            $state = $this->getState();
            $value = $state['anchorMax'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_anchor_max($this->elementId, $value->x, $value->y);
        }
    }

    public Vector2 $pivot {
        get {
            $state = $this->getState();
            $value = $state['pivot'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.5),
                (float) ($value['y'] ?? 0.5),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_pivot($this->elementId, $value->x, $value->y);
        }
    }

    public Vector2 $anchoredPosition {
        get {
            $state = $this->getState();
            $value = $state['anchoredPosition'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 0.0),
                (float) ($value['y'] ?? 0.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_anchored_position($this->elementId, $value->x, $value->y);
        }
    }

    public Vector2 $sizeDelta {
        get {
            $state = $this->getState();
            $value = $state['sizeDelta'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 100.0),
                (float) ($value['y'] ?? 100.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_size_delta($this->elementId, $value->x, $value->y);
        }
    }

    public Vector2 $scale {
        get {
            $state = $this->getState();
            $value = $state['scale'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 1.0),
                (float) ($value['y'] ?? 1.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_rect_transform_set_scale($this->elementId, $value->x, $value->y);
        }
    }

    public float $rotation {
        get {
            $state = $this->getState();
            return (float) ($state['rotation'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_ui_rect_transform_set_rotation($this->elementId, $value);
        }
    }

    /**
     * @return array{
     *     anchorMin?: array{x?: float, y?: float},
     *     anchorMax?: array{x?: float, y?: float},
     *     pivot?: array{x?: float, y?: float},
     *     anchoredPosition?: array{x?: float, y?: float},
     *     sizeDelta?: array{x?: float, y?: float},
     *     scale?: array{x?: float, y?: float},
     *     rotation?: float
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     anchorMin?: array{x?: float, y?: float},
         *     anchorMax?: array{x?: float, y?: float},
         *     pivot?: array{x?: float, y?: float},
         *     anchoredPosition?: array{x?: float, y?: float},
         *     sizeDelta?: array{x?: float, y?: float},
         *     scale?: array{x?: float, y?: float},
         *     rotation?: float
         * } $state
         */
        $state = \lenga_internal_ui_rect_transform_get_state($this->elementId);

        return $state;
    }
}
