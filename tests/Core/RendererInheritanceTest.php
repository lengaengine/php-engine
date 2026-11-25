<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use Lenga\Engine\Core\CapsuleRenderer;
use Lenga\Engine\Core\CircleRenderer;
use Lenga\Engine\Core\CubeRenderer;
use Lenga\Engine\Core\CylinderRenderer;
use Lenga\Engine\Core\EllipseRenderer;
use Lenga\Engine\Core\GameObject;
use Lenga\Engine\Core\LineRenderer2D;
use Lenga\Engine\Core\MeshRenderer;
use Lenga\Engine\Core\ModelRenderer;
use Lenga\Engine\Core\PlaneRenderer;
use Lenga\Engine\Core\PolygonRenderer;
use Lenga\Engine\Core\RectangleRenderer;
use Lenga\Engine\Core\Renderer;
use Lenga\Engine\Core\Renderer2D;
use Lenga\Engine\Core\RingRenderer;
use Lenga\Engine\Core\RoundedRectangleRenderer;
use Lenga\Engine\Core\SphereRenderer;
use Lenga\Engine\Core\SpriteRenderer;
use Lenga\Engine\Core\TrailRenderer;
use Lenga\Engine\Core\TriangleRenderer2D;
use Lenga\Engine\Interfaces\RendererInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RendererInheritanceTest extends TestCase
{
    public function testRendererBaseIsAbstract(): void
    {
        self::assertTrue((new ReflectionClass(Renderer::class))->isAbstract());
        self::assertTrue((new ReflectionClass(Renderer2D::class))->isAbstract());
    }

    public function testConcreteRenderersShareTheRendererBaseContract(): void
    {
        $gameObject = new GameObject('Renderable');
        $renderer3DClasses = [
            CapsuleRenderer::class,
            CubeRenderer::class,
            CylinderRenderer::class,
            MeshRenderer::class,
            ModelRenderer::class,
            PlaneRenderer::class,
            SphereRenderer::class,
            TrailRenderer::class,
        ];
        $renderer2DClasses = [
            CircleRenderer::class,
            EllipseRenderer::class,
            LineRenderer2D::class,
            PolygonRenderer::class,
            RectangleRenderer::class,
            RingRenderer::class,
            RoundedRectangleRenderer::class,
            SpriteRenderer::class,
            TriangleRenderer2D::class,
        ];

        $index = 1;
        foreach ($renderer3DClasses as $rendererClass) {
            $renderer = new $rendererClass($gameObject, $index++);

            self::assertInstanceOf(Renderer::class, $renderer);
            self::assertInstanceOf(RendererInterface::class, $renderer);
            self::assertNotInstanceOf(Renderer2D::class, $renderer);
        }

        foreach ($renderer2DClasses as $rendererClass) {
            $renderer = new $rendererClass($gameObject, $index++);

            self::assertInstanceOf(Renderer2D::class, $renderer);
            self::assertInstanceOf(Renderer::class, $renderer);
            self::assertInstanceOf(RendererInterface::class, $renderer);
        }
    }
}
