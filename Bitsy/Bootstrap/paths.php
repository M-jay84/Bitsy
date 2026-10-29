<?php

declare(strict_types=1);

// Disk paths only — never echo these in HTML
define('CACHE_PATH',      APPROOT . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'cache');
define('LOG_PATH',        APPROOT . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'logs');
define('UPLOAD_PATH',     APPROOT . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'files');
define('VIEW_PATH',       APPROOT . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Views');
define('CONTROLLER_PATH', APPROOT . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Controllers');

// Public URL base — must stay a URL, never a C:\ path.
define('URLROOT', 'http://localhost/Bitsy');