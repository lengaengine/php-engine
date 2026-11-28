<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\NativeEngine;
use RuntimeException;

/**
 * The base class of every UI element on a {@see Canvas}: {@see Text}, {@see Image}, {@see Button} and {@see Slider}.
 *
 * It holds what all elements share: a name, visibility, draw order, a place in
 * the UI hierarchy and a {@see RectTransform} for layout. Elements are created
 * in the editor or with the `create...()` methods; scripts never construct them.
 *
 * Get an element by assigning it to a typed public field of your Behaviour in
 * the Inspector, or look it up by name:
 *
 * ```php
 * $canvas = Scene::getActive()?->findCanvas('HUD');
 * $panel = $canvas?->findElementByName('Inventory Panel');
 * if ($panel !== null) {
 *     $panel->visible = true;
 * }
 * ```
 *
 * Each property read asks the engine for the element's current state, so keep
 * the element object and read properties when you need them.
 */
abstract class UIElement
{
    /**
     * One object per native element, so listeners added to an element's
     * Signals stay with it however the element is looked up again.
     *
     * @var array<int, UIElement>
     */
    private static array $objects = [];

    /**
     * The element type's name, as the engine reports it.
     */
    protected const string NATIVE_TYPE = '';

    private readonly RectTransform $rectTransformValue;

    /**
     * @internal
     * @param string $nameValue The element name.
     * @param int $elementId The engine ID of the element.
     * @param int|null $canvasId The engine ID of the canvas the element belongs to.
     */
    public function __construct(
        private string $nameValue,
        private readonly int $elementId,
        private readonly ?int $canvasId,
    ) {
        $this->rectTransformValue = new RectTransform($elementId);
    }

    /** The element's name, as shown in the Hierarchy. */
    public string $name {
        get {
            $this->nameValue = (string) ($this->getState()['name'] ?? $this->nameValue);
            return $this->nameValue;
        }
    }

    /**
     * Whether the element is active.
     *
     * Disabling an element also deactivates its children; read
     * {@see UIElement::$activeInHierarchy} to see the combined result.
     * Defaults to `true`.
     */
    public bool $enabled {
        get {
            return (bool) ($this->getState()['enabled'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_element_set_enabled', $this->elementId, $value);
        }
    }

    /**
     * Whether the element is drawn on screen.
     *
     * Use it to show and hide HUD parts such as hearts, prompts and panels. Defaults to `true`.
     */
    public bool $visible {
        get {
            return (bool) ($this->getState()['visible'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('ui_element_set_visible', $this->elementId, $value);
        }
    }

    /**
     * Whether this element and all of its parents are enabled.
     *
     * Defaults to `false`.
     */
    public bool $activeInHierarchy {
        get {
            return (bool) ($this->getState()['activeInHierarchy'] ?? false);
        }
    }

    /**
     * Draw order among sibling elements. Elements with a higher value are drawn on top.
     *
     * Defaults to `0`.
     */
    public int $sortOrder {
        get {
            return (int) ($this->getState()['sortOrder'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('ui_element_set_sort_order', $this->elementId, $value);
        }
    }

    /** The element's position, size, anchors, pivot, scale and rotation. */
    public RectTransform $rectTransform {
        get {
            return $this->rectTransformValue;
        }
    }

    /**
     * Returns the element's runtime ID.
     *
     * Most scripts do not need it; use element references, names, parents and
     * children for normal UI work. The ID is valid while the scene is loaded.
     */
    public function getId(): int
    {
        return $this->elementId;
    }

    /**
     * Returns whether the element has keyboard and gamepad focus.
     *
     * A {@see Button}, {@see Slider} or {@see InputField} is focused while it
     * is the selected element of its canvas. Keyboard and gamepad navigation
     * move the selection, and {@see Canvas::selectElement()} sets it from a
     * script. Text and Image elements always return false.
     */
    public function isFocused(): bool
    {
        return (bool) ($this->getState()['focused'] ?? false);
    }

    /**
     * Returns the Canvas this element belongs to, or null when it has none.
     */
    public function getCanvas(): ?Canvas
    {
        $state = $this->getState();
        $canvasId = $state['canvasId'] ?? $this->canvasId;

        return \is_int($canvasId) ? Canvas::fromNativeLookupData(['id' => $canvasId]) : null;
    }

    /**
     * Returns the parent element, or null when the element sits directly on its canvas.
     */
    public function getParent(): ?self
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|null|false $data */
        $data = NativeEngine::call('ui_element_get_parent', $this->elementId);

        return \is_array($data) ? self::fromNativeLookupData($data) : null;
    }

    /**
     * Moves the element under another element in the UI hierarchy.
     *
     * The element's layout is then calculated inside its new parent's rectangle.
     *
     * @param UIElement|null $parent The new parent, or null to place the element directly on its canvas.
     * @return bool Whether the element was moved.
     */
    public function setParent(?self $parent): bool
    {
        return NativeEngine::call('ui_element_set_parent', $this->elementId, $parent?->getId());
    }

    /**
     * Returns the element's direct children.
     *
     * @return list<UIElement> The children, or an empty list when the element has none.
     */
    public function getChildren(): array
    {
        /** @var list<array{id?: int, name?: string, type?: string, canvasId?: int|null}>|false $data */
        $data = NativeEngine::call('ui_element_get_children', $this->elementId);
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): UIElement => self::fromNativeLookupData($item),
            $data,
        ));
    }

    /**
     * Creates a Text element as a child of this element.
     *
     * The new element is positioned inside this element's rectangle.
     *
     * @param string $name The name of the new element.
     * @throws RuntimeException When this element is not attached to a canvas, or the element cannot be created.
     */
    public function createText(string $name): Text
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createText($name, $this);
    }

    /**
     * Creates a Image element as a child of this element.
     *
     * The new element is positioned inside this element's rectangle.
     *
     * @param string $name The name of the new element.
     * @throws RuntimeException When this element is not attached to a canvas, or the element cannot be created.
     */
    public function createImage(string $name): Image
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createImage($name, $this);
    }

