<p align="center">
  <img src="https://lengaengine.com/images/lenga-logo-xl.png" width="140" alt="Lenga Engine Logo" />
</p>

# Lenga PHP Engine

`lenga/engine` is the PHP gameplay API for the Lenga Engine.

Release history and package-level changes are tracked in [CHANGELOG.md](./CHANGELOG.md).

It provides the PHP-side classes that game scripts use at runtime, including:

- `Behaviour`
- `GameObject`
- `Transform`
- `Input`
- `Time`
- `Preferences`
- 2D and 3D component wrappers
- UI wrappers
- math and collision types

This package is intended to live as a standalone Composer package and be consumed by Lenga projects through normal Composer workflows.

## What This Package Is

This repository contains the PHP scripting surface that runs inside the Lenga native runtime.

It is the layer that lets developers write gameplay code in modern PHP and interact with the engine through high-level classes instead of raw bridge calls.

Example:

```php
<?php

declare(strict_types=1);

namespace Sample\HelloWorld\Scripts;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\KeyCode;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Vector2;

final class PlayerController extends Behaviour
{
    public function update(): void
    {
        if (Input::getKey(KeyCode::SPACE)) {
            $this->transform->translate2D(new Vector2(0, -100 * Time::$deltaTime));
        }
    }
}
```

## What This Package Is Not

This package does **not** include:

- the Lenga Editor
- the native runtime executable
- native physics/rendering/audio implementations
- export tooling
- platform packaging or signing tools

Those belong to the Lenga SDK and native engine distribution.

`lenga/engine` is the PHP API layer only.

## Using This Package

Install `lenga/engine` in your project through Composer, then write gameplay code against the `Lenga\Engine\...` API surface.

This package is designed for source projects. Exported Lenga builds are expected to include the resolved PHP dependencies they need at runtime.

## Requirements

- PHP `^8.5`
- `ext-json`
- `ext-mbstring`

## Installation

When the package is available through Composer, the normal install command will be:

```bash
composer require lenga/engine
```

If you are evaluating the package directly from source before it is published, you can install it through a Composer `path` or `vcs` repository.

## What Else You Need

`lenga/engine` gives you the PHP gameplay API. You still need the Lenga SDK to run and export a game.

The SDK provides:

- the editor
- the native runtime executable
- export/build tooling
- the embedded PHP bridge that calls into this package

This package provides:

- PHP gameplay classes
- wrapper types for native engine systems
- engine-facing utility types used by game scripts

Most projects need both:

- the SDK to run the game
- the PHP package to author and autoload game code

## Current API Areas

The current package includes first-class PHP surfaces for:

- lifecycle-driven behaviours
- scene and object lookup
- transform and vector math
- input and time
- preferences
- prefab instantiation
- 2D rendering and physics
- early 3D rendering and collision wrappers
- UI canvases, elements, and controls
- runtime event dispatch

## Example: Behaviour Script

```php
<?php

declare(strict_types=1);

namespace Sample\HelloWorld\Scripts;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\KeyCode;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Core\Vector3;

final class PlayerController extends Behaviour
{
    public function update(): void
    {
        if (Input::getKey(KeyCode::SPACE)) {
            $this->transform->translate(new Vector3(0, -100 * Time::$deltaTime, 0));
        }
    }
}
```

## Example: Runtime Spawning

```php
<?php

declare(strict_types=1);

namespace Sample\HelloWorld\Scripts;

use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\RectangleRenderer;
use Lenga\Engine\Core\Vector3;

final class RuntimeSpawner extends Behaviour
{
    public function start(): void
    {
        $enemy = $this->gameObject->getScene()?->createGameObject('Enemy')
            ?? GameObject::create('Enemy');

        $enemy->transform->position = new Vector3(320, 180, 0);

        /** @var RectangleRenderer $renderer */
        $renderer = $enemy->addComponent(RectangleRenderer::class);
        $renderer->setSize(48, 48);
        $renderer->setColor(255, 90, 90);
        $renderer->sortingLayer = 'Foreground';
    }
}
```

## Example: Local Preferences

```php
<?php

declare(strict_types=1);

namespace Sample\HelloWorld\Scripts;

use Lenga\Engine\Core\Preferences;

Preferences::setFloat('audio.musicVolume', 0.8);
Preferences::save();
```

## Development

Install dev dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```

## License

This package is being prepared for source-available distribution under SSAL for the initial standalone release.

Until the standalone repository includes its final `LICENSE` file, do not assume MIT or open-source terms.
