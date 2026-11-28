<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/NativeUiTestDoubles.php';

    /**
     * @return array<string, mixed>|false
     */
    function lenga_internal_ui_canvas_create_toggle(int $canvasId, string $name, ?int $parentId = null): array|false
    {
        $state = &$GLOBALS['lenga_ui_test_state'];
        $id = ++$state['nextId'];
        $state['elements'][$id] = ['id' => $id, 'type' => 'Toggle', 'name' => $name, 'canvasId' => $canvasId];
        $state['created'][] = compact('canvasId', 'name', 'parentId');

        return ['id' => $id, 'type' => 'Toggle', 'name' => $name, 'canvasId' => $canvasId];
    }

    lenga_ui_test_declare_setters('toggle', [
        'text', 'font_size', 'font_path', 'is_on', 'interactable', 'text_color', 'background_color',
        'background_image', 'background_size', 'checkmark_color', 'checkmark_image', 'checkmark_size',
        'disabled_color', 'disabled_image', 'disabled_text_color', 'disabled_checkmark_color',
    ]);
}

namespace Lenga\Engine\Tests\UI {
    use Lenga\Engine\Core\Color;
    use Lenga\Engine\Core\Vector2;
    use Lenga\Engine\UI\Canvas;
    use Lenga\Engine\UI\Toggle;
    use Lenga\Engine\UI\UIElement;
    use PHPUnit\Framework\TestCase;

    final class ToggleTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_ui_test_state'] = ['nextId' => 60, 'elements' => [], 'calls' => [], 'created' => []];
            UIElement::releaseNativeElements([11, 61]);
        }

        private function makeToggle(array $state = []): Toggle
        {
            $GLOBALS['lenga_ui_test_state']['elements'][11] = ['id' => 11, 'type' => 'Toggle', 'name' => 'Music'] + $state;

            $toggle = UIElement::fromNativeLookupData(['id' => 11, 'type' => 'Toggle', 'name' => 'Music', 'canvasId' => 3]);
            self::assertInstanceOf(Toggle::class, $toggle);

            return $toggle;
        }

        public function testCanvasCreatesAndFindsToggles(): void
        {
            $canvas = Canvas::fromNativeLookupData(['id' => 3, 'name' => 'Menu']);

            $toggle = $canvas->createToggle('Sound');

            self::assertInstanceOf(Toggle::class, $toggle);
            self::assertSame([['canvasId' => 3, 'name' => 'Sound', 'parentId' => null]], $GLOBALS['lenga_ui_test_state']['created']);
        }

        public function testUnsetValuesReadTheEngineDefaults(): void
        {
            $toggle = $this->makeToggle();

            self::assertFalse($toggle->isOn);
            self::assertTrue($toggle->interactable);
            self::assertSame(24.0, $toggle->fontSize);
            self::assertSame(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255], $toggle->textColor->toRGBA());
            self::assertSame(['r' => 34, 'g' => 197, 'b' => 155, 'a' => 255], $toggle->checkmarkColor->toRGBA());
            self::assertSame(['r' => 56, 'g' => 66, 'b' => 82, 'a' => 180], $toggle->disabledColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $toggle->disabledTextColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $toggle->disabledCheckmarkColor->toRGBA());
        }

        public function testValuesReadTheEngineState(): void
        {
            $toggle = $this->makeToggle([
                'text' => 'Music',
                'isOn' => true,
                'checkmarkSize' => ['x' => 12.0, 'y' => 10.0],
                'disabledTextColor' => ['r' => 1, 'g' => 2, 'b' => 3, 'a' => 4],
            ]);

            self::assertSame('Music', $toggle->text);
            self::assertTrue($toggle->isOn);
            self::assertSame(12.0, $toggle->checkmarkSize->x);
            self::assertSame(10.0, $toggle->checkmarkSize->y);
            self::assertSame(['r' => 1, 'g' => 2, 'b' => 3, 'a' => 4], $toggle->disabledTextColor->toRGBA());
        }

        public function testAssigningValuesSendsThemToTheEngine(): void
        {
            $toggle = $this->makeToggle();

            $toggle->isOn = true;
            $toggle->interactable = false;
            $toggle->backgroundSize = new Vector2(20.0, 18.0);
            $toggle->disabledTextColor = Color::fromRGBA(1, 2, 3, 4);
            $toggle->disabledCheckmarkColor = Color::fromRGBA(5, 6, 7, 8);

            self::assertSame(
                [
                    ['is_on', 11, true],
                    ['interactable', 11, false],
                    ['background_size', 11, 20.0, 18.0],
                    ['disabled_text_color', 11, 1, 2, 3, 4],
                    ['disabled_checkmark_color', 11, 5, 6, 7, 8],
                ],
                $GLOBALS['lenga_ui_test_state']['calls'],
            );
        }
    }
}
