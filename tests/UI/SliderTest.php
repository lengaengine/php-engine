<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/NativeUiTestDoubles.php';

    lenga_ui_test_declare_setters('slider', ['disabled_color', 'disabled_fill_color', 'disabled_handle_color']);
}

namespace Lenga\Engine\Tests\UI {
    use Lenga\Engine\Core\Color;
    use Lenga\Engine\UI\Slider;
    use Lenga\Engine\UI\UIElement;
    use PHPUnit\Framework\TestCase;

    final class SliderTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_ui_test_state'] = ['nextId' => 80, 'elements' => [], 'calls' => [], 'created' => []];
            UIElement::releaseNativeElements([13]);
        }

        private function makeSlider(array $state = []): Slider
        {
            $GLOBALS['lenga_ui_test_state']['elements'][13] = ['id' => 13, 'type' => 'Slider', 'name' => 'Volume'] + $state;

            $slider = UIElement::fromNativeLookupData(['id' => 13, 'type' => 'Slider', 'name' => 'Volume', 'canvasId' => 3]);
            self::assertInstanceOf(Slider::class, $slider);

            return $slider;
        }

        public function testDisabledColorsDefaultToTheEngineDefaults(): void
        {
            $slider = $this->makeSlider();

            self::assertSame(['r' => 56, 'g' => 66, 'b' => 82, 'a' => 180], $slider->disabledColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $slider->disabledFillColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $slider->disabledHandleColor->toRGBA());
        }

        public function testAssigningDisabledColorsSendsThemToTheEngine(): void
        {
            $slider = $this->makeSlider();

            $slider->disabledColor = Color::fromRGBA(1, 2, 3, 4);
            $slider->disabledFillColor = Color::fromRGBA(5, 6, 7, 8);
            $slider->disabledHandleColor = Color::fromRGBA(9, 10, 11, 12);

            self::assertSame(
                [
                    ['disabled_color', 13, 1, 2, 3, 4],
                    ['disabled_fill_color', 13, 5, 6, 7, 8],
                    ['disabled_handle_color', 13, 9, 10, 11, 12],
                ],
                $GLOBALS['lenga_ui_test_state']['calls'],
            );
        }
    }
}
