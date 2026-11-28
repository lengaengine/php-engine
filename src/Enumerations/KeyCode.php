<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

/**
 * A keyboard key.
 *
 * Pass it to {@see \Lenga\Engine\Core\Input::getKey()},
 * {@see \Lenga\Engine\Core\Input::getKeyDown()} and
 * {@see \Lenga\Engine\Core\Input::getKeyUp()}:
 *
 * ```php
 * if (Input::getKeyDown(KeyCode::SPACE)) {
 *     // Jump.
 * }
 * ```
 *
 * Each case's value is the key's name, such as `'LeftArrow'`. The input methods
 * also accept a key name as a string or a raw key code as an `int`; see
 * {@see KeyCode::resolve()} for the names they understand.
 */
enum KeyCode: string
{
    /** The Backspace key. */
    case BACKSPACE = 'Backspace';

    /** The Tab key. */
    case TAB = 'Tab';

    /** The Enter key, also called Return. */
    case ENTER = 'Enter';

    /** The left Shift key. */
    case SHIFT = 'Shift';

    /** The left Control key. */
    case CONTROL = 'Control';

    /** The left Alt key, also called Option on Mac keyboards. */
    case ALT = 'Alt';

    /** The Pause key. */
    case PAUSE = 'Pause';

    /** The Caps Lock key. */
    case CAPS_LOCK = 'CapsLock';

    /** The Escape key. */
    case ESCAPE = 'Escape';

    /** The space bar. */
    case SPACE = 'Space';

    /** The Page Up key. */
    case PAGE_UP = 'PageUp';

    /** The Page Down key. */
    case PAGE_DOWN = 'PageDown';

    /** The End key. */
    case END = 'End';

    /** The Home key. */
    case HOME = 'Home';

    /** The left arrow key. */
    case LEFT_ARROW = 'LeftArrow';

    /** The up arrow key. */
    case UP_ARROW = 'UpArrow';

    /** The right arrow key. */
    case RIGHT_ARROW = 'RightArrow';

    /** The down arrow key. */
    case DOWN_ARROW = 'DownArrow';

    /** The Insert key. */
    case INSERT = 'Insert';

    /** The A key. */
    case A = 'A';

    /** The B key. */
    case B = 'B';

    /** The C key. */
    case C = 'C';

    /** The D key. */
    case D = 'D';

    /** The E key. */
    case E = 'E';

    /** The F key. */
    case F = 'F';

    /** The G key. */
    case G = 'G';

    /** The H key. */
    case H = 'H';

    /** The I key. */
    case I = 'I';

    /** The J key. */
    case J = 'J';

    /** The K key. */
    case K = 'K';

    /** The L key. */
    case L = 'L';

    /** The M key. */
    case M = 'M';

    /** The N key. */
    case N = 'N';

    /** The O key. */
    case O = 'O';

    /** The P key. */
    case P = 'P';

    /** The Q key. */
    case Q = 'Q';

    /** The R key. */
    case R = 'R';

    /** The S key. */
    case S = 'S';

    /** The T key. */
    case T = 'T';

    /** The U key. */
    case U = 'U';

    /** The V key. */
    case V = 'V';

    /** The W key. */
    case W = 'W';

    /** The X key. */
    case X = 'X';

    /** The Y key. */
    case Y = 'Y';

    /** The Z key. */
    case Z = 'Z';

    /** The 0 key on the number row. */
    case ZERO = '0';

    /** The 1 key on the number row. */
    case ONE = '1';

    /** The 2 key on the number row. */
    case TWO = '2';

    /** The 3 key on the number row. */
    case THREE = '3';

    /** The 4 key on the number row. */
    case FOUR = '4';

    /** The 5 key on the number row. */
    case FIVE = '5';

    /** The 6 key on the number row. */
    case SIX = '6';

    /** The 7 key on the number row. */
    case SEVEN = '7';

    /** The 8 key on the number row. */
    case EIGHT = '8';

    /** The 9 key on the number row. */
    case NINE = '9';

    /** The F1 key. */
    case F1 = 'F1';

    /** The F2 key. */
    case F2 = 'F2';

    /** The F3 key. */
    case F3 = 'F3';

    /** The F4 key. */
    case F4 = 'F4';

    /** The F5 key. */
    case F5 = 'F5';

    /** The F6 key. */
    case F6 = 'F6';

    /** The F7 key. */
    case F7 = 'F7';

    /** The F8 key. */
    case F8 = 'F8';

    /** The F9 key. */
    case F9 = 'F9';

    /** The F10 key. */
    case F10 = 'F10';

    /** The F11 key. */
    case F11 = 'F11';

    /** The F12 key. */
    case F12 = 'F12';

