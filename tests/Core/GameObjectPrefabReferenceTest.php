<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\ParticleSystem;
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
}
