<?php

declare(strict_types=1);

namespace Lenga\Engine\Attributes;

use Attribute;

/**
 * Edits a string field in a multi-line text box in the Inspector.
 *
 * Use it for longer text such as dialogue lines or descriptions:
 *
 * ```php
 * use Lenga\Engine\Attributes\TextArea;
 * use Lenga\Engine\Core\Behaviour;
 *
 * final class DialogueTrigger extends Behaviour
 * {
 *     #[TextArea(3, 8)]
 *     public string $dialogueText = 'Press Interact to continue.';
 * }
 * ```
 *
 * The text box is as tall as `$minLines` lines, at least one. The editor
 * reads the attribute from the script's source, so pass the line counts as
 * plain numbers in order. Named arguments such as `minLines: 3` are not
 * read, and the defaults are used instead.
 *
 * `TextAreaAttribute` is another name for this attribute. Write `TextArea`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class TextArea
{
    /**
     * Creates the attribute with the size of the text box.
     *
     * @param int $minLines Number of lines the text box shows. Defaults to `3`.
     * @param int $maxLines Largest number of lines for the text box. Values below `$minLines` are raised to it. Defaults to `10`.
     */
    public function __construct(
        public readonly int $minLines = 3,
        public readonly int $maxLines = 10,
    )
    {
    }
}
