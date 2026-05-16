<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\CapsuleRenderer;
use Lenga\Engine\Core\CubeRenderer;
use Lenga\Engine\Core\CylinderRenderer;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\MeshRenderer;
use Lenga\Engine\Core\ModelRenderer;
use Lenga\Engine\Core\PlaneRenderer;
use Lenga\Engine\Core\RectangleRenderer;
use Lenga\Engine\Core\Renderer;
use Lenga\Engine\Core\SphereRenderer;
use Lenga\Engine\Core\SpriteRenderer;
use Lenga\Engine\Core\TrailRenderer;
use Lenga\Engine\Interfaces\RendererInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RendererInheritanceTest extends TestCase
{
    public function testRendererBaseIsAbstract(): void
    {
        self::assertTrue((new ReflectionClass(Renderer::class))->isAbstract());
    }

    public function testConcreteRenderersShareTheRendererBaseContract(): void
    {
        $gameObject = new GameObject('Renderable');
        $rendererClasses = [
            CapsuleRenderer::class,
            CubeRenderer::class,
            CylinderRenderer::class,
            MeshRenderer::class,
            ModelRenderer::class,
            PlaneRenderer::class,
            RectangleRenderer::class,
            SphereRenderer::class,
            SpriteRenderer::class,
            TrailRenderer::class,
        ];

        foreach ($rendererClasses as $index => $rendererClass) {
            $renderer = new $rendererClass($gameObject, $index + 1);

            self::assertInstanceOf(Renderer::class, $renderer);
            self::assertInstanceOf(RendererInterface::class, $renderer);
        }
    }
}
