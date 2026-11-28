<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Vector2;

/**
 * Controls where a UI element sits inside its parent rectangle, and its size, scale and rotation.
 *
 * Every {@see UIElement} has one in {@see UIElement::$rectTransform}. Anchors and
 * the pivot use normalized coordinates: `x` runs from 0 (left) to 1 (right)
 * and `y` from 0 (top) to 1 (bottom), so `(1.0, 0.0)` is the top-right corner.
 * Positions and sizes are in canvas pixels at the canvas's
 * {@see Canvas::$referenceResolution}, with positive `y` pointing down.
 *
 * Each property returns a new {@see \Lenga\Engine\Core\Vector2}. To change a value,
 * assign a whole new vector; changing a component of the returned vector has no effect.
 *
 * ```php
 * $rect = $pauseButton->rectTransform;
 * $rect->anchorMin = new Vector2(1.0, 0.0);
 * $rect->anchorMax = new Vector2(1.0, 0.0);
 * $rect->pivot = new Vector2(1.0, 0.0);
 * $rect->anchoredPosition = new Vector2(-24.0, 24.0);
 * $rect->sizeDelta = new Vector2(160.0, 44.0);
 * ```
 */
final class RectTransform
{
    /**
     * @internal
     * @param int $elementId The engine ID of the element this RectTransform belongs to.
     */
    public function __construct(private readonly int $elementId)
    {
    }

    /**
     * Returns the runtime ID of the UI element this RectTransform belongs to.
     *
     * It matches {@see UIElement::getId()} for that element.
     */
    public function getElementId(): int
    {
        return $this->elementId;
    }

    /**
     * The lower normalized anchor in the parent rectangle.
     *
     * When it equals {@see RectTransform::$anchorMax}, the element is anchored
     * to one point and keeps a fixed size. Defaults to `(0, 0)`.
     */
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
            NativeEngine::call('ui_rect_transform_set_anchor_min', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The upper normalized anchor in the parent rectangle.
     *
     * Set it apart from {@see RectTransform::$anchorMin} to make the element
     * stretch with its parent between the two anchors. Defaults to `(0, 0)`.
     */
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
            NativeEngine::call('ui_rect_transform_set_anchor_max', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The point inside the element, in normalized coordinates, that is positioned, rotated and scaled.
     *
     * Defaults to `(0.5, 0.5)`, the center.
     */
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
            NativeEngine::call('ui_rect_transform_set_pivot', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The offset from the anchor to the element's pivot, in canvas pixels.
     *
     * Positive `x` moves the element right and positive `y` moves it down.
     * Defaults to `(0, 0)`.
     */
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
            NativeEngine::call('ui_rect_transform_set_anchored_position', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The element's size in canvas pixels when it is anchored to one point, or the amount added to the stretched size when the anchors are apart.
     *
     * Defaults to `(100, 100)`.
     */
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
            NativeEngine::call('ui_rect_transform_set_size_delta', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The element's scale around its pivot. `(1, 1)` is the normal size.
     *
     * Defaults to `(1, 1)`.
     */
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
            NativeEngine::call('ui_rect_transform_set_scale', $this->elementId, $value->x, $value->y);
        }
    }

    /**
     * The element's rotation around its pivot, in degrees.
     *
     * Defaults to `0`.
     */
    public float $rotation {
        get {
            $state = $this->getState();
            return (float) ($state['rotation'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_rect_transform_set_rotation', $this->elementId, $value);
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
        $state = NativeEngine::call('ui_rect_transform_get_state', $this->elementId);

        return $state;
    }
}
