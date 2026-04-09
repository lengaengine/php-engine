<?php

declare(strict_types=1);

namespace Lenga\Engine\UI;

use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\CanvasRenderMode;

final class Canvas
{
    public function __construct(
        private string $nameValue,
        private readonly int $canvasId,
    ) {
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
            \lenga_internal_ui_canvas_set_enabled($this->canvasId, $value);
        }
    }

    public bool $visible {
        get {
            return (bool) ($this->getState()['visible'] ?? true);
        }

        set(bool $value) {
            \lenga_internal_ui_canvas_set_visible($this->canvasId, $value);
        }
    }

    public int $sortOrder {
        get {
            return (int) ($this->getState()['sortOrder'] ?? 0);
        }

        set(int $value) {
            \lenga_internal_ui_canvas_set_sort_order($this->canvasId, $value);
        }
    }

    public CanvasRenderMode $renderMode {
        get {
            $value = (string) ($this->getState()['renderMode'] ?? CanvasRenderMode::ScreenSpaceOverlay->value);
            return CanvasRenderMode::tryFrom($value) ?? CanvasRenderMode::ScreenSpaceOverlay;
        }

        set(CanvasRenderMode $value) {
            \lenga_internal_ui_canvas_set_render_mode($this->canvasId, $value->value);
        }
    }

    public Vector2 $referenceResolution {
        get {
            $value = $this->getState()['referenceResolution'] ?? [];

            return new Vector2(
                (float) ($value['x'] ?? 1280.0),
                (float) ($value['y'] ?? 720.0),
            );
        }

        set(Vector2 $value) {
            \lenga_internal_ui_canvas_set_reference_resolution($this->canvasId, $value->x, $value->y);
        }
    }

    public float $matchWidthOrHeight {
        get {
            return (float) ($this->getState()['matchWidthOrHeight'] ?? 0.0);
        }

        set(float $value) {
            \lenga_internal_ui_canvas_set_match_width_or_height($this->canvasId, $value);
        }
    }

    public function getId(): int
    {
        return $this->canvasId;
    }

    public function createText(string $name, ?UIElement $parent = null): Text
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = \lenga_internal_ui_canvas_create_text($this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to create UI Text element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Text) {
            throw new \RuntimeException("Native UI element '{$name}' was not returned as Text.");
        }

        return $element;
    }

    public function createImage(string $name, ?UIElement $parent = null): Image
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = \lenga_internal_ui_canvas_create_image($this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to create UI Image element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Image) {
            throw new \RuntimeException("Native UI element '{$name}' was not returned as Image.");
        }

        return $element;
    }

    public function createButton(string $name, ?UIElement $parent = null): Button
    {
        /** @var array{id?: int, name?: string, type?: string, canvasId?: int|null}|false $data */
        $data = \lenga_internal_ui_canvas_create_button($this->canvasId, $name, $parent?->getId());
        if (!\is_array($data)) {
            throw new \RuntimeException("Failed to create UI Button element '{$name}'.");
        }

        $element = UIElement::fromNativeLookupData($data);
        if (!$element instanceof Button) {
            throw new \RuntimeException("Native UI element '{$name}' was not returned as Button.");
        }

        return $element;
    }

    /**
     * @return list<UIElement>
     */
    public function getRootElements(): array
    {
        /** @var list<array{id?: int, name?: string, type?: string, canvasId?: int|null}>|false $data */
        $data = \lenga_internal_ui_canvas_get_root_elements($this->canvasId);
        if (!\is_array($data)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $item): UIElement => UIElement::fromNativeLookupData($item),
            $data,
        ));
    }

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

    public function findTextByName(string $name): ?Text
    {
        $element = $this->findElementByName($name);
        return $element instanceof Text ? $element : null;
    }

    public function findButtonByName(string $name): ?Button
    {
        $element = $this->findElementByName($name);
        return $element instanceof Button ? $element : null;
    }

    /**
     * @param array{id?: int, name?: string} $data
     */
    public static function fromNativeLookupData(array $data): self
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            throw new \RuntimeException('Invalid native Canvas lookup data received.');
        }

        return new self(
            (string) ($data['name'] ?? 'Canvas'),
            $id,
        );
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
        $state = \lenga_internal_ui_canvas_get_state($this->canvasId);
        if (!\is_array($state)) {
            throw new \RuntimeException("Failed to resolve native Canvas '{$this->nameValue}'.");
        }

        return $state;
    }
}
