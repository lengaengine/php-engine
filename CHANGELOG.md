# Changelog

All notable changes to `lenga/engine` will be documented in this file.

## Unreleased

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
