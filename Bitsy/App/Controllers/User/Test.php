<?php
declare(strict_types=1);

namespace App\Controllers\User;

use App\Libs\View;
use App\Libs\Flash;
use App\Libs\Redirect;

class Test
{
    public function index(): void
    {
        View::render('test_index', [
            'TITLE' => 'Test',
        ], 'user');
    }

    public function second($id = null): void
    {
        if ($id === null || $id === '') {
            Flash::set(LANG['MISSING_ID'], 'error');
            Redirect::to('test');
        }

        View::render('test_second', [
            'TITLE' => 'Test second',
            'id'    => (string) $id,
        ], 'user');
    }

    public function flash(): void
    {
        Flash::set(LANG['SUCCESS'], 'success');
        Redirect::to('test');
    }

    public function error(): void
    {
        // Forces styled exception page (testing mode shows detail)
        throw new \RuntimeException('Example controlled failure for Errors handler.');
    }
}