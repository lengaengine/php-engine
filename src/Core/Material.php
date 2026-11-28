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
use function array_diff;
use function array_keys;
use function array_unique;
use function array_values;
use function in_array;
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
 * Assigned to a renderer, a Material draws with every field it has: the
 * renderer's material slot takes its asset, or the renderer's default
 * material when it has none, with the fields the Material sets over it. A
 * Material read from a renderer carries the values its slot draws with, and
 * assigning one of its fields changes the slot, as assigning a field of
 * Unity's Renderer.material does. Changing an object a field holds, such as
 * `$material->tiling->x`, does not; assign the field.
 */
final class Material implements Stringable
{
    /**
     * The material file version {@see toAssetDocument()} writes and
     * {@see fromAssetDocument()} reads.
     */
    public const int ASSET_DOCUMENT_VERSION = 2;

    /**
     * The version of the data {@see __serialize()} writes. Data without a
     * version was written before 0.10 and holds the fields flat.
     */
    private const int SERIALIZATION_VERSION = 3;

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
     * The shader mode name of a material that reacts to scene lighting.
     *
     * @deprecated Use {@see MaterialShaderMode::Lit} with {@see $shaderMode}.
     */
    public const string SHADER_LIT = 'Lit';

    /**
     * The shader mode name of a material that ignores scene lighting.
     *
     * @deprecated Use {@see MaterialShaderMode::Unlit} with {@see $shaderMode}.
     */
    public const string SHADER_UNLIT = 'Unlit';

    /**
     * The rendering mode name of a solid surface.
     *
     * @deprecated Use {@see MaterialSurfaceType::Opaque}.
     */
    public const string RENDERING_OPAQUE = 'Opaque';

    /**
     * The rendering mode name of a solid surface with see-through parts cut out by alpha clipping.
     *
     * @deprecated Use {@see MaterialSurfaceType::Opaque} with {@see $alphaClipping}.
     */
    public const string RENDERING_CUTOUT = 'Cutout';

    /**
     * The rendering mode name of a see-through surface that fades out entirely with its alpha.
     *
     * @deprecated Use {@see MaterialSurfaceType::Transparent} with {@see MaterialBlendingMode::Alpha}.
     */
    public const string RENDERING_FADE = 'Fade';

    /**
     * The rendering mode name of a see-through surface, such as glass, that keeps its highlights.
     *
     * @deprecated Use {@see MaterialSurfaceType::Transparent} with {@see MaterialBlendingMode::Premultiply}.
     */
    public const string RENDERING_TRANSPARENT = 'Transparent';

    /**
     * The name of the first UV channel of a mesh.
     *
     * @deprecated Use {@see MaterialUvSet::UV0} with {@see $secondaryUv}.
     */
    public const string UV0 = 'UV0';

    /**
     * The name of the second UV channel of a mesh.
     *
     * @deprecated Use {@see MaterialUvSet::UV1} with {@see $secondaryUv}.
     */
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
            $this->recordChange('baseColor');
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
     */
    public MaterialShaderMode $shaderMode = MaterialShaderMode::Lit {
        set {
            $this->shaderMode = $value;
            $this->recordChange('shaderMode');
        }
    }

    /**
     * The name of the built-in shader the material draws with, 'Lit' or 'Unlit'.
     *
     * It reads and writes {@see $shaderMode}. Assigning a name sets the case
     * with that value, and assigning a case sets it as it is. Any other string
     * throws an InvalidArgumentException.
     *
     * @deprecated Use {@see $shaderMode}.
     */
    public string $shader {
        get => $this->shaderMode->value;

        set(MaterialShaderMode|string $value) {
            $this->shaderMode = $value instanceof MaterialShaderMode
                ? $value
                : (MaterialShaderMode::tryFrom($value)
                    ?? throw new InvalidArgumentException("Invalid material shader '{$value}'."));
        }
    }

