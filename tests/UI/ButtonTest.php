<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/NativeUiTestDoubles.php';

    lenga_ui_test_declare_setters('button', ['disabled_text_color', 'disabled_outline_color']);
}

namespace Lenga\Engine\Tests\UI {
    use Lenga\Engine\Core\Color;
    use Lenga\Engine\UI\Button;
    use Lenga\Engine\UI\UIElement;
    use PHPUnit\Framework\TestCase;

    final class ButtonTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_ui_test_state'] = ['nextId' => 40, 'elements' => [], 'calls' => [], 'created' => []];
            UIElement::releaseNativeElements([9]);
        }

        private function makeButton(array $state = []): Button
        {
            $GLOBALS['lenga_ui_test_state']['elements'][9] = ['id' => 9, 'type' => 'Button', 'name' => 'Play'] + $state;

            $button = UIElement::fromNativeLookupData(['id' => 9, 'type' => 'Button', 'name' => 'Play', 'canvasId' => 3]);
            self::assertInstanceOf(Button::class, $button);

            return $button;
        }

        public function testDisabledContentColorsDefaultToTheDimmedGrey(): void
        {
            $button = $this->makeButton();

            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $button->disabledTextColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $button->disabledOutlineColor->toRGBA());
        }

        public function testDisabledContentColorsReadTheEngineState(): void
        {
            $button = $this->makeButton([
                'disabledTextColor' => ['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40],
                'disabledOutlineColor' => ['r' => 50, 'g' => 60, 'b' => 70, 'a' => 80],
            ]);

            self::assertSame(['r' => 10, 'g' => 20, 'b' => 30, 'a' => 40], $button->disabledTextColor->toRGBA());
            self::assertSame(['r' => 50, 'g' => 60, 'b' => 70, 'a' => 80], $button->disabledOutlineColor->toRGBA());
        }

        public function testAssigningDisabledContentColorsSendsThemToTheEngine(): void
        {
            $button = $this->makeButton();

            $button->disabledTextColor = Color::fromRGBA(1, 2, 3, 4);
            $button->disabledOutlineColor = Color::fromRGBA(5, 6, 7, 8);

            self::assertSame(
                [
                    ['disabled_text_color', 9, 1, 2, 3, 4],
                    ['disabled_outline_color', 9, 5, 6, 7, 8],
                ],
                $GLOBALS['lenga_ui_test_state']['calls'],
            );
        }
    }
}
