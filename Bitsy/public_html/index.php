<?php

declare(strict_types=1);
// Realpath normalises Windows separators; false-safe fallback if realpath fails.
$appRoot = realpath(dirname(__DIR__));
if ($appRoot === false) {
    $appRoot = dirname(__DIR__);
}

define('APPROOT', $appRoot);

require APPROOT.'/Bootstrap/init.php';

(new App\Libs\Router())->dispatch();