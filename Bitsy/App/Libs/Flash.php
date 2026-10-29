<?php

declare(strict_types=1);

namespace App\Libs;

class Flash
{
    private const KEY = '_flash';

    public static function set(string $message, string $type = 'info'): void
    {
        $_SESSION[self::KEY] = [
            'message' => $message,
            'type'    => $type, // success | error | warning | info
        ];
    }

    public static function get(): ?array
    {
        if (empty($_SESSION[self::KEY])) {
            return null;
        }

        $flash = $_SESSION[self::KEY];
        unset($_SESSION[self::KEY]);
        return $flash;
    }

    public static function has(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }
}