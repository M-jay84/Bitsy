<?php

namespace App\Controllers\User;


use App\Libs\View;

/*
* Default Controller (Home Page)
*/
class Home
{

    // Admincp Default Page
    public function index()
    {
        echo 'Welcome to the User Home Page';
		echo '<ul>
		<li><a href="<?= e(URLROOT) ?>/home"><?= e('Home') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/home/test"><?= e('Home Test') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test"><?= e('Test index') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/second/42"><?= e('Test second with id') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/flash"><?= e('Test flash message') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/error"><?= e('Test error page (styled)') ?></a></li>
</ul>';
    }

    public function test(): void
    {
        
        View::render('user-test', [
            'TITLE' => LANG['HOME'],
        ], 'user');
        
    }
}