    /**
     * Returns the numeric key code Lenga's input system uses for this key.
     *
     * Most scripts pass the case itself to {@see \Lenga\Engine\Core\Input}
     * and never need the number.
     */
    public function toRaylibKeyCode(): int
    {
        return match ($this) {
            self::BACKSPACE => 259,
            self::TAB => 258,
            self::ENTER => 257,
            self::SHIFT => 340,
            self::CONTROL => 341,
            self::ALT => 342,
            self::PAUSE => 284,
            self::CAPS_LOCK => 280,
            self::ESCAPE => 256,
            self::SPACE => 32,
            self::PAGE_UP => 266,
            self::PAGE_DOWN => 267,
            self::END => 269,
            self::HOME => 268,
            self::LEFT_ARROW => 263,
            self::UP_ARROW => 265,
            self::RIGHT_ARROW => 262,
            self::DOWN_ARROW => 264,
            self::INSERT => 260,
            default => self::resolveGeneratedKey($this->value) ?? 0,
        };
    }

    /**
     * Converts a key, a key name or a raw key code to the numeric key code Lenga's input system uses.
     *
     * The `Input` key methods call this for every key you pass. A name is
     * matched without regard to case or surrounding spaces, `-` and spaces
     * count as `_`, and a `KEY_` prefix is ignored, so `'space'`, `'Space'`
     * and `'KEY_SPACE'` all name the space bar. It understands each case's
     * value, such as `'LeftArrow'` or `'7'`, the case names of the letter,
     * function and named keys, such as `'LEFT_ARROW'`, and also `RETURN`,
     * `CTRL`, `ESC`, `INS`, `LEFT`, `RIGHT`, `UP`, `DOWN`, `LEFT_SHIFT`,
     * `LEFT_CONTROL`, `LEFT_ALT`, and `DELETE` or `DEL` for the Delete key,
     * which has no case of its own. Write number row keys as `'0'` to `'9'`:
     * names such as `'ZERO'` are not recognised.
     *
     * @param self|string|int $key A KeyCode case, a key name, or a raw key code. A raw key code is returned unchanged.
     * @return int|null The key code, or null when the name is not recognised.
     */
    public static function resolve(self|string|int $key): ?int
    {
        if (is_int($key)) {
            return $key;
        }

        if ($key instanceof self) {
            return $key->toRaylibKeyCode();
        }

        $normalized = self::normalizeIdentifier($key);

        return match ($normalized) {
            'BACKSPACE' => self::BACKSPACE->toRaylibKeyCode(),
            'TAB' => self::TAB->toRaylibKeyCode(),
            'ENTER', 'RETURN' => self::ENTER->toRaylibKeyCode(),
            'SHIFT', 'LEFTSHIFT', 'LEFT_SHIFT' => self::SHIFT->toRaylibKeyCode(),
            'CONTROL', 'CTRL', 'LEFTCONTROL', 'LEFT_CONTROL' => self::CONTROL->toRaylibKeyCode(),
            'ALT', 'LEFTALT', 'LEFT_ALT' => self::ALT->toRaylibKeyCode(),
            'PAUSE' => self::PAUSE->toRaylibKeyCode(),
            'CAPSLOCK', 'CAPS_LOCK' => self::CAPS_LOCK->toRaylibKeyCode(),
            'ESC', 'ESCAPE' => self::ESCAPE->toRaylibKeyCode(),
            'SPACE' => self::SPACE->toRaylibKeyCode(),
            'PAGEUP', 'PAGE_UP' => self::PAGE_UP->toRaylibKeyCode(),
            'PAGEDOWN', 'PAGE_DOWN' => self::PAGE_DOWN->toRaylibKeyCode(),
            'END' => self::END->toRaylibKeyCode(),
            'HOME' => self::HOME->toRaylibKeyCode(),
            'LEFT', 'LEFTARROW', 'LEFT_ARROW' => self::LEFT_ARROW->toRaylibKeyCode(),
            'UP', 'UPARROW', 'UP_ARROW' => self::UP_ARROW->toRaylibKeyCode(),
            'RIGHT', 'RIGHTARROW', 'RIGHT_ARROW' => self::RIGHT_ARROW->toRaylibKeyCode(),
            'DOWN', 'DOWNARROW', 'DOWN_ARROW' => self::DOWN_ARROW->toRaylibKeyCode(),
            'INSERT', 'INS' => self::INSERT->toRaylibKeyCode(),
            'DELETE', 'DEL' => 261,
            default => self::resolveGeneratedKey($normalized),
        };
    }

    private static function normalizeIdentifier(string $key): string
    {
        $normalized = strtoupper(trim($key));
        $normalized = str_replace(['-', ' '], '_', $normalized);

        if (str_starts_with($normalized, 'KEY_')) {
            return substr($normalized, 4);
        }

        return $normalized;
    }

    private static function resolveGeneratedKey(string $key): ?int
    {
        if (strlen($key) === 1 && str_contains('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $key)) {
            return ord($key);
        }

        if (preg_match('/^F([1-9]|1[0-2])$/', $key, $matches) === 1) {
            return 289 + (int) $matches[1];
        }

        return null;
    }
}
