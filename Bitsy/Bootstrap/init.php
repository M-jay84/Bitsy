<?php
declare(strict_types=1);

use App\Libs\Errors;

// Load Paths / Autoload
require 'paths.php';

// Autoload Classes By Nmanespace
spl_autoload_register(function ($className) {
    $filename = APPROOT . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($filename)) {
        require_once($filename);
    }
});

// Load Config & DB Details
require 'config.php';
require 'database.php';

// Start Session
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Custom session name
    session_name('MBITSYSESSID');
    
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

date_default_timezone_set('Europe/London');

// Handle Errors/Exceptions
(new Errors)->register(true);

// Load Langauge Array
require 'language.php';
require 'security.php';
require 'style.php';

// Load Functions
require 'functions.php';