    /**
     * Creates a Button element as a child of this element.
     *
     * The new element is positioned inside this element's rectangle.
     *
     * @param string $name The name of the new element.
     * @throws RuntimeException When this element is not attached to a canvas, or the element cannot be created.
     */
    public function createButton(string $name): Button
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createButton($name, $this);
    }

    /**
     * Creates a Slider element as a child of this element.
     *
     * The new element is positioned inside this element's rectangle.
     *
     * @param string $name The name of the new element.
     * @throws RuntimeException When this element is not attached to a canvas, or the element cannot be created.
     */
    public function createSlider(string $name): Slider
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createSlider($name, $this);
    }

    /**
     * Creates an {@see InputField} as a child of this element.
     *
     * The new element is positioned inside this element's rectangle.
     *
     * @param string $name The name of the new element.
     * @throws RuntimeException When this element is not attached to a canvas, or the element cannot be created.
     */
    public function createInputField(string $name): InputField
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createInputField($name, $this);
    }

    /**
     * Returns the first element with the given name among this element's children and their descendants.
     *
     * The search checks each child, then that child's descendants, before
     * moving to the next child. Returns null when nothing matches.
     *
     * @param string $name The element name. The match is exact and case-sensitive.
     */
    public function findDescendantByName(string $name): ?self
    {
        foreach ($this->getChildren() as $child) {
            if ($child->name === $name) {
                return $child;
            }

            $match = $child->findDescendantByName($name);
            if ($match instanceof self) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Returns the element described by a UI lookup array, such as the `current`
     * and `previous` entries of an {@see \Lenga\Engine\Core\EventBus::UI_SELECTION_CHANGED} payload.
     *
     * The returned object is a {@see Text}, {@see Image}, {@see Button},
     * {@see Slider} or {@see InputField}, matching the array's `type`. Every
     * lookup of the same element returns the same object until the engine
     * destroys the element, so listeners added to its Signals stay.
     *
     * @param array{id?: int, name?: string, type?: string, canvasId?: int|null} $data The lookup array.
     * @throws RuntimeException When the array has no valid `id`, or its `type` is not one of the classes above.
     */
    public static function fromNativeLookupData(array $data): self
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException('Invalid native UI element lookup data received.');
        }

        $name = (string) ($data['name'] ?? 'UIElement');
        $canvasId = isset($data['canvasId']) && \is_int($data['canvasId']) ? $data['canvasId'] : null;
        $type = (string) ($data['type'] ?? '');

        $existing = self::$objects[$id] ?? null;
        if ($existing !== null && $existing::NATIVE_TYPE === $type) {
            return $existing;
        }

        return self::$objects[$id] = match ($type) {
            'Text' => new Text($name, $id, $canvasId),
            'Image' => new Image($name, $id, $canvasId),
            'Button' => new Button($name, $id, $canvasId),
            'Toggle' => new Toggle($name, $id, $canvasId),
            'Slider' => new Slider($name, $id, $canvasId),
            'InputField' => new InputField($name, $id, $canvasId),
            default => throw new RuntimeException("Unsupported native UI element type '{$type}'."),
        };
    }

    /**
     * Hands an event the engine reported for an element to that element's
     * object, if a script has looked the element up.
     *
     * @internal Called by the C++ runtime after each UI frame.
     */
    public static function dispatchNativeEvent(int $elementId, string $event): void
    {
        (self::$objects[$elementId] ?? null)?->receiveNativeEvent($event);
    }

    /**
     * Lets go of the objects of elements the engine destroyed.
     *
     * @internal Called by the C++ runtime before each UI frame.
     *
     * @param list<int> $elementIds
     */
    public static function releaseNativeElements(array $elementIds): void
    {
        foreach ($elementIds as $elementId) {
            unset(self::$objects[$elementId]);
        }
    }

    /**
     * Receives an event the engine reported for this element. Elements with
     * Signals dispatch them here.
     */
    protected function receiveNativeEvent(string $event): void
    {
    }

    /**
     * Finds a UI element in the active scene by the ID the scene file stores for it.
     *
     * Returns null when no element has that ID.
     *
     * @param string $sceneElementId The element's ID in the scene file.
     */
    public static function findBySceneId(string $sceneElementId): ?self
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = NativeEngine::call('ui_element_find_by_scene_id', $sceneElementId);
        if (!\is_array($data)) {
            return null;
        }

        return self::fromNativeLookupData($data);
    }

    /**
     * @internal
     * @param array<string, mixed> $data A UI element reference saved with a Behaviour field.
     */
    public static function fromSerializedReference(array $data): ?self
    {
        $sceneElementId = isset($data['elementId']) && \is_string($data['elementId'])
            ? $data['elementId']
            : '';
        if ($sceneElementId !== '') {
            $element = self::findBySceneId($sceneElementId);
            if ($element !== null) {
                return $element;
            }
        }

        $instanceId = isset($data['instanceId']) && \is_int($data['instanceId'])
            ? $data['instanceId']
            : null;
        if ($instanceId !== null && $instanceId > 0 && isset($data['type']) && \is_string($data['type'])) {
            return self::fromNativeLookupData([
                'id' => $instanceId,
                'name' => isset($data['name']) && \is_string($data['name']) ? $data['name'] : 'UIElement',
                'type' => $data['type'],
                'canvasId' => isset($data['canvasId']) && \is_int($data['canvasId']) ? $data['canvasId'] : null,
            ]);
        }

        return null;
    }

    /**
     * @internal
     * @return array{
     *     id?: int,
     *     type?: string,
     *     name?: string,
     *     enabled?: bool,
     *     visible?: bool,
     *     activeInHierarchy?: bool,
     *     sortOrder?: int,
     *     canvasId?: int|null,
     *     parentId?: int|null,
     *     rectTransform?: array{
     *         anchorMin?: array{x?: float, y?: float},
     *         anchorMax?: array{x?: float, y?: float},
     *         pivot?: array{x?: float, y?: float},
     *         anchoredPosition?: array{x?: float, y?: float},
     *         sizeDelta?: array{x?: float, y?: float},
     *         scale?: array{x?: float, y?: float},
     *         rotation?: float
     *     },
     *     text?: string,
     *     fontSize?: float,
     *     fontPath?: string,
     *     outlineColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     outlineDistance?: array{x?: float, y?: float},
     *     useSdf?: bool,
     *     sdfOutlineWidth?: float,
     *     sdfSoftness?: float,
     *     spritePath?: string,
     *     interactable?: bool,
     *     hovered?: bool,
     *     pressed?: bool,
     *     pressedThisFrame?: bool,
     *     releasedThisFrame?: bool,
     *     clicked?: bool,
     *     color?: array{r?: int, g?: int, b?: int, a?: int},
     *     textColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     backgroundColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     hoverColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     pressedColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     disabledColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     focused?: bool,
     *     minValue?: float,
     *     maxValue?: float,
     *     value?: float,
     *     wholeNumbers?: bool,
     *     showHandle?: bool,
     *     backgroundImagePath?: string,
     *     backgroundSize?: array{x?: float, y?: float},
     *     fillColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     fillImagePath?: string,
     *     fillSize?: array{x?: float, y?: float},
     *     handleColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     handleImagePath?: string,
     *     handleSize?: array{x?: float, y?: float},
     *     placeholder?: string,
     *     contentType?: string,
     *     characterLimit?: int,
     *     caretPosition?: int,
     *     editing?: bool,
     *     restoreOnCancel?: bool,
     *     valueChangedThisFrame?: bool,
     *     submittedThisFrame?: bool,
     *     cancelledThisFrame?: bool,
     *     editEndedThisFrame?: bool,
     *     placeholderColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     caretColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     caretWidth?: float,
     *     caretBlinkRate?: float,
     *     editingColor?: array{r?: int, g?: int, b?: int, a?: int},
     *     outlineWidth?: float
     * }
     */
    protected function getState(): array
    {
        /** @var array{
         *     id?: int,
         *     type?: string,
         *     name?: string,
         *     enabled?: bool,
         *     visible?: bool,
         *     activeInHierarchy?: bool,
         *     sortOrder?: int,
         *     canvasId?: int|null,
         *     parentId?: int|null,
         *     rectTransform?: array{
         *         anchorMin?: array{x?: float, y?: float},
         *         anchorMax?: array{x?: float, y?: float},
         *         pivot?: array{x?: float, y?: float},
         *         anchoredPosition?: array{x?: float, y?: float},
         *         sizeDelta?: array{x?: float, y?: float},
         *         scale?: array{x?: float, y?: float},
         *         rotation?: float
         *     },
         *     text?: string,
         *     fontSize?: float,
         *     fontPath?: string,
         *     outlineColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     outlineDistance?: array{x?: float, y?: float},
         *     useSdf?: bool,
         *     sdfOutlineWidth?: float,
         *     sdfSoftness?: float,
         *     spritePath?: string,
         *     interactable?: bool,
         *     hovered?: bool,
         *     pressed?: bool,
         *     pressedThisFrame?: bool,
         *     releasedThisFrame?: bool,
         *     clicked?: bool,
         *     color?: array{r?: int, g?: int, b?: int, a?: int},
         *     textColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     backgroundColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     hoverColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     pressedColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     disabledColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     focused?: bool,
         *     minValue?: float,
         *     maxValue?: float,
         *     value?: float,
         *     wholeNumbers?: bool,
         *     showHandle?: bool,
         *     backgroundImagePath?: string,
         *     backgroundSize?: array{x?: float, y?: float},
         *     fillColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     fillImagePath?: string,
         *     fillSize?: array{x?: float, y?: float},
         *     handleColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     handleImagePath?: string,
         *     handleSize?: array{x?: float, y?: float},
         *     placeholder?: string,
         *     contentType?: string,
         *     characterLimit?: int,
         *     caretPosition?: int,
         *     editing?: bool,
         *     restoreOnCancel?: bool,
         *     valueChangedThisFrame?: bool,
         *     submittedThisFrame?: bool,
         *     cancelledThisFrame?: bool,
         *     editEndedThisFrame?: bool,
         *     placeholderColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     caretColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     caretWidth?: float,
         *     caretBlinkRate?: float,
         *     editingColor?: array{r?: int, g?: int, b?: int, a?: int},
         *     outlineWidth?: float
         * }|false $state
         */
        $state = NativeEngine::call('ui_element_get_state', $this->elementId);
        if (!\is_array($state)) {
            throw new RuntimeException("Failed to resolve native UI element '{$this->nameValue}'.");
        }

        return $state;
    }
}
