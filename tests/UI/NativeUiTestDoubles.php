<?php

declare(strict_types=1);

// Stand-ins for the engine's native UI functions, shared by the UI tests.
// Each test keeps its elements, and the native calls it expects, in
// $GLOBALS['lenga_ui_test_state'].

/**
 * @return array<string, mixed>|false
 */
function lenga_internal_ui_element_get_state(int $elementId): array|false
{
    return $GLOBALS['lenga_ui_test_state']['elements'][$elementId] ?? false;
}

function lenga_ui_test_record(string $binding, int $elementId, mixed ...$values): bool
{
    $state = &$GLOBALS['lenga_ui_test_state'];
    $state['calls'][] = [$binding, $elementId, ...$values];

    return isset($state['elements'][$elementId]);
}

/**
 * Declares lenga_internal_ui_{$element}_set_{$property} for each property. Each one records its call as
 * [$property, $elementId, ...$values].
 *
 * @param list<string> $properties
 */
function lenga_ui_test_declare_setters(string $element, array $properties): void
{
    foreach ($properties as $property) {
        $function = sprintf('lenga_internal_ui_%s_set_%s', $element, $property);
        if (function_exists($function)) {
            continue;
        }
        eval(sprintf(
            'function %1$s(int $elementId, mixed ...$values): bool { return lenga_ui_test_record(%2$s, $elementId, ...$values); }',
            $function,
            var_export($property, true),
        ));
    }
}
