<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Closure;
use InvalidArgumentException;
use Lenga\Engine\Enumerations\MaterialBlendingMode;
use Lenga\Engine\Enumerations\MaterialDepthTest;
use Lenga\Engine\Enumerations\MaterialDepthWrite;
use Lenga\Engine\Enumerations\MaterialRenderFace;
use Lenga\Engine\Enumerations\MaterialShaderMode;
use Lenga\Engine\Enumerations\MaterialSurfaceType;
use Lenga\Engine\Enumerations\MaterialUvSet;
use Stringable;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function log;
use function trim;

/**
 * Describes the surface appearance assigned to a Renderer.
 *
 * A Material may reference a project asset through {@see assetPath}, or it may
 * be an inline runtime material value created directly from script. Its fields
 * are those of a material file: Unity URP's Surface Options, surface inputs and
 * Emission.
 *
 * The native renderer receives a Material's asset path and base colour. Its
 * other fields describe the material but do not change how a script-made
 * Material draws; assign a material asset to draw with them.
 */
final class Material implements Stringable
{
    /**
     * The material file version {@see toAssetDocument()} writes and
     * {@see fromAssetDocument()} reads.
     */
    public const int ASSET_DOCUMENT_VERSION = 2;

    /**
     * The Alpha Clipping threshold of a new material.
     */
    public const float DEFAULT_ALPHA_CLIP_THRESHOLD = 0.5;

    /**
     * The lowest Sorting Priority the engine draws with.
     */
    public const int MIN_SORTING_PRIORITY = -50;

    /**
     * The highest Sorting Priority the engine draws with.
     */
    public const int MAX_SORTING_PRIORITY = 50;

    /**
     * @deprecated Use {@see MaterialShaderMode::Lit}.
     */
    public const MaterialShaderMode SHADER_LIT = MaterialShaderMode::Lit;

    /**
     * @deprecated Use {@see MaterialShaderMode::Unlit}.
     */
    public const MaterialShaderMode SHADER_UNLIT = MaterialShaderMode::Unlit;

    /**
     * @deprecated Use {@see MaterialSurfaceType::Opaque}.
     */
    public const string RENDERING_OPAQUE = 'Opaque';

    /**
     * @deprecated Use {@see MaterialSurfaceType::Opaque} with {@see $alphaClipping}.
     */
    public const string RENDERING_CUTOUT = 'Cutout';

    /**
     * @deprecated Use {@see MaterialSurfaceType::Transparent} with {@see MaterialBlendingMode::Alpha}.
     */
    public const string RENDERING_FADE = 'Fade';

    /**
     * @deprecated Use {@see MaterialSurfaceType::Transparent} with {@see MaterialBlendingMode::Premultiply}.
     */
    public const string RENDERING_TRANSPARENT = 'Transparent';

    /**
     * @deprecated Use {@see MaterialUvSet::UV0}.
     */
    public const MaterialUvSet UV0 = MaterialUvSet::UV0;

    /**
     * @deprecated Use {@see MaterialUvSet::UV1}.
     */
    public const MaterialUvSet UV1 = MaterialUvSet::UV1;

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

    /**
     * The built-in shader the material draws with.
     *
     * Reading it gives the enum case. Assigning the case's string value, such
     * as 'Unlit', sets that case and is deprecated. Any other string throws an
     * InvalidArgumentException.
     */
    public MaterialShaderMode $shader = MaterialShaderMode::Lit {
        set(MaterialShaderMode|string $value) {
            $this->shader = $value instanceof MaterialShaderMode
                ? $value
                : (MaterialShaderMode::tryFrom($value)
                    ?? throw new InvalidArgumentException("Invalid material shader '{$value}'."));
        }
    }

    /**
     * The UV channel the detail textures sample.
     *
     * Reading it gives the enum case. Assigning the case's string value, such
     * as 'UV1', sets that case and is deprecated. Any other string throws an
     * InvalidArgumentException.
     */
    public MaterialUvSet $secondaryUvSet = MaterialUvSet::UV0 {
        set(MaterialUvSet|string $value) {
            $this->secondaryUvSet = $value instanceof MaterialUvSet
                ? $value
                : (MaterialUvSet::tryFrom($value)
                    ?? throw new InvalidArgumentException("Invalid material secondary UV set '{$value}'."));
        }
    }

