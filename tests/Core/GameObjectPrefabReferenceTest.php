<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\InstantiateOptions;
use Lenga\Engine\Core\ParticleSystem;
use Lenga\Engine\Core\Quaternion;
use Lenga\Engine\Core\Vector3;
use Lenga\Engine\Internal\BehaviourBridge;
use PHPUnit\Framework\TestCase;

final class PrefabParticleSystemReceiver extends Behaviour
{
    public ?ParticleSystem $hitSparkPrefab = null;
}

final class GameObjectPrefabReferenceTest extends TestCase
{
    public function testPrefabAssetPathCreatesDetachedPrefabReference(): void
    {
        $reference = GameObject::fromPrefabAssetPath('Assets/Prefabs/Projectile.prefab.json');

        self::assertNull($reference->getInstanceId());
        self::assertSame('Projectile', $reference->name);
        self::assertSame('Assets/Prefabs/Projectile.prefab.json', $reference->prefabAssetPath);
    }

    public function testPrefabReferenceSerializesAsPrefabAsset(): void
    {
        $reference = GameObject::fromPrefabAssetPath('Assets/Prefabs/Projectile.prefab.json', 'Projectile Source');

        self::assertSame([
            '__lengaRefKind' => 'PrefabAsset',
            'assetPath' => 'Assets/Prefabs/Projectile.prefab.json',
            'name' => 'Projectile Source',
        ], $reference->__serialize());
    }

    public function testSerializedPrefabAssetReferenceHydratesWithoutNativeRuntime(): void
    {
        $reference = GameObject::fromSerializedReference([
            '__lengaRefKind' => 'PrefabAsset',
            'assetPath' => 'Assets/Prefabs/MuzzleFlash.prefab.json',
            'name' => 'Muzzle Flash',
        ]);

        self::assertInstanceOf(GameObject::class, $reference);
        self::assertNull($reference->getInstanceId());
        self::assertSame('Muzzle Flash', $reference->name);
        self::assertSame('Assets/Prefabs/MuzzleFlash.prefab.json', $reference->prefabAssetPath);
    }

    public function testSetActiveOnPrefabReferenceDoesNotRequireNativeRuntime(): void
    {
        $reference = GameObject::fromPrefabAssetPath('Assets/Prefabs/Projectile.prefab.json');

        $reference->setActive(false);

        self::assertFalse($reference->activeSelf);
        self::assertFalse($reference->activeInHierarchy);
    }

    public function testPrefabOwnedComponentReferenceHydratesTypedParticleSystemField(): void
    {
        $behaviour = new PrefabParticleSystemReceiver();

        BehaviourBridge::applyProperties($behaviour, [
            'hitSparkPrefab' => [
                '__lengaRefKind' => 'Component',
                'componentType' => 'ParticleSystem',
                'componentSceneId' => 'hit-sparks-particles',
                'gameObject' => [
                    '__lengaRefKind' => 'PrefabAsset',
                    'assetPath' => 'Assets/Prefabs/HitSparks.prefab.json',
                    'name' => 'Hit Sparks',
                ],
            ],
        ]);

        self::assertInstanceOf(ParticleSystem::class, $behaviour->hitSparkPrefab);
        self::assertSame('ParticleSystem', $behaviour->hitSparkPrefab->type);
        self::assertSame('hit-sparks-particles', $behaviour->hitSparkPrefab->getSceneComponentId());
        self::assertSame('Assets/Prefabs/HitSparks.prefab.json', $behaviour->hitSparkPrefab->gameObject->prefabAssetPath);
    }

    public function testInstantiateOptionsSerializeWorldSpawnData(): void
    {
        $options = InstantiateOptions::at(
            new Vector3(1.0, 2.0, 3.0),
            new Vector3(0.0, 90.0, 0.0),
            name: 'Hit Spark',
        );

        $native = $options->toNativeArray();

        self::assertSame('Hit Spark', $native['name']);
        self::assertSame(['x' => 1.0, 'y' => 2.0, 'z' => 3.0], $native['position']);
        self::assertIsArray($native['rotation']);
        self::assertArrayHasKey('w', $native['rotation']);
        self::assertFalse($native['parentSpecified']);
    }

    public function testInstantiateOptionsAcceptQuaternionRotation(): void
    {
        $rotation = Quaternion::fromEulerAngles(new Vector3(0.0, 45.0, 0.0));
        $options = InstantiateOptions::fromArray([
            'name' => 'Projectile',
            'position' => ['x' => 4.0, 'y' => 5.0, 'z' => 6.0],
            'rotation' => $rotation->__serialize(),
        ]);

        $native = $options->toNativeArray();

        self::assertSame('Projectile', $native['name']);
        self::assertSame(['x' => 4.0, 'y' => 5.0, 'z' => 6.0], $native['position']);
        self::assertEqualsWithDelta($rotation->normalized->w, $native['rotation']['w'], 0.000001);
    }
}
