<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

abstract class UIElement
{
    private readonly RectTransform $rectTransformValue;

    public function __construct(
        private string $nameValue,
        private readonly int $elementId,
        private readonly ?int $canvasId,
    ) {
        $this->rectTransformValue = new RectTransform($elementId);
    }

    public string $name {
        get {
            $this->nameValue = (string) ($this->getState()['name'] ?? $this->nameValue);
            return $this->nameValue;
        }
    }

    public bool $enabled {
        get {
            return (bool) ($this->getState()['enabled'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_ui_element_set_enabled($this->elementId, $value);
        }
    }

    public bool $visible {
        get {
            return (bool) ($this->getState()['visible'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_ui_element_set_visible($this->elementId, $value);
        }
    }

    public bool $activeInHierarchy {
        get {
            return (bool) ($this->getState()['activeInHierarchy'] ?? false);
        }
    }

    public int $sortOrder {
        get {
            return (int) ($this->getState()['sortOrder'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_ui_element_set_sort_order($this->elementId, $value);
        }
    }

    public RectTransform $rectTransform {
        get {
            return $this->rectTransformValue;
        }
    }

    public function getId(): int
    {
        return $this->elementId;
    }

    public function isFocused(): bool
    {
        return (bool) ($this->getState()['focused'] ?? false);
    }

    public function getCanvas(): ?Canvas
    {
        $state = $this->getState();
        $canvasId = $state['canvasId'] ?? $this->canvasId;

        return \is_int($canvasId) ? Canvas::fromNativeLookupData(['id' => $canvasId]) : null;
    }

    public function getParent(): ?self
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|null|false $data */
        $data = \lenga_internal_ui_element_get_parent($this->elementId);

        return \is_array($data) ? self::fromNativeLookupData($data) : null;
    }

    public function setParent(?self $parent): bool
    {
        return \lenga_internal_ui_element_set_parent($this->elementId, $parent?->getId());
    }

    /**
     * @return list<UIElement>
     */
    public function getChildren(): array
    {
        /** @var list<array{id?: int, name?: string, type?: string, canvasId?: int|null}>|false $data */
        $data = \lenga_internal_ui_element_get_children($this->elementId);
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): UIElement => self::fromNativeLookupData($item),
            $data,
        ));
    }

    public function createText(string $name): Text
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new \RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createText($name, $this);
    }

    public function createImage(string $name): Image
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new \RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createImage($name, $this);
    }

    public function createButton(string $name): Button
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new \RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createButton($name, $this);
    }

    public function createSlider(string $name): Slider
    {
        $canvas = $this->getCanvas();
        if ($canvas === null) {
            throw new \RuntimeException("UI element '{$this->name}' is not attached to a canvas.");
        }

        return $canvas->createSlider($name, $this);
    }

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
     * @param array{id?: int, name?: string, type?: string, canvasId?: int|null} $data
     */
    public static function fromNativeLookupData(array $data): self
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            throw new \RuntimeException('Invalid native UI element lookup data received.');
        }

        $name = (string) ($data['name'] ?? 'UIElement');
        $canvasId = isset($data['canvasId']) && \is_int($data['canvasId']) ? $data['canvasId'] : null;
        $type = (string) ($data['type'] ?? '');

        return match ($type) {
            'Text' => new Text($name, $id, $canvasId),
            'Image' => new Image($name, $id, $canvasId),
            'Button' => new Button($name, $id, $canvasId),
            'Slider' => new Slider($name, $id, $canvasId),
            default => throw new \RuntimeException("Unsupported native UI element type '{$type}'."),
        };
    }

    /**
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
     *     handleSize?: array{x?: float, y?: float}
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
         *     handleSize?: array{x?: float, y?: float}
         * }|false $state
         */
        $state = \lenga_internal_ui_element_get_state($this->elementId);
        if (!\is_array($state)) {
            throw new \RuntimeException("Failed to resolve native UI element '{$this->nameValue}'.");
        }

        return $state;
    }
}
