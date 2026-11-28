<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * Where a {@see \Lenga\Engine\UI\Canvas} is placed: over the screen, in front of a camera or in the scene.
 *
 * Set it with {@see \Lenga\Engine\UI\Canvas::$renderMode}. Lenga saves the chosen
 * mode with the scene, but the runtime draws canvases the same way in every mode.
 */
enum CanvasRenderMode: string
{
    /** Draws the canvas over the screen, on top of the scene. This is the default mode. */
    case ScreenSpaceOverlay = 'ScreenSpaceOverlay';

    /** Draws the canvas in screen space in front of a camera. */
    case ScreenSpaceCamera = 'ScreenSpaceCamera';

    /** Places the canvas in the scene as a flat object. */
    case WorldSpace = 'WorldSpace';
}
