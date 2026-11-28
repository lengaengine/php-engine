<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/NativeUiTestDoubles.php';

    /**
     * @return array<string, mixed>|false
     */
    function lenga_internal_ui_canvas_create_input_field(int $canvasId, string $name, ?int $parentId = null): array|false
    {
        $state = &$GLOBALS['lenga_ui_test_state'];
        $id = ++$state['nextId'];
        $state['elements'][$id] = ['id' => $id, 'type' => 'InputField', 'name' => $name, 'canvasId' => $canvasId];
        $state['created'][] = compact('canvasId', 'name', 'parentId');

        return ['id' => $id, 'type' => 'InputField', 'name' => $name, 'canvasId' => $canvasId];
    }

    lenga_ui_test_declare_setters('input_field', [
        'text', 'placeholder', 'font_path', 'content_type', 'background_image', 'font_size', 'caret_width',
        'caret_blink_rate', 'outline_width', 'character_limit', 'caret_position', 'interactable',
        'restore_on_cancel', 'editing', 'text_color', 'placeholder_color', 'caret_color', 'background_color',
        'hover_color', 'editing_color', 'disabled_color', 'outline_color', 'mask_character',
        'selection_anchor', 'selection_color', 'disabled_text_color', 'disabled_placeholder_color',
        'disabled_outline_color',
    ]);
}

namespace Lenga\Engine\Tests\UI {
    use Lenga\Engine\Core\Color;
    use Lenga\Engine\Enumerations\InputFieldContentType;
    use Lenga\Engine\UI\Canvas;
    use Lenga\Engine\UI\InputField;
    use InvalidArgumentException;
    use Lenga\Engine\UI\UIElement;
    use PHPUnit\Framework\TestCase;

