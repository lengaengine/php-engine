<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Attributes\SerializeField;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Internal\BehaviourBridge;
use PHPUnit\Framework\TestCase;

final class ColorSerializedBehaviour extends Behaviour
{
    #[SerializeField]
    private Color $privateTint;

    public Color $publicTint;

    public ?Color $nullableTint = null;

    public function privateTint(): Color
    {
        return $this->privateTint;
    }
}

final class BehaviourColorSerializationTest extends TestCase
{
    public function testSerializedColorArraysHydrateTypedColorProperties(): void
    {
        $behaviour = new ColorSerializedBehaviour();

        BehaviourBridge::applyProperties($behaviour, [
            'privateTint' => ['r' => 12, 'g' => 34, 'b' => 56, 'a' => 78],
            'publicTint' => [90, 120, 150, 180],
            'nullableTint' => null,
        ]);

        self::assertSame(['r' => 12, 'g' => 34, 'b' => 56, 'a' => 78], $behaviour->privateTint()->toRGBA());
        self::assertSame(['r' => 90, 'g' => 120, 'b' => 150, 'a' => 180], $behaviour->publicTint->toRGBA());
        self::assertNull($behaviour->nullableTint);
    }

    public function testMissingNonNullableColorValueFallsBackToDefaultColor(): void
    {
        $behaviour = new ColorSerializedBehaviour();

        BehaviourBridge::applyProperties($behaviour, [
            'publicTint' => null,
        ]);

        self::assertSame(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255], $behaviour->publicTint->toRGBA());
    }
}
