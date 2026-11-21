# Changelog

All notable changes to `lenga/engine` will be documented in this file.

## Unreleased

### Removed

- `Renderer` no longer calls the per-type `<renderer>_set_material_path`, `<renderer>_set_material_paths` and `<renderer>_set_color` native functions to assign materials, and no longer throws a `LogicException` for several material assets; it uses the renderer material bridge. The engine keeps `<renderer>_set_material_path` and `<renderer>_set_color` for scripts that call them.
- Removed `Scene::getBackdropLayers()`, `Scene::getBackdropLayer()`, `Scene::getBackdropLayerCount()` and the `BackdropLayer` class. The engine no longer has backdrop layers: since scene format 3 each saved layer is a sprite in the scene, a GameObject named "Backdrop N" under the main camera or the Backdrops root. Scripts that read or moved backdrop layers stop working; move those GameObjects instead.

### Changed

- `AudioMixer::transitionToPreset()` now sweeps the cutoff of low-pass and high-pass effects over the transition time, alongside channel volume, pitch and pan. Other effect settings still switch when the transition starts. The method's description lists which values blend.
- `Material` follows the engine's Unity URP material model. Its fields are those of a version 2 material file.
- `Material::$shaderMode` holds the shader as a `MaterialShaderMode` enum and `Material::$secondaryUv` the detail UV set as a `MaterialUvSet` enum, both in `Lenga\Engine\Enumerations`. `Material::$shader` and `Material::$secondaryUvSet` are deprecated. They read and write the same values as their names, `'Lit'`, `'Unlit'`, `'UV0'` and `'UV1'`, as before, and also accept the enum cases; any other string throws an `InvalidArgumentException` as before. The constants `Material::SHADER_LIT`, `SHADER_UNLIT`, `UV0` and `UV1` are deprecated and hold the same strings as before. The constructor's `shader` and `secondaryUvSet` parameters take the enum cases or the names.
- `Material::$renderingMode`, its constructor parameter and the `RENDERING_*` constants are deprecated. Assigning a Rendering Mode sets the Surface Options as the engine converts a version 1 material file: Opaque is Opaque; Cutout is Opaque with Alpha Clipping at 0.5; Fade is Transparent with Alpha blending; Transparent is Transparent with Premultiply blending. Reading it gives the mode the Surface Options correspond to, and Transparent for Additive and Multiply blending.
- `Material::$emission` and its constructor parameter are deprecated. Assigning a value converts it once, as the engine converts a version 1 material file once when it reads it: a value above 0 turns Emission on with the opaque base colour at log2(value) stops as the Emission Color and the base texture as the Emission Map; 0 turns Emission off. Reading it gives 0 when Emission is off and 2^intensity of the Emission Color when it is on.
- `Material` serializes as a version number, its asset path and its material file document. Data serialized by earlier versions still unserializes; its `renderingMode` and `emission` convert as the deprecated properties convert them, and its base colour counts as custom, as before.
- A `Material` assigned to a renderer draws with every field it has, through the engine's renderer material bridge (`renderer_get_materials`, `renderer_set_materials`, `renderer_set_material`). A material with an asset path draws its asset with the fields a script set over it, or every field when it was read with its values; a script-made material draws with all of its fields; a material that sets nothing but its base colour leaves the slot to the renderer's default material in that colour, as `Material::fromColor()` did. Before, only the asset path and base colour reached the renderer.
- `Component::$gameObject`, `Component::$type`, `Component::$enabled`, `Behaviour::$gameObject`, `Behaviour::$transform` and `Behaviour::$enabled` carry `#[HideInInspector]`. The editor reads the properties a class inherits, and these accessors are runtime handles: the Inspector draws no field for them and a scene saves no value for them.
- `Renderer::$materials` reads every slot with the values it draws with, including its Surface Options and Emission, and assigning sets every slot. Assigning a field of a Material read from or assigned to a renderer changes its slot; before, only `assetPath` and `baseColor` did. `Renderer::$material` sets the first slot and leaves the others, as the native bridge did before.

### Added

