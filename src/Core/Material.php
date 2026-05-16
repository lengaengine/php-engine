<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Closure;
use InvalidArgumentException;
use Stringable;
use function in_array;
use function is_array;
use function is_string;
use function trim;

/**
 * Describes the surface appearance assigned to a Renderer.
 *
 * A Material may reference a project asset through {@see assetPath}, or it may
 * be an inline runtime material value created directly from script.
 */
final class Material implements Stringable
{
    public const string SHADER_LIT = 'Lit';
    public const string SHADER_UNLIT = 'Unlit';
    public const string RENDERING_OPAQUE = 'Opaque';
    public const string RENDERING_CUTOUT = 'Cutout';
    public const string RENDERING_FADE = 'Fade';
    public const string RENDERING_TRANSPARENT = 'Transparent';
    public const string UV0 = 'UV0';
    public const string UV1 = 'UV1';

    /**
     * Project-relative material asset path.
     */
    public ?string $assetPath {
        get => $this->assetPathValue;

        set(?string $value) {
            $this->assetPathValue = self::normalizeNullablePath($value);
            $this->notifyChanged();
        }
    }

    /**
     * Material base color.
     */
    public Color $baseColor {
        get => $this->baseColorValue;

        set(Color $value) {
            $this->baseColorValue = $value;
            $this->baseColorCustomized = true;
            $this->notifyChanged();
        }
    }

    /**
     * Alias for the material's base color.
     */
    public Color $color {
        get => $this->baseColor;

        set(Color $value) {
            $this->baseColor = $value;
        }
    }

    private ?string $assetPathValue = null;
    private Color $baseColorValue;
    private bool $baseColorCustomized = false;
    private ?Closure $changeListener = null;

    /**
     * Creates a material.
     */
    public function __construct(
        ?string $assetPath = null,
        public string $name = 'Material',
        public string $shader = self::SHADER_LIT,
        public string $renderingMode = self::RENDERING_OPAQUE,
        public ?string $shaderAssetPath = null,
        Color $baseColor = new Color(1.0, 1.0, 1.0, 1.0),
        public ?string $albedo = null,
        public float $metallic = 0.0,
        public float $smoothness = 0.5,
        public ?string $normalMap = null,
        public ?string $heightMap = null,
        public ?string $occlusion = null,
        public float $emission = 0.0,
        public ?string $detailMask = null,
        public Vector2 $tiling = new Vector2(1.0, 1.0),
        public Vector2 $offset = new Vector2(0.0, 0.0),
        public ?string $detailAlbedo = null,
        public ?string $detailNormalMap = null,
        public Vector2 $secondaryTiling = new Vector2(1.0, 1.0),
        public Vector2 $secondaryOffset = new Vector2(0.0, 0.0),
        public string $secondaryUvSet = self::UV0,
    ) {
        $this->assetPathValue = self::normalizeNullablePath($assetPath);
        $this->baseColorValue = $baseColor;
        $this->baseColorCustomized = $baseColor->toRGBA() !== Color::white()->toRGBA();
        $this->shaderAssetPath = self::normalizeNullablePath($shaderAssetPath);
        $this->albedo = self::normalizeNullablePath($albedo);
        $this->normalMap = self::normalizeNullablePath($normalMap);
        $this->heightMap = self::normalizeNullablePath($heightMap);
        $this->occlusion = self::normalizeNullablePath($occlusion);
        $this->detailMask = self::normalizeNullablePath($detailMask);
        $this->detailAlbedo = self::normalizeNullablePath($detailAlbedo);
        $this->detailNormalMap = self::normalizeNullablePath($detailNormalMap);
        $this->shader = self::validateOneOf($shader, [self::SHADER_LIT, self::SHADER_UNLIT], 'shader');
        $this->renderingMode = self::validateOneOf(
            $renderingMode,
            [
                self::RENDERING_OPAQUE,
                self::RENDERING_CUTOUT,
                self::RENDERING_FADE,
                self::RENDERING_TRANSPARENT,
            ],
            'rendering mode',
        );
        $this->secondaryUvSet = self::validateOneOf($secondaryUvSet, [self::UV0, self::UV1], 'secondary UV set');
    }

    /**
     * Creates a material reference from a project-relative material asset path.
     */
    public static function fromAssetPath(string $assetPath): self
    {
        return new self(assetPath: self::requirePath($assetPath));
    }

    /**
     * Creates an inline material that uses a base color without requiring an asset.
     */
    public static function fromColor(Color $baseColor): self
    {
        return new self(baseColor: $baseColor);
    }

    /**
     * Creates a material from native renderer state.
     *
     * @param array<string, mixed> $state
     */
    public static function fromRendererState(array $state): self
    {
        $material = new self(
            assetPath: self::normalizeNullablePath($state['materialPath'] ?? null),
            baseColor: Color::fromRGBAArray(is_array($state['color'] ?? null) ? $state['color'] : []),
        );

        if (isset($state['materialPaths']) && is_array($state['materialPaths'])) {
            $firstPath = $state['materialPaths'][0] ?? null;
            $material->assetPath = self::normalizeNullablePath($firstPath) ?? $material->assetPath;
        }

        return $material;
    }

    /**
     * Returns a readable material label.
     */
    public function __toString(): string
    {
        return $this->assetPath ?? $this->name;
    }

    /**
     * Registers a listener used by renderer-owned material instances.
     *
     * @internal
     */
    public function bindChangeListener(?Closure $listener): self
    {
        $this->changeListener = $listener;

        return $this;
    }

