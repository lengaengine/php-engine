# Lenga PHP Engine Guardrails

These rules apply to `lenga/engine` package code and tests. They are version-controlled so PHP conventions and native bridge boundaries remain stable across machines, environments, and agents.

## PHP Version and Style

- Write PHP 8.5+ code. Do not target generic or older PHP syntax.
- Use `declare(strict_types=1);` in new PHP files.
- Use typed class constants, properties, parameters, and return types wherever PHP 8.5 supports them.
- Document public PHP classes, properties, and methods with docblocks that describe developer-facing behavior, parameters, returns, and deprecations where relevant.
- Prefer `use` imports over fully qualified class names. Keep fully qualified names only when there is a clear reason, such as global built-ins, unavoidable name collisions, or intentionally explicit runtime references.
- Keep files LF/Unix line endings. Do not introduce CRLF line endings.

## Native Runtime Boundary

- Do not ship `EngineHeader.php` or any PHP declarations for native bridge functions.
- Do not call physical native functions directly from package source.
- Only `Lenga\Engine\Core\NativeEngine` may know the physical native bridge prefix.
- Call native bindings through logical names, for example `NativeEngine::call('scene_get_active')`.
- Do not pass physical native function names into `NativeEngine::call()`.
- Public APIs that require the native runtime should fail with a Lenga-specific runtime exception when used outside the Lenga editor/runtime.

## Verification

- Run `php -l` on changed PHP files before committing.
- Run `php-engine/vendor/bin/phpunit php-engine/tests` after runtime boundary, lifecycle, event, or API-surface changes.
- Keep the internal API boundary tests updated when bridge rules change.

## Commit Messages

- Use Conventional Commits for all commit messages before work is shared. Prefer `type(scope): summary` when a useful scope exists, for example `feat(api): add audio clips`, `fix(events): prevent duplicate listeners`, or `test(bridge): cover native call names`.
- Keep commit subjects lowercase after the type/scope prefix unless a proper noun, API symbol, or file name requires casing.
