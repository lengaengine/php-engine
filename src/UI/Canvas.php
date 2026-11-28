<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\NativeEngine;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\CanvasRenderMode;
use RuntimeException;

/**
 * A container for UI elements such as HUDs, menus and overlays.
 *
 * Canvases are created in the editor or with {@see \Lenga\Engine\SceneManagement\Scene::createCanvas()}.
 * Get one with {@see \Lenga\Engine\SceneManagement\Scene::findCanvas()} or by assigning it
 * to a typed public field of your Behaviour in the Inspector, then find its
 * elements by name:
 *
 * ```php
 * $hud = Scene::getActive()?->findCanvas('HUD');
 * $scoreLabel = $hud?->findTextByName('Score Label');
 * ```
 *
 * Elements are laid out at {@see Canvas::$referenceResolution}, and the canvas
 * scales that layout to fit the screen. The canvas also tracks the selected
 * element used for keyboard and gamepad navigation.
 */
final class Canvas
{
    /**
     * @internal
     * @param string $nameValue The canvas name.
     * @param int $canvasId The engine ID of the canvas.
     */
    public function __construct(
        private string $nameValue,
        private readonly int $canvasId,
    ) {
    }

    /** The canvas name, as shown in the Hierarchy. */
    public string $name {
        get {
            $this->nameValue = (string) ($this->getState()['name'] ?? $this->nameValue);
            return $this->nameValue;
        }
    }

