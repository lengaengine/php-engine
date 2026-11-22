<?php

declare(strict_types=1);

namespace {
    /**
     * @return array<string, mixed>|false
     */
    function lenga_internal_camera_get_main(): array|false
    {
        $GLOBALS['lenga_camera_main_test_state']['calls']++;

        return $GLOBALS['lenga_camera_main_test_state']['main'];
    }
}

namespace Lenga\Engine\Tests\Core {
    use Lenga\Engine\Core\Camera;
    use PHPUnit\Framework\TestCase;
    use ReflectionClass;
    use ReflectionMethod;
    use ReflectionNamedType;
    use ReflectionProperty;

    final class CameraMainTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_camera_main_test_state'] = ['calls' => 0, 'main' => false];
        }

        public function testGetMainWrapsTheCameraTheEngineChose(): void
        {
            $this->engineChooses(cameraId: 71, gameObjectId: 7, sceneComponentId: 'camera-71');

            $camera = Camera::getMain();

            self::assertInstanceOf(Camera::class, $camera);
            self::assertSame(71, $camera->getInstanceId());
            self::assertSame('camera-71', $camera->getSceneComponentId());
            self::assertSame(7, $camera->gameObject->getInstanceId());
            self::assertSame(8, $camera->gameObject->transform->getNativeId());
        }

        public function testGetMainIsNullWhenNoCameraQualifies(): void
        {
            self::assertNull(Camera::getMain());
            self::assertSame(1, $GLOBALS['lenga_camera_main_test_state']['calls']);
        }

        public function testGetMainAsksTheEngineOnEveryCall(): void
        {
            $this->engineChooses(cameraId: 71, gameObjectId: 7, sceneComponentId: 'camera-71');
            self::assertSame(71, Camera::getMain()?->getInstanceId());

            $this->engineChooses(cameraId: 92, gameObjectId: 9, sceneComponentId: 'camera-92');
            self::assertSame(92, Camera::getMain()?->getInstanceId());

            $GLOBALS['lenga_camera_main_test_state']['main'] = false;
            self::assertNull(Camera::getMain());

            self::assertSame(3, $GLOBALS['lenga_camera_main_test_state']['calls']);
        }

        public function testGetMainIsAStaticGetterThatReturnsANullableCamera(): void
        {
            $method = new ReflectionMethod(Camera::class, 'getMain');
            $returnType = $method->getReturnType();

            self::assertTrue($method->isStatic());
            self::assertTrue($method->isPublic());
            self::assertSame(0, $method->getNumberOfParameters());
            self::assertInstanceOf(ReflectionNamedType::class, $returnType);
            self::assertSame(Camera::class, $returnType->getName());
            self::assertTrue($returnType->allowsNull());
        }

        public function testCameraKeepsNoStaticCopyOfTheMainCamera(): void
        {
            $staticProperties = array_map(
                static fn (ReflectionProperty $property): string => $property->getName(),
                (new ReflectionClass(Camera::class))->getProperties(ReflectionProperty::IS_STATIC),
            );

            self::assertSame([], $staticProperties);
        }

        private function engineChooses(int $cameraId, int $gameObjectId, string $sceneComponentId): void
        {
            $GLOBALS['lenga_camera_main_test_state']['main'] = [
                'id' => $cameraId,
                'gameObjectId' => $gameObjectId,
                'type' => 'Camera',
                'sceneComponentId' => $sceneComponentId,
                'gameObject' => [
                    'id' => $gameObjectId,
                    'name' => 'Main Camera',
                    'sceneObjectId' => 'object-' . $gameObjectId,
                    'tag' => 'MainCamera',
                    'layer' => 0,
                    'activeSelf' => true,
                    'activeInHierarchy' => true,
                    'transformId' => $gameObjectId + 1,
                ],
            ];
        }
    }
}
