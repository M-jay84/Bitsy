<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Libs\View;

// Default Controller (Home Page)
class Home
{
    public function index(): void
    {
        View::render('home', [
            'TITLE' => LANG['HOME'] ?? 'Home',
        ], 'user');
    }

    public function test(): void
    {
        View::render('home_test', [
            'TITLE' => LANG['HOME'],
        ], 'user');
    }
}