- `SpriteRenderer::$drawMode` (`SpriteDrawMode::Simple` or `SpriteDrawMode::Tiled`) and `SpriteRenderer::$tileSize`. A Tiled sprite repeats its image across its size, one tile every `tileSize` world units.
- `Tween::spriteColorTo()`, which tweens a sprite's colour.
- `CameraRig`, a component that moves the camera on its GameObject. It follows `$target` with per-axis `$dampingTime`, an optional `$deadZone` and `$lookaheadDistance`, keeps a 2D view inside `$boundsMin` and `$boundsMax` when `$boundsEnabled` is set, and eases the camera's Size toward `$targetSize`. `shake()` shakes the view without moving the Transform, and `snapToTarget()` jumps to the followed point. In a 2D scene the rig follows on X and Y and keeps the camera's Z; in a 3D scene it follows on X, Y and Z. The rig runs while the game runs.
- Listed the classes renamed between Lenga lines in `composer.json`, under `extra.lenga.renames`. The editor reads the list when it upgrades a project and rewrites the project's scripts to the new names. A class that moves or changes its name gets an entry under the line that renames it.
- `Material` Surface Options: `surfaceType`, `blendingMode`, `renderFace`, `alphaClipping`, `alphaClipThreshold`, `sortingPriority`, `depthWrite` and `depthTest`, with the enums `MaterialSurfaceType`, `MaterialBlendingMode`, `MaterialRenderFace`, `MaterialDepthWrite` and `MaterialDepthTest`. Names and defaults are the engine's.
- `Material` Emission: `emissionEnabled`, `emissionColor` and `emissionMap`.
- `HdrColor`, a colour with an intensity in exposure stops. `linearValue()` gives the colour decoded to linear times 2^intensity, as the engine computes it.
- `Material::toAssetDocument()` and `Material::fromAssetDocument()`, which write and read a material file's version 2 document.
- `Color::toHex()`, `Color::fromHex()` and `Color::tryFromHex()` for `#RRGGBB` and `#RRGGBBAA` strings.
- `Material::toRendererSlot()` and `Material::fromRendererSlot()` (internal), which describe a material to the renderer material bridge.

## 0.9.1 - 2026-08-07

### Fixed

- Aligned the PHP-side 2D physics compatibility contract with Lenga's positive-Y-down coordinate system and restored the default gravity vector to `(0, 980, 0)`.

## 0.9.0 - 2026-08-06

### Added

- Added `Lenga\Engine\Core\Random`, a gameplay-oriented random helper with an explicit pseudo-random engine and pure-PHP fallback for Web export compatibility.

### Fixed

- Fixed `GameObject::getComponent()` and related component lookup APIs so base component contracts such as `Component`, `ComponentInterface`, `Renderer`, and `RendererInterface` can resolve concrete native renderer wrappers like `TrailRenderer`.
- Fixed `Random`'s pure-PHP generator so Web exports avoid integer overflow warnings when generating seeded gameplay values.

## 0.7.1 - 2026-04-21

### Fixed

- Fixed `Vector4::moveTowards()` so negative `maxDelta` values move away from the target instead of snapping to it.

### Changed

- Added pure-PHP fallbacks for bridge-backed `Vector4` helpers when the native bridge functions are unavailable:
  - `lerp()`
  - `moveTowards()`
  - `clampMagnitude()`
  - `project()`

### Testing

- Added regression coverage for negative-distance `Vector4::moveTowards()` behavior.

## 0.6.0 - 2026-04-19

### Added

- Added first-class 2D effector wrappers for:
  - `PlatformEffector2D`
  - `AreaEffector2D`
  - `PointEffector2D`
  - `SurfaceEffector2D`
  - `BuoyancyEffector2D`
- Expanded `SpriteAnimation` parameter support with:
  - `getInt()`
  - `setInt()`
  - `getFloat()`
  - `setFloat()`
- Extended the PHP bridge/header surface for time scale, gameplay pause control, animator parameter access, and 2D effector bindings.

### Changed

- Improved behaviour and native component reference stability by carrying scene component ids during serialization and reattachment. This makes component resolution more reliable when a game object has multiple native components of the same type.
- Updated `GameObject` component registration so the new native 2D effectors are available through the PHP API.

### Compatibility Notes

- `Transform::translate2D()` now takes a `Vector2` offset instead of separate `dx` and `dy` float arguments.

Old:

```php
$transform->translate2D(10, 0);
```

New:

```php
use Lenga\Engine\Core\Vector2;

$transform->translate2D(new Vector2(10, 0));
```

## 0.5.0 - 2026-04-09

- Added quaternion support and broader 3D math/runtime API coverage.
