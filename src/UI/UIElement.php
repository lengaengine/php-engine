<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\NativeEngine;
use RuntimeException;

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

    public function __construct(
        private string $nameValue,
        private readonly int $elementId,
        private readonly ?int $canvasId,
    ) {
        $this->rectTransformValue = new RectTransform($elementId);
    }

    /**
     * The element's name, as shown in the Hierarchy.
     */
    public string $name {
        get {
            $this->nameValue = (string) ($this->getState()['name'] ?? $this->nameValue);
            return $this->nameValue;
        }
    }

    /**
     * Whether the element and its children take part in the UI. A disabled element is hidden and cannot be interacted with.
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
     * Whether the element is drawn. A hidden element keeps its place and its children's settings.
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
     * Whether the element and every parent above it are enabled.
     */
    public bool $activeInHierarchy {
        get {
            return (bool) ($this->getState()['activeInHierarchy'] ?? false);
        }
    }

    /**
     * Drawing order among its siblings: higher values draw on top and are hit first by the pointer.
     */
    public int $sortOrder {
        get {
            return (int) ($this->getState()['sortOrder'] ?? 0);
        }

        set(int $value) {
            NativeEngine::call('ui_element_set_sort_order', $this->elementId, $value);
        }
    }

    /**
     * The element's position, size, anchors, pivot, scale and rotation in its parent.
     */
    public RectTransform $rectTransform {
        get {
            return $this->rectTransformValue;
        }
    }

    /**
     * The id the engine knows this element by.
     */
    public function getId(): int
    {
        return $this->elementId;
    }

    /**
     * Whether the element has keyboard or gamepad focus.
     */
    public function isFocused(): bool
    {
        return (bool) ($this->getState()['focused'] ?? false);
    }

    /**
     * The Canvas the element belongs to.
     */
    public function getCanvas(): ?Canvas
    {
        $state = $this->getState();
        $canvasId = $state['canvasId'] ?? $this->canvasId;

        return \is_int($canvasId) ? Canvas::fromNativeLookupData(['id' => $canvasId]) : null;
    }

    /**
     * The element this one is a child of, or null for a root element of its Canvas.
     */
    public function getParent(): ?self
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|null|false $data */
        $data = NativeEngine::call('ui_element_get_parent', $this->elementId);

        return \is_array($data) ? self::fromNativeLookupData($data) : null;
    }

    /**
     * Moves the element under another element of the same Canvas, or to the Canvas's root with null. Returns whether it moved.
     */
    public function setParent(?self $parent): bool
    {
        return NativeEngine::call('ui_element_set_parent', $this->elementId, $parent?->getId());
    }

    /**
     * The element's direct children, in Hierarchy order.
     *
     * @return list<UIElement>
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
     * Creates a Text element as a child of this one.
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
     * Creates an Image element as a child of this one.
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
     * Creates a Button element as a child of this one.
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
     * Creates a Slider element as a child of this one.
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
     * Creates an Input Field as a child of this one.
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
     * The first element under this one, at any depth, with the given name.
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
     * The object that stands for a native UI element. Every lookup of the
     * same element returns the same object, until the engine destroys the
     * element.
     *
     * @param array{id?: int, name?: string, type?: string, canvasId?: int|null} $data
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
     * The element with the given id from the scene file, or null.
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
     * The element a serialized behaviour property refers to, by its scene id
     * or, failing that, its engine id. Null when the element no longer
     * exists.
     *
     * @param array{elementId?: string, instanceId?: int, type?: string, name?: string, canvasId?: int|null} $data
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
     * The element's current values, read from the engine.
     *
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