    /**
     * The UV channel the detail textures sample.
     */
    public MaterialUvSet $secondaryUv = MaterialUvSet::UV0 {
        set {
            $this->secondaryUv = $value;
            $this->recordChange('secondaryUv');
        }
    }

    /**
     * The name of the UV channel the detail textures sample, 'UV0' or 'UV1'.
     *
     * It reads and writes {@see $secondaryUv}. Assigning a name sets the case
     * with that value, and assigning a case sets it as it is. Any other string
     * throws an InvalidArgumentException.
     *
     * @deprecated Use {@see $secondaryUv}.
     */
    public string $secondaryUvSet {
        get => $this->secondaryUv->value;

        set(MaterialUvSet|string $value) {
            $this->secondaryUv = $value instanceof MaterialUvSet
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

    /**
     * Where each field is in a material file's document.
     */
    private const array DOCUMENT_PATHS = [
        'name' => ['material', 'name'],
        'shaderMode' => ['material', 'shader'],
        'surfaceType' => ['material', 'surfaceOptions', 'surfaceType'],
        'blendingMode' => ['material', 'surfaceOptions', 'blendingMode'],
        'renderFace' => ['material', 'surfaceOptions', 'renderFace'],
        'alphaClipping' => ['material', 'surfaceOptions', 'alphaClipping'],
        'alphaClipThreshold' => ['material', 'surfaceOptions', 'alphaClipThreshold'],
        'sortingPriority' => ['material', 'surfaceOptions', 'sortingPriority'],
        'depthWrite' => ['material', 'surfaceOptions', 'depthWrite'],
        'depthTest' => ['material', 'surfaceOptions', 'depthTest'],
        'baseColor' => ['baseColor'],
        'metallic' => ['metallic'],
        'smoothness' => ['smoothness'],
        'emissionEnabled' => ['emissionEnabled'],
        'emissionColor' => ['emissionColor'],
        'shaderAssetPath' => ['shaderAssetPath'],
        'albedo' => ['baseTexturePath'],
        'normalMap' => ['normalMapPath'],
        'heightMap' => ['heightMapPath'],
        'occlusion' => ['occlusionMapPath'],
        'detailMask' => ['detailMaskPath'],
        'detailAlbedo' => ['detailAlbedoPath'],
        'detailNormalMap' => ['detailNormalMapPath'],
        'emissionMap' => ['emissionMapPath'],
        'tiling' => ['tiling'],
        'offset' => ['offset'],
        'secondaryTiling' => ['secondaryTiling'],
        'secondaryOffset' => ['secondaryOffset'],
        'secondaryUv' => ['secondaryUvSet'],
    ];

    private ?string $assetPathValue = null;
    private Color $baseColorValue;
    private ?Closure $changeListener = null;

    /**
     * Whether the fields hold the values of a material file or renderer slot,
     * read with them, rather than only the values a script gave.
     */
    private bool $documentBacked = false;

    /** The native slot had no material; its document describes inherited draw values. */
    private bool $defaultSlot = false;

    /**
     * The fields assigned since the material was made or read.
     *
     * @var array<string, true>
     */
    private array $assignedFields = [];

    /**
     * Whether assignments are recorded: off while the material is made or read.
     */
    private bool $recordingChanges = false;

    /**
     * Creates a material.
     *
     * The parameters up to secondaryUvSet keep the order they had before the
     * Surface Options and Emission settings were added, which follow them.
     *
     * @param ?string $assetPath Project-relative material asset path, or null for an inline material.
     * @param string $name Display name.
     * @param MaterialShaderMode|string $shader The built-in shader the material draws with, stored in {@see $shaderMode}. A name string is deprecated.
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
     * @param MaterialUvSet|string $secondaryUvSet UV channel the detail textures sample, stored in {@see $secondaryUv}. A name string is deprecated.
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
        public string $name = 'Material' {
            set {
                $this->name = $value;
                $this->recordChange('name');
            }
        },
        MaterialShaderMode|string $shader = MaterialShaderMode::Lit,
        ?string $renderingMode = null,
        public ?string $shaderAssetPath = null {
            set {
                $this->shaderAssetPath = $value;
                $this->recordChange('shaderAssetPath');
            }
        },
        Color $baseColor = new Color(1.0, 1.0, 1.0, 1.0),
        public ?string $albedo = null {
            set {
                $this->albedo = $value;
                $this->recordChange('albedo');
            }
        },
        public float $metallic = 0.0 {
            set {
                $this->metallic = $value;
                $this->recordChange('metallic');
            }
        },
        public float $smoothness = 0.5 {
            set {
                $this->smoothness = $value;
                $this->recordChange('smoothness');
            }
        },
        public ?string $normalMap = null {
            set {
                $this->normalMap = $value;
                $this->recordChange('normalMap');
            }
        },
        public ?string $heightMap = null {
            set {
                $this->heightMap = $value;
                $this->recordChange('heightMap');
            }
        },
        public ?string $occlusion = null {
            set {
                $this->occlusion = $value;
                $this->recordChange('occlusion');
            }
        },
        ?float $emission = null,
        public ?string $detailMask = null {
            set {
                $this->detailMask = $value;
                $this->recordChange('detailMask');
            }
        },
        public Vector2 $tiling = new Vector2(1.0, 1.0) {
            set {
                $this->tiling = $value;
                $this->recordChange('tiling');
            }
        },
        public Vector2 $offset = new Vector2(0.0, 0.0) {
            set {
                $this->offset = $value;
                $this->recordChange('offset');
            }
        },
        public ?string $detailAlbedo = null {
            set {
                $this->detailAlbedo = $value;
                $this->recordChange('detailAlbedo');
            }
        },
        public ?string $detailNormalMap = null {
            set {
                $this->detailNormalMap = $value;
                $this->recordChange('detailNormalMap');
            }
        },
        public Vector2 $secondaryTiling = new Vector2(1.0, 1.0) {
            set {
                $this->secondaryTiling = $value;
                $this->recordChange('secondaryTiling');
            }
        },
        public Vector2 $secondaryOffset = new Vector2(0.0, 0.0) {
            set {
                $this->secondaryOffset = $value;
                $this->recordChange('secondaryOffset');
            }
        },
        MaterialUvSet|string $secondaryUvSet = MaterialUvSet::UV0,
        public bool $emissionEnabled = false {
            set {
                $this->emissionEnabled = $value;
                $this->recordChange('emissionEnabled');
            }
        },
        public HdrColor $emissionColor = new HdrColor(new Color(0.0, 0.0, 0.0, 1.0)) {
            set {
                $this->emissionColor = $value;
                $this->recordChange('emissionColor');
            }
        },
        public ?string $emissionMap = null {
            set {
                $this->emissionMap = $value;
                $this->recordChange('emissionMap');
            }
        },
        public MaterialSurfaceType $surfaceType = MaterialSurfaceType::Opaque {
            set {
                $this->surfaceType = $value;
                $this->recordChange('surfaceType');
            }
        },
        public MaterialBlendingMode $blendingMode = MaterialBlendingMode::Alpha {
            set {
                $this->blendingMode = $value;
                $this->recordChange('blendingMode');
            }
        },
        public MaterialRenderFace $renderFace = MaterialRenderFace::Front {
            set {
                $this->renderFace = $value;
                $this->recordChange('renderFace');
            }
        },
        public bool $alphaClipping = false {
            set {
                $this->alphaClipping = $value;
                $this->recordChange('alphaClipping');
            }
        },
        public float $alphaClipThreshold = self::DEFAULT_ALPHA_CLIP_THRESHOLD {
            set {
                $this->alphaClipThreshold = $value;
                $this->recordChange('alphaClipThreshold');
            }
        },
        public int $sortingPriority = 0 {
            set {
                $this->sortingPriority = $value;
                $this->recordChange('sortingPriority');
            }
        },
        public MaterialDepthWrite $depthWrite = MaterialDepthWrite::Auto {
            set {
                $this->depthWrite = $value;
                $this->recordChange('depthWrite');
            }
        },
        public MaterialDepthTest $depthTest = MaterialDepthTest::LessEqual {
            set {
                $this->depthTest = $value;
                $this->recordChange('depthTest');
            }
        },
    ) {
        $this->assetPathValue = self::normalizeNullablePath($assetPath);
        $this->shader = $shader;
        $this->secondaryUvSet = $secondaryUvSet;
        $this->baseColorValue = $baseColor;
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
        $this->recordingChanges = true;
    }

    /**
     * Creates a material reference from a project-relative material asset path.
     *
     * @param string $assetPath The project-relative path of the material file, such as `Assets/Materials/Rock.material.json`.
     * @throws InvalidArgumentException When the path is empty.
     */
    public static function fromAssetPath(string $assetPath): self
    {
        return new self(assetPath: self::requirePath($assetPath));
    }

    /**
     * Creates an inline material that uses a base color without requiring an asset.
     *
     * @param Color $baseColor The color of the surface.
     */
    public static function fromColor(Color $baseColor): self
    {
        return new self(baseColor: $baseColor);
    }

    /**
     * Creates a material from the state a renderer reports.
     *
     * @internal Engine bookkeeping. Scripts read a renderer's material with
     *     {@see Renderer::$material}.
     *
     * @param array<string, mixed> $state The renderer state.
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
     * @param array<string, mixed> $document The decoded JSON of a material file.
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
                'shader' => $this->shaderMode->value,
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
            'secondaryUvSet' => $this->secondaryUv->value,
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
        return isset($this->assignedFields['baseColor'])
            || $this->baseColor->toRGBA() !== Color::white()->toRGBA();
    }

    /**
     * Creates the material of a renderer's slot from the native renderer's
     * description of it: the asset the slot references, '' for none, and the
     * material file document of what the slot draws with.
     *
     * @internal
     *
     * @param array{assetPath?: mixed, document?: mixed, defaultSlot?: mixed} $slot
     */
    public static function fromRendererSlot(array $slot): self
    {
        $document = is_array($slot['document'] ?? null) ? $slot['document'] : [];

        $material = self::fromAssetDocument($document, self::normalizeNullablePath($slot['assetPath'] ?? null));
        $material->defaultSlot = $material->assetPath === null && ($slot['defaultSlot'] ?? false) === true;

        return $material;
    }

    /**
     * The native renderer's description of this material for a slot.
     *
     * With an asset path, the slot takes the asset, and `document` holds the
     * fields that go over the asset's: every field for a material read with
     * its values, and the fields a script set otherwise. Without one, a
     * script-made material that sets no field but its base colour leaves the
     * slot to the renderer's default material in that colour, as
     * Material::fromColor() always has. This also applies to a slot that was
     * already using the renderer's default. Inline materials read from the
     * renderer keep their full documents.
     *
     * @internal
     *
     * @return array{assetPath: string, document: ?array<string, mixed>, baseColor?: string}
     */
    public function toRendererSlot(): array
    {
        if ($this->assetPath !== null) {
            $document = $this->documentBacked ? $this->toAssetDocument() : $this->changedDocument();

            return ['assetPath' => $this->assetPath, 'document' => $document === [] ? null : $document];
        }

        if ((!$this->documentBacked || $this->defaultSlot) && array_diff($this->changedFields(), ['baseColor']) === []) {
            return ['assetPath' => '', 'document' => null, 'baseColor' => $this->baseColor->toHex()];
        }

        return ['assetPath' => '', 'document' => $this->toAssetDocument()];
    }

    /**
     * Serializes the material and preserves which fields override its asset.
     *
     * @return array{version: int, assetPath: ?string, document: array<string, mixed>, documentBacked: bool, defaultSlot: bool, assignedFields: list<string>}
     */
    public function __serialize(): array
    {
        return [
            'version' => self::SERIALIZATION_VERSION,
            'assetPath' => $this->assetPath,
            'document' => $this->toAssetDocument(),
            'documentBacked' => $this->documentBacked,
            'defaultSlot' => $this->defaultSlot,
            'assignedFields' => array_keys($this->assignedFields),
        ];
    }

    /**
     * Restores the material from {@see __serialize()} data.
     *
     * Data serialized before 0.10 has no version and holds the fields as the
     * constructor takes them. Its renderingMode and emission convert as the
     * deprecated {@see $renderingMode} and {@see $emission} convert them, and
     * its base colour counts as custom, as it did when that data was
     * unserialized before.
     *
     * Version 2 recorded only the full document and cannot recover which
     * fields were overrides. It is restored as a complete material.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException When the data has a version this class does not write.
     */
    public function __unserialize(array $data): void
    {
        $version = $data['version'] ?? null;
        if ($version === null) {
            $this->readPreviousSerialization($data);

            return;
        }

        if ($version !== 2 && $version !== self::SERIALIZATION_VERSION) {
            throw new InvalidArgumentException("Unsupported material serialization version '{$version}'.");
        }

        $this->assetPathValue = self::normalizeNullablePath($data['assetPath'] ?? null);
        $this->readAssetDocument(is_array($data['document'] ?? null) ? $data['document'] : []);
        if ($version === self::SERIALIZATION_VERSION) {
            $this->documentBacked = ($data['documentBacked'] ?? true) === true;
            $this->defaultSlot = $this->documentBacked && ($data['defaultSlot'] ?? false) === true;
            foreach (is_array($data['assignedFields'] ?? null) ? $data['assignedFields'] : [] as $field) {
                if (is_string($field) && isset(self::DOCUMENT_PATHS[$field])) {
                    $this->assignedFields[$field] = true;
                }
            }
        }
    }

    /**
     * Restores data serialized before 0.10. Its keys are the constructor's
     * parameters, so the constructor converts it with the rules it applies
     * to scripts that pass those parameters.
     *
     * @param array<string, mixed> $data
     */
    private function readPreviousSerialization(array $data): void
    {
        $this->__construct(
            assetPath: self::normalizeNullablePath($data['assetPath'] ?? null),
            name: (string) ($data['name'] ?? 'Material'),
            shader: (string) ($data['shader'] ?? MaterialShaderMode::Lit->value),
            renderingMode: (string) ($data['renderingMode'] ?? self::RENDERING_OPAQUE),
            shaderAssetPath: self::normalizeNullablePath($data['shaderAssetPath'] ?? null),
            baseColor: Color::fromRGBAArray(is_array($data['baseColor'] ?? null) ? $data['baseColor'] : []),
            albedo: self::normalizeNullablePath($data['albedo'] ?? null),
            metallic: (float) ($data['metallic'] ?? 0.0),
            smoothness: (float) ($data['smoothness'] ?? 0.5),
            normalMap: self::normalizeNullablePath($data['normalMap'] ?? null),
            heightMap: self::normalizeNullablePath($data['heightMap'] ?? null),
            occlusion: self::normalizeNullablePath($data['occlusion'] ?? null),
            emission: (float) ($data['emission'] ?? 0.0),
            detailMask: self::normalizeNullablePath($data['detailMask'] ?? null),
            tiling: self::readVector2($data['tiling'] ?? null, new Vector2(1.0, 1.0)),
            offset: self::readVector2($data['offset'] ?? null, new Vector2(0.0, 0.0)),
            detailAlbedo: self::normalizeNullablePath($data['detailAlbedo'] ?? null),
            detailNormalMap: self::normalizeNullablePath($data['detailNormalMap'] ?? null),
            secondaryTiling: self::readVector2($data['secondaryTiling'] ?? null, new Vector2(1.0, 1.0)),
            secondaryOffset: self::readVector2($data['secondaryOffset'] ?? null, new Vector2(0.0, 0.0)),
            secondaryUvSet: (string) ($data['secondaryUvSet'] ?? MaterialUvSet::UV0->value),
        );
        // Before 0.10, unserializing assigned the base colour through its
        // property, which marks it custom whatever its value. The renderer
        // sends a custom base colour with the material's asset.
        $this->assignedFields['baseColor'] = true;
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
        $this->recordingChanges = false;
        $this->readAssetDocumentFields($document);
        $this->documentBacked = true;
        $this->defaultSlot = false;
        $this->assignedFields = [];
        $this->recordingChanges = true;
    }

    /**
     * @param array<string, mixed> $document
     */
    private function readAssetDocumentFields(array $document): void
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
        $this->shaderMode = MaterialShaderMode::tryFrom(self::readString($header, 'shader')) ?? MaterialShaderMode::Lit;
        $this->surfaceType = MaterialSurfaceType::tryFrom(self::readString($surface, 'surfaceType')) ?? MaterialSurfaceType::Opaque;
        $this->blendingMode = MaterialBlendingMode::tryFrom(self::readString($surface, 'blendingMode')) ?? MaterialBlendingMode::Alpha;
        $this->renderFace = MaterialRenderFace::tryFrom(self::readString($surface, 'renderFace')) ?? MaterialRenderFace::Front;
        $this->alphaClipping = is_bool($surface['alphaClipping'] ?? null) ? $surface['alphaClipping'] : false;
        $this->alphaClipThreshold = self::readFloat($surface, 'alphaClipThreshold', self::DEFAULT_ALPHA_CLIP_THRESHOLD);
        $this->sortingPriority = is_int($surface['sortingPriority'] ?? null) ? $surface['sortingPriority'] : 0;
        $this->depthWrite = MaterialDepthWrite::tryFrom(self::readString($surface, 'depthWrite')) ?? MaterialDepthWrite::Auto;
        $this->depthTest = MaterialDepthTest::tryFrom(self::readString($surface, 'depthTest')) ?? MaterialDepthTest::LessEqual;

        $this->baseColorValue = Color::tryFromHex(self::readString($document, 'baseColor')) ?? Color::white();
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
        $this->secondaryUv = MaterialUvSet::tryFrom(self::readString($document, 'secondaryUvSet')) ?? MaterialUvSet::UV0;
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

    /**
     * The fields this material sets: those assigned since it was made or
     * read and, for a material not read with its values, those that differ
     * from a new material's.
     *
     * @return list<string>
     */
    private function changedFields(): array
    {
        $fields = array_keys($this->assignedFields);
        if ($this->documentBacked) {
            return $fields;
        }

        $document = $this->toAssetDocument();
        $defaults = new self()->toAssetDocument();
        foreach (self::DOCUMENT_PATHS as $field => $path) {
            if (self::valueAt($document, $path) !== self::valueAt($defaults, $path)) {
                $fields[] = $field;
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * The material file document of the fields this material sets.
     *
     * @return array<string, mixed>
     */
    private function changedDocument(): array
    {
        $document = $this->toAssetDocument();
        $changedFields = $this->changedFields();
        $changed = [];
        foreach (self::DOCUMENT_PATHS as $field => $path) {
            if (!in_array($field, $changedFields, true)) {
                continue;
            }
            $target = &$changed;
            foreach ($path as $key) {
                $target[$key] ??= [];
                $target = &$target[$key];
            }
            $target = self::valueAt($document, $path);
            unset($target);
        }

        return $changed;
    }

    /**
     * @param array<string, mixed> $document
     * @param list<string> $path
     */
    private static function valueAt(array $document, array $path): mixed
    {
        $value = $document;
        foreach ($path as $key) {
            $value = is_array($value) ? ($value[$key] ?? null) : null;
        }

        return $value;
    }

    private function recordChange(string $field): void
    {
        if (!$this->recordingChanges) {
            return;
        }

        $this->assignedFields[$field] = true;
        $this->notifyChanged();
    }

    private function notifyChanged(): void
    {
        if ($this->changeListener === null) {
            return;
        }

        ($this->changeListener)($this);
    }
}
