<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Attributes\ListOf;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Color;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Internal\BehaviourBridge;
use PHPUnit\Framework\TestCase;

final class ListOfReceiver
{
    public string $name = '';
    public Vector3 $offset;
    public Color $tint;
}

final class ListOfSerializedBehaviour extends Behaviour
{
    /** @var array<int, Vector3> */
    #[ListOf(Vector3::class)]
    public array $points = [];

    /** @var array<int, Color> */
    #[ListOf(Color::class)]
    public array $colors = [];

    /** @var array<int, ListOfReceiver> */
    #[ListOf(ListOfReceiver::class)]
    public array $receivers = [];

    /** @var array<int, mixed> */
    public array $untypedValues = [];
}

final class BehaviourListOfSerializationTest extends TestCase
{
    public function testListOfHydratesStructuredAndComplexItems(): void
    {
        $behaviour = new ListOfSerializedBehaviour();

        BehaviourBridge::applyProperties($behaviour, [
            'points' => [
                ['x' => 1.0, 'y' => 2.0, 'z' => 3.0],
                ['x' => -4.0, 'y' => 5.5, 'z' => 6.25],
            ],
            'colors' => [
                ['r' => 12, 'g' => 34, 'b' => 56, 'a' => 78],
            ],
            'receivers' => [
                [
                    'name' => 'Primary',
                    'offset' => ['x' => 7.0, 'y' => 8.0, 'z' => 9.0],
                    'tint' => ['r' => 90, 'g' => 120, 'b' => 150, 'a' => 180],
                ],
            ],
            'untypedValues' => [
                ['x' => 10.0, 'y' => 11.0, 'z' => 12.0],
            ],
        ]);

        self::assertContainsOnlyInstancesOf(Vector3::class, $behaviour->points);
        self::assertSame(1.0, $behaviour->points[0]->x);
        self::assertSame(5.5, $behaviour->points[1]->y);

        self::assertContainsOnlyInstancesOf(Color::class, $behaviour->colors);
        self::assertSame(['r' => 12, 'g' => 34, 'b' => 56, 'a' => 78], $behaviour->colors[0]->toRGBA());

        self::assertContainsOnlyInstancesOf(ListOfReceiver::class, $behaviour->receivers);
        self::assertSame('Primary', $behaviour->receivers[0]->name);
        self::assertInstanceOf(Vector3::class, $behaviour->receivers[0]->offset);
        self::assertSame(9.0, $behaviour->receivers[0]->offset->z);
        self::assertInstanceOf(Color::class, $behaviour->receivers[0]->tint);
        self::assertSame(['r' => 90, 'g' => 120, 'b' => 150, 'a' => 180], $behaviour->receivers[0]->tint->toRGBA());

        self::assertIsArray($behaviour->untypedValues[0]);
    }
}
