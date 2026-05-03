<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\Collision2D;
use Lenga\Engine\Core\Collision3D;
use PHPUnit\Framework\TestCase;

final class CollisionPayloadTest extends TestCase
{
    public function testCollision2DPrimaryFieldsPointAtContactingObject(): void
    {
        $collision = Collision2D::fromNativeData([
            'selfGameObject' => self::gameObjectData('Floor', 10, 'Floor'),
            'selfCollider' => self::componentData('BoxCollider2D', 20, 'Floor', 10, 'Floor'),
            'gameObject' => self::gameObjectData('Ball', 11, 'Ball'),
            'collider' => self::componentData('CircleCollider2D', 21, 'Ball', 11, 'Ball'),
            'otherGameObject' => self::gameObjectData('Ball', 11, 'Ball'),
            'otherCollider' => self::componentData('CircleCollider2D', 21, 'Ball', 11, 'Ball'),
            'isTrigger' => true,
        ]);

        self::assertSame(11, $collision->gameObject?->getInstanceId());
        self::assertSame(21, $collision->collider?->getInstanceId());
        self::assertSame(11, $collision->otherGameObject?->getInstanceId());
        self::assertSame(21, $collision->otherCollider?->getInstanceId());
        self::assertSame(10, $collision->selfGameObject?->getInstanceId());
        self::assertSame(20, $collision->selfCollider?->getInstanceId());
        self::assertTrue($collision->isTrigger);
    }

    public function testCollision3DPrimaryFieldsPointAtContactingObject(): void
    {
        $collision = Collision3D::fromNativeData([
            'selfGameObject' => self::gameObjectData('Hazard', 30, 'Hazard'),
            'selfCollider' => self::componentData('BoxCollider3D', 40, 'Hazard', 30, 'Hazard'),
            'gameObject' => self::gameObjectData('Player', 31, 'Player'),
            'collider' => self::componentData('SphereCollider3D', 41, 'Player', 31, 'Player'),
            'otherGameObject' => self::gameObjectData('Player', 31, 'Player'),
            'otherCollider' => self::componentData('SphereCollider3D', 41, 'Player', 31, 'Player'),
            'isTrigger' => false,
        ]);

        self::assertSame(31, $collision->gameObject?->getInstanceId());
        self::assertSame(41, $collision->collider?->getInstanceId());
        self::assertSame(31, $collision->otherGameObject?->getInstanceId());
        self::assertSame(41, $collision->otherCollider?->getInstanceId());
        self::assertSame(30, $collision->selfGameObject?->getInstanceId());
        self::assertSame(40, $collision->selfCollider?->getInstanceId());
        self::assertFalse($collision->isTrigger);
    }

    /**
     * @return array{name: string, id: int, tag: string, activeSelf: bool, activeInHierarchy: bool}
     */
    private static function gameObjectData(string $name, int $id, string $tag): array
    {
        return [
            'name' => $name,
            'id' => $id,
            'tag' => $tag,
            'activeSelf' => true,
            'activeInHierarchy' => true,
        ];
    }

    /**
     * @return array{id: int, type: string, gameObject: array{name: string, id: int, tag: string, activeSelf: bool, activeInHierarchy: bool}}
     */
    private static function componentData(string $type, int $id, string $gameObjectName, int $gameObjectId, string $tag): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'gameObject' => self::gameObjectData($gameObjectName, $gameObjectId, $tag),
        ];
    }
}
