<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

enum CanvasRenderMode: string
{
    case ScreenSpaceOverlay = 'ScreenSpaceOverlay';
    case ScreenSpaceCamera = 'ScreenSpaceCamera';
    case WorldSpace = 'WorldSpace';
}
