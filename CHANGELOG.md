# Changelog

All notable changes to `lenga/engine` will be documented in this file.

## Unreleased

### Removed

- Removed `Scene::getBackdropLayers()`, `Scene::getBackdropLayer()`, `Scene::getBackdropLayerCount()` and the `BackdropLayer` class. The engine no longer has backdrop layers: since scene format 3 each saved layer is a sprite in the scene, a GameObject named "Backdrop N" under the main camera or the Backdrops root. Scripts that read or moved backdrop layers stop working; move those GameObjects instead.

### Changed

- `AudioMixer::transitionToPreset()` now sweeps the cutoff of low-pass and high-pass effects over the transition time, alongside channel volume, pitch and pan. Other effect settings still switch when the transition starts. The method's description lists which values blend.

### Added

- `SpriteRenderer::$drawMode` (`SpriteDrawMode::Simple` or `SpriteDrawMode::Tiled`) and `SpriteRenderer::$tileSize`. A Tiled sprite repeats its image across its size, one tile every `tileSize` world units.
- `Tween::spriteColorTo()`, which tweens a sprite's colour.
- `CameraRig`, a component that moves the camera on its GameObject. It follows `$target` with per-axis `$dampingTime`, an optional `$deadZone` and `$lookaheadDistance`, keeps a 2D view inside `$boundsMin` and `$boundsMax` when `$boundsEnabled` is set, and eases the camera's Size toward `$targetSize`. `shake()` shakes the view without moving the Transform, and `snapToTarget()` jumps to the followed point. In a 2D scene the rig follows on X and Y and keeps the camera's Z; in a 3D scene it follows on X, Y and Z. The rig runs while the game runs.
- Listed the classes renamed between Lenga lines in `composer.json`, under `extra.lenga.renames`. The editor reads the list when it upgrades a project and rewrites the project's scripts to the new names. A class that moves or changes its name gets an entry under the line that renames it.

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