    final class InputFieldTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['lenga_ui_test_state'] = ['nextId' => 40, 'elements' => [], 'calls' => [], 'created' => []];
            UIElement::releaseNativeElements([7, 41, 42]);
        }

        private function makeField(array $state = []): InputField
        {
            $GLOBALS['lenga_ui_test_state']['elements'][7] = ['id' => 7, 'type' => 'InputField', 'name' => 'Name'] + $state;

            $field = UIElement::fromNativeLookupData(['id' => 7, 'type' => 'InputField', 'name' => 'Name', 'canvasId' => 3]);
            self::assertInstanceOf(InputField::class, $field);

            return $field;
        }

        /**
         * @return list<array<int, mixed>>
         */
        private function calls(): array
        {
            return $GLOBALS['lenga_ui_test_state']['calls'];
        }

        public function testNativeLookupDataOfTypeInputFieldBuildsAnInputField(): void
        {
            self::assertSame(7, $this->makeField()->getId());
        }

        public function testCanvasCreatesInputFieldsUnderAParent(): void
        {
            $canvas = Canvas::fromNativeLookupData(['id' => 3, 'name' => 'Menu']);
            $parent = $this->makeField();

            $field = $canvas->createInputField('Player Name', $parent);

            self::assertInstanceOf(InputField::class, $field);
            self::assertSame(
                [['canvasId' => 3, 'name' => 'Player Name', 'parentId' => 7]],
                $GLOBALS['lenga_ui_test_state']['created'],
            );
        }

        public function testValuesReadFromNativeState(): void
        {
            $field = $this->makeField([
                'text' => 'Ada99',
                'placeholder' => 'Your name',
                'characterLimit' => 16,
                'contentType' => 'Alphanumeric',
                'caretPosition' => 3,
                'editing' => true,
                'restoreOnCancel' => false,
                'valueChangedThisFrame' => true,
                'submittedThisFrame' => true,
                'cancelledThisFrame' => false,
                'editEndedThisFrame' => true,
                'caretBlinkRate' => 0.0,
                'textColor' => ['r' => 10, 'g' => 20, 'b' => 30, 'a' => 255],
            ]);

            self::assertSame('Ada99', $field->text);
            self::assertSame('Your name', $field->placeholder);
            self::assertSame(16, $field->characterLimit);
            self::assertSame(InputFieldContentType::Alphanumeric, $field->contentType);
            self::assertSame(3, $field->caretPosition);
            self::assertTrue($field->editing);
            self::assertFalse($field->restoreOnCancel);
            self::assertTrue($field->valueChangedThisFrame);
            self::assertTrue($field->submittedThisFrame);
            self::assertFalse($field->cancelledThisFrame);
            self::assertTrue($field->editEndedThisFrame);
            self::assertSame(0.0, $field->caretBlinkRate);
            self::assertSame(['r' => 10, 'g' => 20, 'b' => 30, 'a' => 255], $field->textColor->toRGBA());
        }

        public function testDefaultsWhenNativeStateLeavesValuesOut(): void
        {
            $field = $this->makeField();

            self::assertSame('', $field->text);
            self::assertSame(0, $field->characterLimit);
            self::assertSame(InputFieldContentType::Standard, $field->contentType);
            self::assertFalse($field->editing);
            self::assertTrue($field->restoreOnCancel);
            self::assertTrue($field->interactable);
            self::assertSame(0.85, $field->caretBlinkRate);
            self::assertSame(1.0, $field->outlineWidth);
        }

        public function testUnknownContentTypeReadsAsStandard(): void
        {
            self::assertSame(InputFieldContentType::Standard, $this->makeField(['contentType' => 'Hexadecimal'])->contentType);
        }

        public function testSettersSendValuesToTheEngine(): void
        {
            $field = $this->makeField();

            $field->text = 'Grace';
            $field->placeholder = 'Name';
            $field->characterLimit = 16;
            $field->contentType = InputFieldContentType::Alphanumeric;
            $field->caretPosition = 2;
            $field->restoreOnCancel = false;
            $field->interactable = false;
            $field->fontSize = 30.0;
            $field->fontPath = 'Fonts/ui.ttf';
            $field->backgroundImage = 'Images/field.png';
            $field->caretColor = Color::fromRGBA(1, 2, 3, 4);

            self::assertSame([
                ['text', 7, 'Grace'],
                ['placeholder', 7, 'Name'],
                ['character_limit', 7, 16],
                ['content_type', 7, 'Alphanumeric'],
                ['caret_position', 7, 2],
                ['restore_on_cancel', 7, false],
                ['interactable', 7, false],
                ['font_size', 7, 30.0],
                ['font_path', 7, 'Fonts/ui.ttf'],
                ['background_image', 7, 'Images/field.png'],
                ['caret_color', 7, 1, 2, 3, 4],
            ], $this->calls());
        }

        public function testNegativeCountsAndWidthsAreClampedBeforeTheEngineSeesThem(): void
        {
            $field = $this->makeField();

            $field->characterLimit = -5;
            $field->caretPosition = -1;
            $field->caretWidth = 0.0;
            $field->caretBlinkRate = -2.0;
            $field->outlineWidth = -1.0;

            self::assertSame([
                ['character_limit', 7, 0],
                ['caret_position', 7, 0],
                ['caret_width', 7, 1.0],
                ['caret_blink_rate', 7, 0.0],
                ['outline_width', 7, 0.0],
            ], $this->calls());
        }

        public function testEveryLookupOfAnElementGivesTheSameObject(): void
        {
            $field = $this->makeField();
            $again = UIElement::fromNativeLookupData(['id' => 7, 'type' => 'InputField', 'name' => 'Name']);

            self::assertSame($field, $again);
        }

        public function testReleasedElementsGetANewObject(): void
        {
            $field = $this->makeField();
            UIElement::releaseNativeElements([7]);

            self::assertNotSame($field, $this->makeField());
        }

        public function testEngineEventsReachTheFieldsSignals(): void
        {
            $field = $this->makeField(['text' => 'Ada99']);
            $heard = [];
            foreach (['valueChanged', 'submitted', 'cancelled', 'editEnded'] as $name) {
                $field->{$name}->add(static function (InputField $source) use (&$heard, $name, $field): void {
                    $heard[] = [$name, $source === $field, $source->text];
                });
            }

            UIElement::dispatchNativeEvent(7, 'valueChanged');
            UIElement::dispatchNativeEvent(7, 'submitted');
            UIElement::dispatchNativeEvent(7, 'cancelled');
            UIElement::dispatchNativeEvent(7, 'editEnded');
            UIElement::dispatchNativeEvent(7, 'unknown');
            UIElement::dispatchNativeEvent(99, 'submitted');

            self::assertSame([
                ['valueChanged', true, 'Ada99'],
                ['submitted', true, 'Ada99'],
                ['cancelled', true, 'Ada99'],
                ['editEnded', true, 'Ada99'],
            ], $heard);
        }

        public function testListenersStayWhenTheFieldIsLookedUpAgain(): void
        {
            $count = 0;
            $this->makeField()->submitted->add(static function () use (&$count): void {
                ++$count;
            });

            UIElement::dispatchNativeEvent(7, 'submitted');

            self::assertSame(1, $count);
        }

        public function testEveryContentTypeMatchesTheEnginesName(): void
        {
            self::assertSame(
                ['Standard', 'IntegerNumber', 'DecimalNumber', 'Alphanumeric', 'Name', 'EmailAddress', 'Password', 'Pin'],
                array_map(static fn (InputFieldContentType $type): string => $type->value, InputFieldContentType::cases()),
            );
            self::assertSame(InputFieldContentType::Pin, $this->makeField(['contentType' => 'Pin'])->contentType);
        }

        public function testMaskCharacterMustBeOnePrintableCharacter(): void
        {
            $field = $this->makeField(['maskCharacter' => '•']);
            self::assertSame('•', $field->maskCharacter);

            $field->maskCharacter = '#';
            self::assertSame([['mask_character', 7, '#']], $this->calls());

            $this->expectException(InvalidArgumentException::class);
            $field->maskCharacter = '##';
        }

        public function testMaskCharacterRefusesControlCharacters(): void
        {
            $this->expectException(InvalidArgumentException::class);
            $this->makeField()->maskCharacter = "\t";
        }

        public function testSelectionReadsFromTheAnchorToTheCaret(): void
        {
            $field = $this->makeField(['text' => 'Grüße Ada', 'selectionAnchor' => 9, 'caretPosition' => 6]);

            self::assertSame(9, $field->selectionAnchor);
            self::assertSame('Ada', $field->selectedText);
            self::assertSame('', $this->makeField(['text' => 'Ada', 'selectionAnchor' => 2, 'caretPosition' => 2])->selectedText);
        }

        public function testSelectAllPutsTheCaretAtTheEnd(): void
        {
            $field = $this->makeField(['text' => 'Grüße']);
            $field->selectAll();

            self::assertSame([['caret_position', 7, 5], ['selection_anchor', 7, 0]], $this->calls());
        }

        public function testBeginAndEndEditingAreActions(): void
        {
            $field = $this->makeField();

            $field->beginEditing();
            $field->endEditing();

            self::assertSame([['editing', 7, true], ['editing', 7, false]], $this->calls());
        }

        public function testDisabledContentColorsDefaultAndSendToTheEngine(): void
        {
            $field = $this->makeField();

            self::assertSame(['r' => 132, 'g' => 140, 'b' => 154, 'a' => 255], $field->placeholderColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $field->disabledTextColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $field->disabledPlaceholderColor->toRGBA());
            self::assertSame(['r' => 118, 'g' => 118, 'b' => 118, 'a' => 255], $field->disabledOutlineColor->toRGBA());

            $field->disabledTextColor = Color::fromRGBA(1, 2, 3, 4);
            $field->disabledPlaceholderColor = Color::fromRGBA(5, 6, 7, 8);
            $field->disabledOutlineColor = Color::fromRGBA(9, 10, 11, 12);

            self::assertSame(
                [
                    ['disabled_text_color', 7, 1, 2, 3, 4],
                    ['disabled_placeholder_color', 7, 5, 6, 7, 8],
                    ['disabled_outline_color', 7, 9, 10, 11, 12],
                ],
                $this->calls(),
            );
        }
    }
}