    /**
     * The version 1 Rendering Mode the Surface Options correspond to.
     *
     * Assigning a mode sets the Surface Options as the engine converts a
     * version 1 material file: Opaque is Opaque; Cutout is Opaque with Alpha
     * Clipping at 0.5; Fade is Transparent with Alpha blending; Transparent is
     * Transparent with Premultiply blending. Render Face, Sorting Priority,
     * Depth Write and Depth Test keep their values, and so does the Alpha
     * Clipping threshold for every mode but Cutout. An unknown mode throws an
     * InvalidArgumentException.
     *
     * Reading it gives Fade for a Transparent surface with Alpha blending,
     * Transparent for a Transparent surface with any other blending mode,
     * Cutout for an Opaque surface with Alpha Clipping, and Opaque otherwise.
     *
     * @deprecated Use {@see $surfaceType}, {@see $blendingMode}, {@see $alphaClipping} and {@see $alphaClipThreshold}.
     */
    public string $renderingMode {
        get => match (true) {
            $this->surfaceType === MaterialSurfaceType::Transparent
                && $this->blendingMode === MaterialBlendingMode::Alpha => self::RENDERING_FADE,
            $this->surfaceType === MaterialSurfaceType::Transparent => self::RENDERING_TRANSPARENT,
            $this->alphaClipping => self::RENDERING_CUTOUT,
            default => self::RENDERING_OPAQUE,
        };

        set(string $value) {
            $this->applyRenderingMode($value);
        }
    }

    /**
     * The version 1 scalar emission the Emission settings correspond to.
     *
     * Assigning a value converts it once, as the engine converts a version 1
     * material file once when it reads it. A value above 0 turns Emission on,
     * sets the Emission Color to the opaque base colour at log2(value) stops,
     * and sets the Emission Map to the base texture. 0 or less turns Emission
     * off and leaves the Emission Color and Emission Map as they are. Changing
     * the base colour or base texture afterwards does not change the Emission
     * settings.
     *
     * Reading it gives 0 when Emission is off. When Emission is on and the
     * Emission Color's red, green and blue are the base colour's, it gives
     * 2^intensity, the factor version 1 multiplied the base colour by. When
     * they differ, no version 1 factor describes the settings, and it gives
     * 2^intensity all the same: the factor the Emission Color's own colour is
     * multiplied by. The Emission Map is not compared.
     *
     * @deprecated Use {@see $emissionEnabled}, {@see $emissionColor} and {@see $emissionMap}.
     */
    public float $emission {
        get => $this->emissionEnabled ? $this->emissionColor->intensityScale() : 0.0;

        set(float $value) {
            $this->applyEmission($value);
        }
    }

    private ?string $assetPathValue = null;
    private Color $baseColorValue;
    private bool $baseColorCustomized = false;
    private ?Closure $changeListener = null;

