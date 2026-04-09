<?php

declare(strict_types=1);

namespace Lenga\Engine\Enumerations;

enum KeyCode: string
{
    case BACKSPACE = 'Backspace';
    case TAB = 'Tab';
    case ENTER = 'Enter';
    case SHIFT = 'Shift';
    case CONTROL = 'Control';
    case ALT = 'Alt';
    case PAUSE = 'Pause';
    case CAPS_LOCK = 'CapsLock';
    case ESCAPE = 'Escape';
    case SPACE = 'Space';
    case PAGE_UP = 'PageUp';
    case PAGE_DOWN = 'PageDown';
    case END = 'End';
    case HOME = 'Home';
    case LEFT_ARROW = 'LeftArrow';
    case UP_ARROW = 'UpArrow';
    case RIGHT_ARROW = 'RightArrow';
    case DOWN_ARROW = 'DownArrow';
    case INSERT = 'Insert';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case G = 'G';
    case H = 'H';
    case I = 'I';
    case J = 'J';
    case K = 'K';
    case L = 'L';
    case M = 'M';
    case N = 'N';
    case O = 'O';
    case P = 'P';
    case Q = 'Q';
    case R = 'R';
    case S = 'S';
    case T = 'T';
    case U = 'U';
    case V = 'V';
    case W = 'W';
    case X = 'X';
    case Y = 'Y';
    case Z = 'Z';
    case ZERO = '0';
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';
    case SIX = '6';
    case SEVEN = '7';
    case EIGHT = '8';
    case NINE = '9';
    case F1 = 'F1';
    case F2 = 'F2';
    case F3 = 'F3';
    case F4 = 'F4';
    case F5 = 'F5';
    case F6 = 'F6';
    case F7 = 'F7';
    case F8 = 'F8';
    case F9 = 'F9';
    case F10 = 'F10';
    case F11 = 'F11';
    case F12 = 'F12';

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
        if (strlen($key) === 1) {
            if (ctype_alpha($key) || ctype_digit($key)) {
                return ord($key);
            }
        }

        if (preg_match('/^F([1-9]|1[0-2])$/', $key, $matches) === 1) {
            return 289 + (int) $matches[1];
        }

        return null;
    }
}
