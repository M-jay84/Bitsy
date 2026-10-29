<?php

declare(strict_types=1);

// Place All Lecacy Functions Here
// Once added availbe in mvc

// Redidect Page
function showMessage($url, $message1,$refresh = 3)
{
    if ($url != URLROOT) {
        $url = URLROOT . '/' . $url;
    }

    $data = [
        'TITLE' => 'Message',
        'MESSAGE' => $message1,
        'URL' => $url,
        'REFRESH'  => $refresh
    ];
    extract($data);
    require_once VIEW_PATH . "/inc/error-page.php";
    exit();
}

// Get Redirect
function Url($url = URLROOT, $go_to = false)
{
    if ($url != URLROOT) {
        $url = URLROOT . '/' . $url;
    }

    if ($go_to) {
        if (!headers_sent()) {
            header("Location: " . $url, true, 302);
            exit();
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="' . $url . '";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="3; url=' . $url . '" />';
            echo '</noscript>';
            exit();
        }
    }

    return $url;
}