    /** Whether the canvas is active. Defaults to `true`. */
    public bool $enabled {
        get {
            return (bool) ($this->getState()['enabled'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_canvas_set_enabled', $this->canvasId, $value);
        }
    }

    /**
     * Whether the canvas and its elements are drawn. Use it to show and hide whole menus.
     *
     * Defaults to `true`.
     */
    public bool $visible {
        get {
            return (bool) ($this->getState()['visible'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_canvas_set_visible', $this->canvasId, $value);
        }
    }

    /** Draw order among canvases. Canvases with a higher value are drawn on top. Defaults to `0`. */
    public int $sortOrder {
        get {
            return (int) ($this->getState()['sortOrder'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('ui_canvas_set_sort_order', $this->canvasId, $value);
        }
    }

    /**
     * How the canvas is placed: as an overlay on the screen, in front of a camera, or in the world.
     *
     * Defaults to {@see CanvasRenderMode::ScreenSpaceOverlay}.
     */
    public CanvasRenderMode $renderMode {
        get {
            $value = (string) ($this->getState()['renderMode'] ?? CanvasRenderMode::ScreenSpaceOverlay->value);
            return CanvasRenderMode::tryFrom($value) ?? CanvasRenderMode::ScreenSpaceOverlay;
        }

        set(CanvasRenderMode $value) {
            NativeEngine::call('ui_canvas_set_render_mode', $this->canvasId, $value->value);
        }
    }

    /**
     * The screen size, in pixels, the canvas layout is designed for.
     *
     * Element positions and sizes use this resolution. On a different screen
     * size the canvas scales the layout uniformly and centers it. Defaults to `(1280, 720)`.
     */
    public Vector2 $referenceResolution {
        get {
            $value = $this->getState()['referenceResolution'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 1280.0),
                (float) ($value['y'] ?? 720.0),
            );
        }

        set(Vector2 $value) {
            NativeEngine::call('ui_canvas_set_reference_resolution', $this->canvasId, $value->x, $value->y);
        }
    }

    /**
     * Whether the canvas scales to match the screen width (0), the screen height (1), or a blend of both.
     *
     * Values are clamped to 0 to 1. Defaults to `0.0`.
     */
    public float $matchWidthOrHeight {
        get {
            return (float) ($this->getState()['matchWidthOrHeight'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('ui_canvas_set_match_width_or_height', $this->canvasId, $value);
        }
    }

    /**
     * Returns the canvas's runtime ID.
     *
     * Most scripts do not need it; use canvas references and names for normal UI work.
     */
    public function getId(): int
    {
        return $this->canvasId;
    }

    /**
     * Creates a Text element on this canvas.
     *
     * @param string $name The name of the new element.
     * @param UIElement|null $parent The element to create it under, or null to place it directly on the canvas.
     * @throws RuntimeException When the element cannot be created.
     */
    public function createText(string $name, ?UIElement $parent = null): Text
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = NativeEngine::call('ui_canvas_create_text', $this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to create UI Text element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Text) {
            throw new RuntimeException("Native UI element '{$name}' was not returned as Text.");
        }

        return $element;
    }

    /**
     * Creates a Image element on this canvas.
     *
     * @param string $name The name of the new element.
     * @param UIElement|null $parent The element to create it under, or null to place it directly on the canvas.
     * @throws RuntimeException When the element cannot be created.
     */
    public function createImage(string $name, ?UIElement $parent = null): Image
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = NativeEngine::call('ui_canvas_create_image', $this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to create UI Image element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Image) {
            throw new RuntimeException("Native UI element '{$name}' was not returned as Image.");
        }

        return $element;
    }

    /**
     * Creates a Button element on this canvas.
     *
     * @param string $name The name of the new element.
     * @param UIElement|null $parent The element to create it under, or null to place it directly on the canvas.
     * @throws RuntimeException When the element cannot be created.
     */
    public function createButton(string $name, ?UIElement $parent = null): Button
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = NativeEngine::call('ui_canvas_create_button', $this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to create UI Button element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Button) {
            throw new RuntimeException("Native UI element '{$name}' was not returned as Button.");
        }

        return $element;
    }

    /**
     * Creates a Slider element on this canvas.
     *
     * @param string $name The name of the new element.
     * @param UIElement|null $parent The element to create it under, or null to place it directly on the canvas.
     * @throws RuntimeException When the element cannot be created.
     */
    public function createSlider(string $name, ?UIElement $parent = null): Slider
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = NativeEngine::call('ui_canvas_create_slider', $this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new RuntimeException("Failed to create UI Slider element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Slider) {
            throw new RuntimeException("Native UI element '{$name}' was not returned as Slider.");
        }

        return $element;
    }

    /**
     * Returns the elements placed directly on the canvas, without their children.
     *
     * @return list<UIElement> The top-level elements, or an empty list when the canvas has none.
     */
    public function getRootElements(): array
    {
        /** @var list<array{id?: int, name?: string, type?: string, canvasId?: int|null}>|false $data */
        $data = NativeEngine::call('ui_canvas_get_root_elements', $this->canvasId);
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): UIElement => UIElement::fromNativeLookupData($item),
            $data,
        ));
    }

    /**
     * Returns the first element on this canvas with the given name, or null when there is none.
     *
     * The search covers every element on the canvas, including children. Call
     * it once, for example in `start()`, and keep the result.
     *
     * @param string $name The element name, as shown in the Hierarchy. The match is exact and case-sensitive.
     */
    public function findElementByName(string $name): ?UIElement
    {
        foreach ($this->getRootElements() as $element) {
            if ($element->name === $name) {
                return $element;
            }

            $match = $element->findDescendantByName($name);
            if ($match instanceof UIElement) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Returns the first Text on this canvas with the given name, or null when there is none.
     *
     * Works like {@see Canvas::findElementByName()} and returns null when the
     * first element with that name is a different type.
     *
     * @param string $name The element name. The match is exact and case-sensitive.
     */
    public function findTextByName(string $name): ?Text
    {
        $element = $this->findElementByName($name);
        return $element instanceof Text ? $element : null;
    }

    /**
     * Returns the first Button on this canvas with the given name, or null when there is none.
     *
     * Works like {@see Canvas::findElementByName()} and returns null when the
     * first element with that name is a different type.
     *
     * @param string $name The element name. The match is exact and case-sensitive.
     */
    public function findButtonByName(string $name): ?Button
    {
        $element = $this->findElementByName($name);
        return $element instanceof Button ? $element : null;
    }

    /**
     * Returns the first Slider on this canvas with the given name, or null when there is none.
     *
     * Works like {@see Canvas::findElementByName()} and returns null when the
     * first element with that name is a different type.
     *
     * @param string $name The element name. The match is exact and case-sensitive.
     */
    public function findSliderByName(string $name): ?Slider
    {
        $element = $this->findElementByName($name);
        return $element instanceof Slider ? $element : null;
    }

    /**
     * Returns the element that has keyboard and gamepad focus on this canvas, or null when nothing is selected.
     */
    public function getSelectedElement(): ?UIElement
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|null|false $data */
        $data = NativeEngine::call('ui_canvas_get_selected_element', $this->canvasId);

        return \is_array($data) ? UIElement::fromNativeLookupData($data) : null;
    }

    /**
     * Returns the element set as **First Selected** for this canvas in the Inspector, or null when none is set.
     *
     * Select it when a menu opens so keyboard and gamepad players start on the right button:
     *
     * ```php
     * $first = $menu->getFirstSelectedElement();
     * if ($first !== null) {
     *     $menu->selectElement($first);
     * }
     * ```
     */
    public function getFirstSelectedElement(): ?UIElement
    {
        $data = $this->getState()['firstSelectedElement'] ?? null;

        return \is_array($data) ? UIElement::fromNativeLookupData($data) : null;
    }

    /**
     * Makes an element the selected element of this canvas, giving it keyboard and gamepad focus.
     *
     * The previous selection loses focus, and {@see \Lenga\Engine\Core\EventBus::UI_SELECTION_CHANGED}
     * is emitted when the selection changes.
     *
     * @param UIElement $element The element to select.
     * @param bool $viaPointer Whether the selection came from the pointer. It is passed on as `viaPointer` in the event payload.
     * @return bool Whether the canvas and element were found.
     */
    public function selectElement(UIElement $element, bool $viaPointer = false): bool
    {
        return NativeEngine::call('ui_canvas_select_element', $this->canvasId, $element->getId(), $viaPointer);
    }

    /**
     * Removes the selection from this canvas, so no element has keyboard and gamepad focus.
     *
     * @return bool Whether the canvas was found.
     */
    public function clearSelection(): bool
    {
        return NativeEngine::call('ui_canvas_clear_selection', $this->canvasId);
    }

    /**
     * Clicks the selected element, as if the player pressed it. Does nothing when nothing is selected.
     *
     * @return bool Whether the canvas was found.
     */
    public function submitSelected(): bool
    {
        return NativeEngine::call('ui_canvas_submit_selected', $this->canvasId);
    }

    /**
     * Returns the canvas described by a canvas lookup array, such as the `canvas`
     * entry of an {@see \Lenga\Engine\Core\EventBus::UI_SELECTION_CHANGED} payload.
     *
     * @param array{id?: int, name?: string} $data The lookup array.
     * @throws RuntimeException When the array has no valid `id`.
     */
    public static function fromNativeLookupData(array $data): self
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException('Invalid native Canvas lookup data received.');
        }

        return new self(
            (string) ($data['name'] ?? 'Canvas'),
            $id,
        );
    }