    /**
     * Returns true when the material carries an explicit renderer tint.
     *
     * @internal
     */
    public function hasCustomBaseColor(): bool
    {
        return $this->baseColorCustomized;
    }

    /**
     * @return array{
     *     assetPath: ?string,
     *     name: string,
     *     shader: string,
     *     renderingMode: string,
     *     shaderAssetPath: ?string,
     *     baseColor: array{r: int, g: int, b: int, a: int},
     *     albedo: ?string,
     *     metallic: float,
     *     smoothness: float,
     *     normalMap: ?string,
     *     heightMap: ?string,
     *     occlusion: ?string,
     *     emission: float,
     *     detailMask: ?string,
     *     tiling: array{x: float, y: float},
     *     offset: array{x: float, y: float},
     *     detailAlbedo: ?string,
     *     detailNormalMap: ?string,
     *     secondaryTiling: array{x: float, y: float},
     *     secondaryOffset: array{x: float, y: float},
     *     secondaryUvSet: string
     * }
     */
    public function __serialize(): array
    {
        return [
            'assetPath' => $this->assetPath,
            'name' => $this->name,
            'shader' => $this->shader,
            'renderingMode' => $this->renderingMode,
            'shaderAssetPath' => $this->shaderAssetPath,
            'baseColor' => $this->baseColor->toRGBA(),
            'albedo' => $this->albedo,
            'metallic' => $this->metallic,
            'smoothness' => $this->smoothness,
            'normalMap' => $this->normalMap,
            'heightMap' => $this->heightMap,
            'occlusion' => $this->occlusion,
            'emission' => $this->emission,
            'detailMask' => $this->detailMask,
            'tiling' => ['x' => $this->tiling->x, 'y' => $this->tiling->y],
            'offset' => ['x' => $this->offset->x, 'y' => $this->offset->y],
            'detailAlbedo' => $this->detailAlbedo,
            'detailNormalMap' => $this->detailNormalMap,
            'secondaryTiling' => ['x' => $this->secondaryTiling->x, 'y' => $this->secondaryTiling->y],
            'secondaryOffset' => ['x' => $this->secondaryOffset->x, 'y' => $this->secondaryOffset->y],
            'secondaryUvSet' => $this->secondaryUvSet,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->assetPath = self::normalizeNullablePath($data['assetPath'] ?? null);
        $this->name = (string) ($data['name'] ?? 'Material');
        $this->shader = self::validateOneOf((string) ($data['shader'] ?? self::SHADER_LIT), [self::SHADER_LIT, self::SHADER_UNLIT], 'shader');
        $this->renderingMode = self::validateOneOf(
            (string) ($data['renderingMode'] ?? self::RENDERING_OPAQUE),
            [
                self::RENDERING_OPAQUE,
                self::RENDERING_CUTOUT,
                self::RENDERING_FADE,
                self::RENDERING_TRANSPARENT,
            ],
            'rendering mode',
        );
        $this->shaderAssetPath = self::normalizeNullablePath($data['shaderAssetPath'] ?? null);
        $this->baseColor = Color::fromRGBAArray(is_array($data['baseColor'] ?? null) ? $data['baseColor'] : []);
        $this->albedo = self::normalizeNullablePath($data['albedo'] ?? null);
        $this->metallic = (float) ($data['metallic'] ?? 0.0);
        $this->smoothness = (float) ($data['smoothness'] ?? 0.5);
        $this->normalMap = self::normalizeNullablePath($data['normalMap'] ?? null);
        $this->heightMap = self::normalizeNullablePath($data['heightMap'] ?? null);
        $this->occlusion = self::normalizeNullablePath($data['occlusion'] ?? null);
        $this->emission = (float) ($data['emission'] ?? 0.0);
        $this->detailMask = self::normalizeNullablePath($data['detailMask'] ?? null);
        $this->tiling = self::readVector2($data['tiling'] ?? null, new Vector2(1.0, 1.0));
        $this->offset = self::readVector2($data['offset'] ?? null, new Vector2(0.0, 0.0));
        $this->detailAlbedo = self::normalizeNullablePath($data['detailAlbedo'] ?? null);
        $this->detailNormalMap = self::normalizeNullablePath($data['detailNormalMap'] ?? null);
        $this->secondaryTiling = self::readVector2($data['secondaryTiling'] ?? null, new Vector2(1.0, 1.0));
        $this->secondaryOffset = self::readVector2($data['secondaryOffset'] ?? null, new Vector2(0.0, 0.0));
        $this->secondaryUvSet = self::validateOneOf((string) ($data['secondaryUvSet'] ?? self::UV0), [self::UV0, self::UV1], 'secondary UV set');
    }

    private static function readVector2(mixed $value, Vector2 $fallback): Vector2
    {
        if (!is_array($value)) {
            return $fallback;
        }

        return new Vector2(
            (float) ($value['x'] ?? $value[0] ?? $fallback->x),
            (float) ($value['y'] ?? $value[1] ?? $fallback->y),
        );
    }

    private static function normalizeNullablePath(mixed $path): ?string
    {
        if (!is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path === '' ? null : $path;
    }

    private static function requirePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            throw new InvalidArgumentException('Material asset path cannot be empty.');
        }

        return $path;
    }

    /**
     * @param list<string> $allowed
     */
    private static function validateOneOf(string $value, array $allowed, string $label): string
    {
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException("Invalid material {$label} '{$value}'.");
        }

        return $value;
    }

    private function notifyChanged(): void
    {
        if ($this->changeListener === null) {
            return;
        }

        ($this->changeListener)($this);
    }
}
