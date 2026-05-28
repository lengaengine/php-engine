<?php

declare(strict_types=1);

namespace Lenga\Engine\Tweening;

enum EasingFunction: string
{
    case Linear = 'Linear';
    case EaseInQuad = 'EaseInQuad';
    case EaseOutQuad = 'EaseOutQuad';
    case EaseInOutQuad = 'EaseInOutQuad';
    case EaseInCubic = 'EaseInCubic';
    case EaseOutCubic = 'EaseOutCubic';
    case EaseInOutCubic = 'EaseInOutCubic';
    case EaseInSine = 'EaseInSine';
    case EaseOutSine = 'EaseOutSine';
    case EaseInOutSine = 'EaseInOutSine';
}
