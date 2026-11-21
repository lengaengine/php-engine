# Changelog

All notable changes to `lenga/engine` will be documented in this file.

## Unreleased

### Changed

- `AudioMixer::transitionToPreset()` now sweeps the cutoff of low-pass and high-pass effects over the transition time, alongside channel volume, pitch and pan. Other effect settings still switch when the transition starts. The method's description lists which values blend.
- `Material` follows the engine's Unity URP material model. Its fields are those of a version 2 material file.
- `Material::$shader` holds a `MaterialShaderMode` enum and `Material::$secondaryUvSet` a `MaterialUvSet` enum, both in `Lenga\Engine\Enumerations`. They held strings. The property and the constructor still accept the strings `'Lit'`, `'Unlit'`, `'UV0'` and `'UV1'`, which are deprecated, and any other string throws an `InvalidArgumentException` as before. Reading the property gives the enum case, so a script that compares it with a string must compare with the case or its `value`. The constants `Material::SHADER_LIT`, `SHADER_UNLIT`, `UV0` and `UV1` now hold the enum cases and are deprecated; a script that uses them as strings must use their `value`.
- `Material::$renderingMode`, its constructor parameter and the `RENDERING_*` constants are deprecated. Assigning a Rendering Mode sets the Surface Options as the engine converts a version 1 material file: Opaque is Opaque; Cutout is Opaque with Alpha Clipping at 0.5; Fade is Transparent with Alpha blending; Transparent is Transparent with Premultiply blending. Reading it gives the mode the Surface Options correspond to, and Transparent for Additive and Multiply blending.
- `Material::$emission` and its constructor parameter are deprecated. Assigning a value converts it once, as the engine converts a version 1 material file once when it reads it: a value above 0 turns Emission on with the opaque base colour at log2(value) stops as the Emission Color and the base texture as the Emission Map; 0 turns Emission off. Reading it gives 0 when Emission is off and 2^intensity of the Emission Color when it is on.
- `Material` serializes as a version number, its asset path and its material file document. Data serialized by earlier versions still unserializes; its `renderingMode` and `emission` convert as the deprecated properties convert them.

### Added

- Listed the classes renamed between Lenga lines in `composer.json`, under `extra.lenga.renames`. The editor reads the list when it upgrades a project and rewrites the project's scripts to the new names. A class that moves or changes its name gets an entry under the line that renames it.
- `Material` Surface Options: `surfaceType`, `blendingMode`, `renderFace`, `alphaClipping`, `alphaClipThreshold`, `sortingPriority`, `depthWrite` and `depthTest`, with the enums `MaterialSurfaceType`, `MaterialBlendingMode`, `MaterialRenderFace`, `MaterialDepthWrite` and `MaterialDepthTest`. Names and defaults are the engine's.
- `Material` Emission: `emissionEnabled`, `emissionColor` and `emissionMap`.
- `HdrColor`, a colour with an intensity in exposure stops. `linearValue()` gives the colour decoded to linear times 2^intensity, as the engine computes it.
- `Material::toAssetDocument()` and `Material::fromAssetDocument()`, which write and read a material file's version 2 document.
- `Color::toHex()`, `Color::fromHex()` and `Color::tryFromHex()` for `#RRGGBB` and `#RRGGBBAA` strings.
- The descriptions of `Material` and `Renderer::$materials` state that a script-made Material sends only its asset path and base colour to the native renderer.

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
