<?php

declare(strict_types=1);

namespace App\Libs;

use App\Libs\Flash;
class Redirect
{
    /**
     * Redirect + optional flash message in one call
     */
    public static function to(string $url = '', ?string $message = null, string $type = 'info'): never
    {
        if ($message !== null) {
            Flash::set($message, $type);
        }

        $target = self::url($url);

        if (!headers_sent()) {
            header('Location: ' . $target, true, 302);
            exit;
        }

        // Fallback if headers already sent
        echo '<script>window.location.href="' . htmlspecialchars($target, ENT_QUOTES) . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES) . '"></noscript>';
        exit;
    }

    public static function url(string $path = ''): string
    {
        $path = trim($path, '/');
        return $path === '' ? URLROOT : URLROOT . '/' . $path;
    }
}