    /**
     * Creates a material.
     *
     * The parameters up to secondaryUvSet keep the order they had before the
     * Surface Options and Emission settings were added, which follow them.
     *
     * @param ?string $assetPath Project-relative material asset path, or null for an inline material.
     * @param string $name Display name.
     * @param MaterialShaderMode|string $shader The built-in shader the material draws with. A string is deprecated; see {@see $shader}.
     * @param ?string $renderingMode Deprecated. A version 1 Rendering Mode; when given it sets the Surface Options as {@see $renderingMode} does, over the Surface Options passed.
     * @param ?string $shaderAssetPath Project shader asset that replaces the built-in shader.
     * @param Color $baseColor Base colour the surface is tinted with.
     * @param ?string $albedo Base texture path.
     * @param float $metallic Metallic, 0..1.
     * @param float $smoothness Smoothness, 0..1.
     * @param ?string $normalMap Normal map texture path.
     * @param ?string $heightMap Height map texture path.
     * @param ?string $occlusion Occlusion map texture path.
     * @param ?float $emission Deprecated. A version 1 scalar emission; when given it sets the Emission settings as {@see $emission} does, over the Emission settings passed.
     * @param ?string $detailMask Detail mask texture path.
     * @param Vector2 $tiling Main texture tiling.
     * @param Vector2 $offset Main texture offset.
     * @param ?string $detailAlbedo Detail albedo texture path.
     * @param ?string $detailNormalMap Detail normal map texture path.
     * @param Vector2 $secondaryTiling Detail texture tiling.
     * @param Vector2 $secondaryOffset Detail texture offset.
     * @param MaterialUvSet|string $secondaryUvSet UV channel the detail textures sample. A string is deprecated; see {@see $secondaryUvSet}.
     * @param bool $emissionEnabled Whether the surface emits light.
     * @param HdrColor $emissionColor Emitted colour and its intensity in stops.
     * @param ?string $emissionMap Texture the emission colour is multiplied by.
     * @param MaterialSurfaceType $surfaceType Whether the surface is Opaque or Transparent.
     * @param MaterialBlendingMode $blendingMode How a Transparent surface combines with what is behind it.
     * @param MaterialRenderFace $renderFace Which faces are drawn.
     * @param bool $alphaClipping Whether fragments below the threshold are discarded.
     * @param float $alphaClipThreshold Alpha below which fragments are discarded, 0..1.
     * @param int $sortingPriority Draw order among surfaces of the same type, -50..50.
     * @param MaterialDepthWrite $depthWrite Whether the surface writes depth.
     * @param MaterialDepthTest $depthTest The depth comparison fragments must pass.
     */
    public function __construct(
        ?string $assetPath = null,
        public string $name = 'Material',
        MaterialShaderMode|string $shader = MaterialShaderMode::Lit,
        ?string $renderingMode = null,
        public ?string $shaderAssetPath = null,
        Color $baseColor = new Color(1.0, 1.0, 1.0, 1.0),
        public ?string $albedo = null,
        public float $metallic = 0.0,
        public float $smoothness = 0.5,
        public ?string $normalMap = null,
        public ?string $heightMap = null,
        public ?string $occlusion = null,
        ?float $emission = null,
        public ?string $detailMask = null,
        public Vector2 $tiling = new Vector2(1.0, 1.0),
        public Vector2 $offset = new Vector2(0.0, 0.0),
        public ?string $detailAlbedo = null,
        public ?string $detailNormalMap = null,
        public Vector2 $secondaryTiling = new Vector2(1.0, 1.0),
        public Vector2 $secondaryOffset = new Vector2(0.0, 0.0),
        MaterialUvSet|string $secondaryUvSet = MaterialUvSet::UV0,
        public bool $emissionEnabled = false,
        public HdrColor $emissionColor = new HdrColor(new Color(0.0, 0.0, 0.0, 1.0)),
        public ?string $emissionMap = null,
        public MaterialSurfaceType $surfaceType = MaterialSurfaceType::Opaque,
        public MaterialBlendingMode $blendingMode = MaterialBlendingMode::Alpha,
        public MaterialRenderFace $renderFace = MaterialRenderFace::Front,
        public bool $alphaClipping = false,
        public float $alphaClipThreshold = self::DEFAULT_ALPHA_CLIP_THRESHOLD,
        public int $sortingPriority = 0,
        public MaterialDepthWrite $depthWrite = MaterialDepthWrite::Auto,
        public MaterialDepthTest $depthTest = MaterialDepthTest::LessEqual,
    ) {
        $this->assetPathValue = self::normalizeNullablePath($assetPath);
        $this->shader = $shader;
        $this->secondaryUvSet = $secondaryUvSet;
        $this->baseColorValue = $baseColor;
        $this->baseColorCustomized = $baseColor->toRGBA() !== Color::white()->toRGBA();
        $this->shaderAssetPath = self::normalizeNullablePath($shaderAssetPath);
        $this->albedo = self::normalizeNullablePath($albedo);
        $this->normalMap = self::normalizeNullablePath($normalMap);
        $this->heightMap = self::normalizeNullablePath($heightMap);
        $this->occlusion = self::normalizeNullablePath($occlusion);
        $this->emissionMap = self::normalizeNullablePath($emissionMap);
        $this->detailMask = self::normalizeNullablePath($detailMask);
        $this->detailAlbedo = self::normalizeNullablePath($detailAlbedo);
        $this->detailNormalMap = self::normalizeNullablePath($detailNormalMap);
        if ($renderingMode !== null) {
            $this->applyRenderingMode($renderingMode);
        }
        if ($emission !== null) {
            $this->applyEmission($emission);
        }
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
     * Creates a material from a material file's decoded JSON document.
     *
     * The document has the layout {@see toAssetDocument()} writes, at
     * {@see ASSET_DOCUMENT_VERSION}. Missing or mistyped keys read as their
     * defaults, and a name that is not one of an enum's values reads as that
     * field's default, as the engine reads material files. The engine converts
     * version 1 files when it loads them; this method does not read them.
     *
     * @param array<string, mixed> $document
     * @param ?string $assetPath Project-relative path of the file the document came from.
     *
     * @throws InvalidArgumentException When the document is not a version 2 material document.
     */
    public static function fromAssetDocument(array $document, ?string $assetPath = null): self
    {
        $material = new self(assetPath: $assetPath);
        $material->readAssetDocument($document);

        return $material;
    }

    /**
     * Returns the material as a material file's JSON document.
     *
     * The layout is the one the engine's material files have at
     * {@see ASSET_DOCUMENT_VERSION}: the name, version, shader and Surface
     * Options under "material", and the surface inputs, Emission and texture
     * paths at the root. Texture paths that are not set are empty strings.
     * The asset path is not part of the document.
     *
     * @return array{
     *     material: array{
     *         name: string,
     *         version: int,
     *         shader: string,
     *         surfaceOptions: array{
     *             surfaceType: string,
     *             blendingMode: string,
     *             renderFace: string,
     *             alphaClipping: bool,
     *             alphaClipThreshold: float,
     *             sortingPriority: int,
     *             depthWrite: string,
     *             depthTest: string
     *         }
     *     },
     *     baseColor: string,
     *     metallic: float,
     *     smoothness: float,
     *     emissionEnabled: bool,
     *     emissionColor: array{color: string, intensity: float},
     *     shaderAssetPath: string,
     *     baseTexturePath: string,
     *     normalMapPath: string,
     *     heightMapPath: string,
     *     occlusionMapPath: string,
     *     detailMaskPath: string,
     *     detailAlbedoPath: string,
     *     detailNormalMapPath: string,
     *     emissionMapPath: string,
     *     tiling: array{x: float, y: float},
     *     offset: array{x: float, y: float},
     *     secondaryTiling: array{x: float, y: float},
     *     secondaryOffset: array{x: float, y: float},
     *     secondaryUvSet: string
     * }
     */
    public function toAssetDocument(): array
    {
        return [
            'material' => [
                'name' => $this->name === '' ? 'Material' : $this->name,
                'version' => self::ASSET_DOCUMENT_VERSION,
                'shader' => $this->shader->value,
                'surfaceOptions' => [
                    'surfaceType' => $this->surfaceType->value,
                    'blendingMode' => $this->blendingMode->value,
                    'renderFace' => $this->renderFace->value,
                    'alphaClipping' => $this->alphaClipping,
                    'alphaClipThreshold' => $this->alphaClipThreshold,
                    'sortingPriority' => $this->sortingPriority,
                    'depthWrite' => $this->depthWrite->value,
                    'depthTest' => $this->depthTest->value,
                ],
            ],
            'baseColor' => $this->baseColor->toHex(),
            'metallic' => $this->metallic,
            'smoothness' => $this->smoothness,
            'emissionEnabled' => $this->emissionEnabled,
            'emissionColor' => $this->emissionColor->toAssetValue(),
            'shaderAssetPath' => $this->shaderAssetPath ?? '',
            'baseTexturePath' => $this->albedo ?? '',
            'normalMapPath' => $this->normalMap ?? '',
            'heightMapPath' => $this->heightMap ?? '',
            'occlusionMapPath' => $this->occlusion ?? '',
            'detailMaskPath' => $this->detailMask ?? '',
            'detailAlbedoPath' => $this->detailAlbedo ?? '',
            'detailNormalMapPath' => $this->detailNormalMap ?? '',
            'emissionMapPath' => $this->emissionMap ?? '',
            'tiling' => self::writeVector2($this->tiling),
            'offset' => self::writeVector2($this->offset),
            'secondaryTiling' => self::writeVector2($this->secondaryTiling),
            'secondaryOffset' => self::writeVector2($this->secondaryOffset),
            'secondaryUvSet' => $this->secondaryUvSet->value,
        ];
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
     * Serializes the material as its asset path and its material file document.
     *
     * @return array{assetPath: ?string, document: array<string, mixed>}
     */
    public function __serialize(): array
    {
        return [
            'assetPath' => $this->assetPath,
            'document' => $this->toAssetDocument(),
        ];
    }

    /**
     * Restores the material from {@see __serialize()} data.
     *
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->assetPathValue = self::normalizeNullablePath($data['assetPath'] ?? null);
        $this->readAssetDocument(is_array($data['document'] ?? null) ? $data['document'] : []);
    }

    /**
     * Sets every field but the asset path from the document, reading missing
     * or mistyped keys as their defaults. It assigns every field, so it also
     * initialises an unserialized material, whose constructor does not run.
     *
     * @param array<string, mixed> $document
     */
    private function readAssetDocument(array $document): void
    {
        $header = is_array($document['material'] ?? null) ? $document['material'] : [];
        if (($header['version'] ?? null) !== self::ASSET_DOCUMENT_VERSION) {
            throw new InvalidArgumentException(
                'Material documents must be version ' . self::ASSET_DOCUMENT_VERSION
                . '. The engine converts version 1 material files when it loads them.',
            );
        }

        $surface = is_array($header['surfaceOptions'] ?? null) ? $header['surfaceOptions'] : [];

        $this->name = is_string($header['name'] ?? null) ? $header['name'] : 'Material';
        $this->shader = MaterialShaderMode::tryFrom(self::readString($header, 'shader')) ?? MaterialShaderMode::Lit;
        $this->surfaceType = MaterialSurfaceType::tryFrom(self::readString($surface, 'surfaceType')) ?? MaterialSurfaceType::Opaque;
        $this->blendingMode = MaterialBlendingMode::tryFrom(self::readString($surface, 'blendingMode')) ?? MaterialBlendingMode::Alpha;
        $this->renderFace = MaterialRenderFace::tryFrom(self::readString($surface, 'renderFace')) ?? MaterialRenderFace::Front;
        $this->alphaClipping = is_bool($surface['alphaClipping'] ?? null) ? $surface['alphaClipping'] : false;
        $this->alphaClipThreshold = self::readFloat($surface, 'alphaClipThreshold', self::DEFAULT_ALPHA_CLIP_THRESHOLD);
        $this->sortingPriority = is_int($surface['sortingPriority'] ?? null) ? $surface['sortingPriority'] : 0;
        $this->depthWrite = MaterialDepthWrite::tryFrom(self::readString($surface, 'depthWrite')) ?? MaterialDepthWrite::Auto;
        $this->depthTest = MaterialDepthTest::tryFrom(self::readString($surface, 'depthTest')) ?? MaterialDepthTest::LessEqual;

        $baseColor = Color::tryFromHex(self::readString($document, 'baseColor')) ?? Color::white();
        $this->baseColorValue = $baseColor;
        $this->baseColorCustomized = $baseColor->toRGBA() !== Color::white()->toRGBA();
        $this->metallic = self::readFloat($document, 'metallic', 0.0);
        $this->smoothness = self::readFloat($document, 'smoothness', 0.5);
        $this->emissionEnabled = is_bool($document['emissionEnabled'] ?? null) ? $document['emissionEnabled'] : false;
        $this->emissionColor = HdrColor::fromAssetValue(
            $document['emissionColor'] ?? null,
            new HdrColor(Color::black()),
        );

        $this->shaderAssetPath = self::normalizeNullablePath($document['shaderAssetPath'] ?? null);
        $this->albedo = self::normalizeNullablePath($document['baseTexturePath'] ?? null);
        $this->normalMap = self::normalizeNullablePath($document['normalMapPath'] ?? null);
        $this->heightMap = self::normalizeNullablePath($document['heightMapPath'] ?? null);
        $this->occlusion = self::normalizeNullablePath($document['occlusionMapPath'] ?? null);
        $this->detailMask = self::normalizeNullablePath($document['detailMaskPath'] ?? null);
        $this->detailAlbedo = self::normalizeNullablePath($document['detailAlbedoPath'] ?? null);
        $this->detailNormalMap = self::normalizeNullablePath($document['detailNormalMapPath'] ?? null);
        $this->emissionMap = self::normalizeNullablePath($document['emissionMapPath'] ?? null);

        $this->tiling = self::readVector2($document['tiling'] ?? null, new Vector2(1.0, 1.0));
        $this->offset = self::readVector2($document['offset'] ?? null, new Vector2(0.0, 0.0));
        $this->secondaryTiling = self::readVector2($document['secondaryTiling'] ?? null, new Vector2(1.0, 1.0));
        $this->secondaryOffset = self::readVector2($document['secondaryOffset'] ?? null, new Vector2(0.0, 0.0));
        $this->secondaryUvSet = MaterialUvSet::tryFrom(self::readString($document, 'secondaryUvSet')) ?? MaterialUvSet::UV0;
    }

    /**
     * Sets the Surface Options a version 1 Rendering Mode stands for, as the
     * engine converts a version 1 material file.
     */
    private function applyRenderingMode(string $renderingMode): void
    {
        [$this->surfaceType, $this->blendingMode, $this->alphaClipping] = match ($renderingMode) {
            self::RENDERING_OPAQUE => [MaterialSurfaceType::Opaque, MaterialBlendingMode::Alpha, false],
            self::RENDERING_CUTOUT => [MaterialSurfaceType::Opaque, MaterialBlendingMode::Alpha, true],
            self::RENDERING_FADE => [MaterialSurfaceType::Transparent, MaterialBlendingMode::Alpha, false],
            self::RENDERING_TRANSPARENT => [MaterialSurfaceType::Transparent, MaterialBlendingMode::Premultiply, false],
            default => throw new InvalidArgumentException("Invalid material rendering mode '{$renderingMode}'."),
        };

        if ($renderingMode === self::RENDERING_CUTOUT) {
            $this->alphaClipThreshold = self::DEFAULT_ALPHA_CLIP_THRESHOLD;
        }
    }

    /**
     * Sets the Emission settings a version 1 scalar emission stands for, as
     * the engine converts a version 1 material file.
     */
    private function applyEmission(float $emission): void
    {
        if ($emission <= 0.0) {
            $this->emissionEnabled = false;

            return;
        }

        $base = $this->baseColor;
        $this->emissionEnabled = true;
        $this->emissionColor = new HdrColor(new Color($base->r, $base->g, $base->b), log($emission, 2));
        $this->emissionMap = $this->albedo;
    }

    /**
     * @param array<string, mixed> $object
     */
    private static function readString(array $object, string $key): string
    {
        $value = $object[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $object
     */
    private static function readFloat(array $object, string $key, float $fallback): float
    {
        $value = $object[$key] ?? null;

        return is_int($value) || is_float($value) ? (float) $value : $fallback;
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

    /**
     * @return array{x: float, y: float}
     */
    private static function writeVector2(Vector2 $value): array
    {
        return ['x' => $value->x, 'y' => $value->y];
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

    private function notifyChanged(): void
    {
        if ($this->changeListener === null) {
            return;
        }

        ($this->changeListener)($this);
    }
}
