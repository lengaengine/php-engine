<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Camera;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Core\MathUtil;
use Lenga\Engine\Core\Time;
use Lenga\Engine\Enumerations\KeyCode;

/**
 * Zooms the main camera in with Q and out with E. Works with perspective
 * and orthographic cameras.
 */
final class CameraZoom extends Behaviour
{
    #[Tooltip('How fast the view zooms, as a share of the current zoom each second.')]
    #[Min(0)]
    public float $zoomSpeed = 1.0;

    #[Tooltip('Narrowest field of view, in degrees, for a perspective camera.')]
    public float $minFieldOfView = 20.0;

    #[Tooltip('Widest field of view, in degrees, for a perspective camera.')]
    public float $maxFieldOfView = 90.0;

    #[Tooltip('Smallest Size, in world units, for an orthographic camera.')]
    public float $minSize = 2.0;

    #[Tooltip('Largest Size, in world units, for an orthographic camera.')]
    public float $maxSize = 40.0;

    public function update(): void
    {
        $zoom = 0.0;
        if (Input::getKey(KeyCode::Q)) {
            $zoom -= 1.0;
        }
        if (Input::getKey(KeyCode::E)) {
            $zoom += 1.0;
        }

        if ($zoom === 0.0) {
            return;
        }

        // Ask for the main camera when you need it: the engine picks it again
        // on every call, so the result follows cameras enabled or made primary.
        $camera = Camera::getMain();
        if ($camera === null) {
            return;
        }

        // Unscaled time keeps zoom working while the game is paused.
        $factor = 1.0 + ($zoom * $this->zoomSpeed * Time::unscaledDeltaTime());

        if ($camera->projection === 'Orthographic') {
            // A larger Size shows more of the world.
            $camera->orthographicSize = MathUtil::clamp(
                $camera->orthographicSize * $factor,
                $this->minSize,
                $this->maxSize,
            );
            return;
        }

        // A wider field of view shows more of the world.
        $camera->fieldOfView = MathUtil::clamp(
            $camera->fieldOfView * $factor,
            $this->minFieldOfView,
            $this->maxFieldOfView,
        );
    }
}