    /**
     * Finds a canvas in the active scene by the ID the scene file stores for it.
     *
     * Returns null when no canvas has that ID.
     *
     * @param string $sceneCanvasId The canvas's ID in the scene file.
     */
    public static function findBySceneId(string $sceneCanvasId): ?self
    {
        /** @var array{id?: int, name?: string}|false $data */
        $data = NativeEngine::call('ui_canvas_find_by_scene_id', $sceneCanvasId);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * @internal
     * @param array<string, mixed> $data A canvas reference saved with a Behaviour field.
     */
    public static function fromSerializedReference(array $data): ?self
    {
        $sceneCanvasId = isset($data['canvasId']) && \is_string($data['canvasId'])
            ? $data['canvasId']
            : '';
        if ($sceneCanvasId !== '') {
            $canvas = self::findBySceneId($sceneCanvasId);
            if ($canvas !== null) {
                return $canvas;
            }
        }

        $instanceId = isset($data['instanceId']) && \is_int($data['instanceId'])
            ? $data['instanceId']
            : null;
        if ($instanceId !== null && $instanceId > 0) {
            return self::fromNativeLookupData([
                'id' => $instanceId,
                'name' => isset($data['name']) && \is_string($data['name']) ? $data['name'] : 'Canvas',
            ]);
        }

        return null;
    }

    /**
     * @return array{
     *     id?: int,
     *     name?: string,
     *     enabled?: bool,
     *     visible?: bool,
     *     sortOrder?: int,
     *     renderMode?: string,
     *     referenceResolution?: array{x?: float, y?: float},
     *     matchWidthOrHeight?: float
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     id?: int,
         *     name?: string,
         *     enabled?: bool,
         *     visible?: bool,
         *     sortOrder?: int,
         *     renderMode?: string,
         *     referenceResolution?: array{x?: float, y?: float},
         *     matchWidthOrHeight?: float
         * }|false $state
         */
        $state = NativeEngine::call('ui_canvas_get_state', $this->canvasId);
        if (!\is_array($state)) {
            throw new RuntimeException("Failed to resolve native Canvas '{$this->nameValue}'.");
        }

        return $state;
    }
}
