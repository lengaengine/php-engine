<?php

declare(strict_types=1);

namespace {
    /**
     * @return array{id: int, type: string, gameObjectId: int}|false
     */
    function lenga_internal_game_object_get_component_by_id(
        int $gameObjectId,
        string $nativeType,
        ?string $scriptClass = null,
    ): array|false {
        $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'][] = [
            'gameObjectId' => $gameObjectId,
            'nativeType' => $nativeType,
            'scriptClass' => $scriptClass,
        ];

        $resultType = $GLOBALS['lenga_component_lookup_inheritance_test_state']['results'][$nativeType] ?? null;
        if (!is_string($resultType)) {
            return false;
        }

        return [
            'id' => ++$GLOBALS['lenga_component_lookup_inheritance_test_state']['nextComponentId'],
            'type' => $resultType,
            'gameObjectId' => $gameObjectId,
        ];
    }

    /**
     * @return array{name?: string, tag?: string, layer?: int, id?: int, activeSelf?: bool, activeInHierarchy?: bool, transformId?: int|null, sceneObjectId?: string}|false
     */
    function lenga_internal_game_object_lookup_by_id(int $gameObjectId): array|false
    {
        return $GLOBALS['lenga_component_lookup_inheritance_test_state']['gameObjects'][$gameObjectId] ?? false;
    }

    /**
     * @return list<array{id: int, type: string, gameObjectId: int, sceneComponentId?: string}>
     */
    function lenga_internal_game_object_get_components_by_id(
        int $gameObjectId,
        ?string $nativeType = null,
        ?string $scriptClass = null,
    ): array {
        $GLOBALS['lenga_component_lookup_inheritance_test_state']['componentListCalls'][] = [
            'gameObjectId' => $gameObjectId,
            'nativeType' => $nativeType,
            'scriptClass' => $scriptClass,
        ];

        $key = $scriptClass ?? $nativeType ?? '*';
        return $GLOBALS['lenga_component_lookup_inheritance_test_state']['componentLists'][$key] ?? [];
    }
}

namespace Lenga\Engine\Core {
    final class ConventionOnlyLookupComponent extends Component
    {
        public function __construct(GameObject $gameObject, int $componentId)
        {
            parent::__construct($gameObject, $componentId, 'ConventionOnlyLookupComponent');
        }
    }
}

namespace Lenga\Engine\Tests\Fixtures {
    use Lenga\Engine\Core\Behaviour;
    use Lenga\Engine\Core\Component;
    use Lenga\Engine\Core\GameObject;
    use Lenga\Engine\Interfaces\ComponentInterface;
    use Lenga\Engine\Interfaces\RendererInterface;

    final class RegisteredLookupComponent extends Component
    {
        public function __construct(GameObject $gameObject, int $componentId)
        {
            parent::__construct($gameObject, $componentId, 'RegisteredLookupComponent');
        }
    }

    final class ComponentReferenceHydrationBehaviour extends Behaviour
    {
        public Component $component;
        public ComponentInterface $componentInterface;
        public RendererInterface $rendererInterface;
    }
}

namespace Lenga\Engine\Tests\Core {
    use Lenga\Engine\Core\Component;
    use Lenga\Engine\Core\GameObject;
    use Lenga\Engine\Core\Renderer;
    use Lenga\Engine\Core\SpriteRenderer;
    use Lenga\Engine\Core\TrailRenderer;
    use Lenga\Engine\Interfaces\ComponentInterface;
    use Lenga\Engine\Interfaces\RendererInterface;
    use Lenga\Engine\Internal\BehaviourBridge;
    use Lenga\Engine\Tests\Fixtures\ComponentReferenceHydrationBehaviour;
    use Lenga\Engine\Tests\Fixtures\RegisteredLookupComponent;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;

    final class ComponentLookupInheritanceTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state'] = [
                'calls' => [],
                'componentListCalls' => [],
                'componentLists' => [],
                'gameObjects' => [],
                'nextComponentId' => 100,
                'results' => [],
            ];
        }

        public function testConcreteTrailRendererLookupUsesExactNativeType(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['TrailRenderer'] = 'TrailRenderer';

            $gameObject = new GameObject('Ball', instanceId: 42);
            $component = $gameObject->getComponent(TrailRenderer::class);

            self::assertInstanceOf(TrailRenderer::class, $component);
            self::assertSame(
                [
                    [
                        'gameObjectId' => 42,
                        'nativeType' => 'TrailRenderer',
                        'scriptClass' => null,
                    ],
                ],
                $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'],
            );
        }

        /**
         * @param class-string<object> $componentClass
         */
        #[DataProvider('concreteComponentWrapperProvider')]
        public function testConcreteComponentClassLookupUsesExactNativeType(string $componentClass, string $nativeType): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results'][$nativeType] = $nativeType;

            $gameObject = new GameObject('Component Host', instanceId: 42);
            $component = $gameObject->getComponent($componentClass);

            self::assertInstanceOf($componentClass, $component);
            self::assertSame(
                [
                    [
                        'gameObjectId' => 42,
                        'nativeType' => $nativeType,
                        'scriptClass' => null,
                    ],
                ],
                $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'],
            );
        }

        public function testRendererBaseLookupCanReturnConcreteTrailRenderer(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['Renderer'] = 'TrailRenderer';

            $gameObject = new GameObject('Ball', instanceId: 42);
            $component = $gameObject->getComponent(Renderer::class);

            self::assertInstanceOf(TrailRenderer::class, $component);
            self::assertSame('Renderer', $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'][0]['nativeType']);
        }

        public function testRendererInterfaceLookupCanReturnConcreteTrailRenderer(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['Renderer'] = 'TrailRenderer';

            $gameObject = new GameObject('Ball', instanceId: 42);
            $component = $gameObject->getComponent(RendererInterface::class);

            self::assertInstanceOf(TrailRenderer::class, $component);
            self::assertSame('Renderer', $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'][0]['nativeType']);
        }

        public function testComponentBaseLookupCanReturnConcreteTrailRenderer(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['Component'] = 'TrailRenderer';

            $gameObject = new GameObject('Ball', instanceId: 42);
            $component = $gameObject->getComponent(Component::class);

            self::assertInstanceOf(TrailRenderer::class, $component);
            self::assertSame('Component', $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'][0]['nativeType']);
        }

        public function testComponentInterfaceLookupCanReturnConcreteTrailRenderer(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['Component'] = 'TrailRenderer';

            $gameObject = new GameObject('Ball', instanceId: 42);
            $component = $gameObject->getComponent(ComponentInterface::class);

            self::assertInstanceOf(TrailRenderer::class, $component);
            self::assertSame('Component', $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'][0]['nativeType']);
        }

        public function testSerializedComponentReferencesHydrateConcreteAndInterfaceTypedProperties(): void
        {
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['gameObjects'][42] = [
                'id' => 42,
                'name' => 'Emitter',
                'sceneObjectId' => 'emitter',
                'transformId' => 24,
            ];
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['componentLists']['SpriteRenderer'] = [
                [
                    'id' => 201,
                    'type' => 'SpriteRenderer',
                    'gameObjectId' => 42,
                    'sceneComponentId' => 'sprite-renderer',
                ],
            ];
            $GLOBALS['lenga_component_lookup_inheritance_test_state']['componentLists']['Renderer'] =
                $GLOBALS['lenga_component_lookup_inheritance_test_state']['componentLists']['SpriteRenderer'];

            $behaviour = new ComponentReferenceHydrationBehaviour();
            $reference = [
                '__lengaRefKind' => 'Component',
                'componentType' => 'SpriteRenderer',
                'componentSceneId' => 'sprite-renderer',
                'gameObject' => [
                    '__lengaRefKind' => 'GameObject',
                    'sceneObjectId' => '',
                    'instanceId' => 42,
                    'name' => 'Emitter',
                ],
                'instanceId' => 201,
            ];

            BehaviourBridge::applyProperties($behaviour, [
                'component' => $reference,
                'componentInterface' => $reference,
                'rendererInterface' => [
                    ...$reference,
                    'componentType' => 'Renderer',
                ],
            ]);

            self::assertInstanceOf(SpriteRenderer::class, $behaviour->component);
            self::assertInstanceOf(SpriteRenderer::class, $behaviour->componentInterface);
            self::assertInstanceOf(SpriteRenderer::class, $behaviour->rendererInterface);
            self::assertInstanceOf(RendererInterface::class, $behaviour->rendererInterface);
        }

        public function testRegisteredComponentWrapperSupportsFutureNonConventionalNamespaces(): void
        {
            GameObject::registerComponentWrapper('RegisteredLookupComponent', RegisteredLookupComponent::class);

            $GLOBALS['lenga_component_lookup_inheritance_test_state']['results']['RegisteredLookupComponent'] = 'RegisteredLookupComponent';

            $gameObject = new GameObject('Component Host', instanceId: 42);
            $component = $gameObject->getComponent(RegisteredLookupComponent::class);

            self::assertInstanceOf(RegisteredLookupComponent::class, $component);
            self::assertSame(
                [
                    [
                        'gameObjectId' => 42,
                        'nativeType' => 'RegisteredLookupComponent',
                        'scriptClass' => null,
                    ],
                ],
                $GLOBALS['lenga_component_lookup_inheritance_test_state']['calls'],
            );
        }

        public function testComponentLookupDocumentsClassStringSpecificReturnTypes(): void
        {
            $reflection = new \ReflectionMethod(GameObject::class, 'getComponent');
            $docComment = $reflection->getDocComment();

            self::assertIsString($docComment);
            self::assertStringContainsString('@template TComponent of object', $docComment);
            self::assertStringContainsString('@param class-string<TComponent>|non-empty-string $type', $docComment);
            self::assertStringContainsString('@return TComponent|null', $docComment);
        }

        /**
         * @return iterable<string, array{class-string<object>, string}>
         */
        public static function concreteComponentWrapperProvider(): iterable
        {
            yield 'AreaEffector2D' => [\Lenga\Engine\Core\AreaEffector2D::class, 'AreaEffector2D'];
            yield 'AudioListener' => [\Lenga\Engine\Audio\AudioListener::class, 'AudioListener'];
            yield 'AudioSource' => [\Lenga\Engine\Audio\AudioSource::class, 'AudioSource'];
            yield 'BoxCollider2D' => [\Lenga\Engine\Core\BoxCollider2D::class, 'BoxCollider2D'];
            yield 'BoxCollider3D' => [\Lenga\Engine\Core\BoxCollider3D::class, 'BoxCollider3D'];
            yield 'BuoyancyEffector2D' => [\Lenga\Engine\Core\BuoyancyEffector2D::class, 'BuoyancyEffector2D'];
            yield 'Camera' => [\Lenga\Engine\Core\Camera::class, 'Camera'];
            yield 'CapsuleCollider3D' => [\Lenga\Engine\Core\CapsuleCollider3D::class, 'CapsuleCollider3D'];
            yield 'CapsuleRenderer' => [\Lenga\Engine\Core\CapsuleRenderer::class, 'CapsuleRenderer'];
            yield 'CharacterController' => [\Lenga\Engine\Core\CharacterController::class, 'CharacterController'];
            yield 'CircleCollider2D' => [\Lenga\Engine\Core\CircleCollider2D::class, 'CircleCollider2D'];
            yield 'CircleRenderer' => [\Lenga\Engine\Core\CircleRenderer::class, 'CircleRenderer'];
            yield 'ConventionOnlyLookupComponent' => [\Lenga\Engine\Core\ConventionOnlyLookupComponent::class, 'ConventionOnlyLookupComponent'];
            yield 'CubeRenderer' => [\Lenga\Engine\Core\CubeRenderer::class, 'CubeRenderer'];
            yield 'CylinderCollider3D' => [\Lenga\Engine\Core\CylinderCollider3D::class, 'CylinderCollider3D'];
            yield 'CylinderRenderer' => [\Lenga\Engine\Core\CylinderRenderer::class, 'CylinderRenderer'];
            yield 'DirectionalLight' => [\Lenga\Engine\Core\DirectionalLight::class, 'DirectionalLight'];
            yield 'DistanceJoint2D' => [\Lenga\Engine\Core\DistanceJoint2D::class, 'DistanceJoint2D'];
            yield 'EllipseRenderer' => [\Lenga\Engine\Core\EllipseRenderer::class, 'EllipseRenderer'];
            yield 'FixedJoint2D' => [\Lenga\Engine\Core\FixedJoint2D::class, 'FixedJoint2D'];
            yield 'GlobalLight2D' => [\Lenga\Engine\Core\GlobalLight2D::class, 'GlobalLight2D'];
            yield 'HingeJoint2D' => [\Lenga\Engine\Core\HingeJoint2D::class, 'HingeJoint2D'];
            yield 'LightOccluder2D' => [\Lenga\Engine\Core\LightOccluder2D::class, 'LightOccluder2D'];
            yield 'LineRenderer2D' => [\Lenga\Engine\Core\LineRenderer2D::class, 'LineRenderer2D'];
            yield 'MeshCollider3D' => [\Lenga\Engine\Core\MeshCollider3D::class, 'MeshCollider3D'];
            yield 'MeshRenderer' => [\Lenga\Engine\Core\MeshRenderer::class, 'MeshRenderer'];
            yield 'ModelRenderer' => [\Lenga\Engine\Core\ModelRenderer::class, 'ModelRenderer'];
            yield 'ParticleSystem' => [\Lenga\Engine\Core\ParticleSystem::class, 'ParticleSystem'];
            yield 'PlaneRenderer' => [\Lenga\Engine\Core\PlaneRenderer::class, 'PlaneRenderer'];
            yield 'PlatformEffector2D' => [\Lenga\Engine\Core\PlatformEffector2D::class, 'PlatformEffector2D'];
            yield 'PointEffector2D' => [\Lenga\Engine\Core\PointEffector2D::class, 'PointEffector2D'];
            yield 'PointLight' => [\Lenga\Engine\Core\PointLight::class, 'PointLight'];
            yield 'PointLight2D' => [\Lenga\Engine\Core\PointLight2D::class, 'PointLight2D'];
            yield 'PolygonRenderer' => [\Lenga\Engine\Core\PolygonRenderer::class, 'PolygonRenderer'];
            yield 'RectangleRenderer' => [\Lenga\Engine\Core\RectangleRenderer::class, 'RectangleRenderer'];
            yield 'Rigidbody2D' => [\Lenga\Engine\Core\Rigidbody2D::class, 'Rigidbody2D'];
            yield 'Rigidbody3D' => [\Lenga\Engine\Core\Rigidbody3D::class, 'Rigidbody3D'];
            yield 'RingRenderer' => [\Lenga\Engine\Core\RingRenderer::class, 'RingRenderer'];
            yield 'RoundedRectangleRenderer' => [\Lenga\Engine\Core\RoundedRectangleRenderer::class, 'RoundedRectangleRenderer'];
            yield 'SliderJoint2D' => [\Lenga\Engine\Core\SliderJoint2D::class, 'SliderJoint2D'];
            yield 'SphereCollider3D' => [\Lenga\Engine\Core\SphereCollider3D::class, 'SphereCollider3D'];
            yield 'SphereRenderer' => [\Lenga\Engine\Core\SphereRenderer::class, 'SphereRenderer'];
            yield 'SpriteAnimation' => [\Lenga\Engine\Core\SpriteAnimation::class, 'SpriteAnimation'];
            yield 'SpriteLight2D' => [\Lenga\Engine\Core\SpriteLight2D::class, 'SpriteLight2D'];
            yield 'SpriteRenderer' => [\Lenga\Engine\Core\SpriteRenderer::class, 'SpriteRenderer'];
            yield 'SurfaceEffector2D' => [\Lenga\Engine\Core\SurfaceEffector2D::class, 'SurfaceEffector2D'];
            yield 'Tilemap' => [\Lenga\Engine\Core\Tilemap::class, 'Tilemap'];
            yield 'TrailRenderer' => [\Lenga\Engine\Core\TrailRenderer::class, 'TrailRenderer'];
            yield 'TriangleRenderer2D' => [\Lenga\Engine\Core\TriangleRenderer2D::class, 'TriangleRenderer2D'];
        }
    }
}
