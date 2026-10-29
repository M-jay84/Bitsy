<?php

declare(strict_types=1);

namespace App\Libs;

use App\Libs\Redirect;
use App\Libs\Flash;
use App\Libs\Cache;
/*
* View Class
*/
class View
{
    // Display View Template
    public static function template($file, $data = [], $page = false)
    {
        //$session_id = isset($data['current_user']->id) ? $data['current_user']->id : 0;
        //if (!$session_id) {
        //$guests_query = "INSERT INTO `guests` (`ip`, `time`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `time` = ?";
        //    DB::run($guests_query, [Ip::getIP(), CONFIG['TT_TIME'], CONFIG['TT_TIME']]);
        //}

        extract($data);

        $TTCache = new Cache();

        if ($page == 'user' && file_exists("../App/Views/user/$file.php")) {
            $default_theme = (isset($_SESSION['stylesheet']) && $_SESSION['stylesheet']) != '' ? $_SESSION['stylesheet'] : 'inc';
            require_once VIEW_PATH . "/$default_theme/theme.php";
        } elseif ($page == 'admin' && file_exists("../App/Views/admin/$file.php")) {
            require_once VIEW_PATH . "/admin/admin-cp.php";
        } elseif (file_exists("../App/Views/user/user-shoutboxload.php")) {
            require_once VIEW_PATH . "/user/user-shoutboxload.php";
        } else {
            showMessage(URLROOT, "View $file does not exist");
        }

        return;
    }

    // new bits
    public static function render(string $file, array $data = [], string $layout = 'user'): void
    {

        $TTCache = new Cache();

        extract($data, EXTR_SKIP);

        $viewFile = VIEW_PATH . '/' . $layout . '/' . $file . '.php';

        if (!is_file($viewFile)) {
            Flash::set(LANG['NO_FILE'] ?? 'View not found', 'error');
            Redirect::to();
        }
 
        // backend compat
        $page = $layout;


        // Make $viewFile available to the layout
        if ($layout === 'user') {
            //$theme = $_SESSION['stylesheet'] ?? CONFIG['DEFAULTTHEME'] ?? 'inc';
            $layoutFile = VIEW_PATH . '/' . $theme . '/theme.php';
            if (!is_file($layoutFile)) {
                // Fallback: render view only with style helpers
                style_header($data['TITLE'] ?? '');
                require $viewFile;
                style_footer();
                return;
            }
            require $layoutFile;
            return;
        }

        if ($layout === 'admin') {
            require VIEW_PATH . '/admin/admin-cp.php';
            return;
        }

        // Raw (AJAX etc.)
        require $viewFile;
    }

    /** Show flash once, using style_* helpers */
    public static function flash(): void
    {
        $flash = Flash::get();
        if ($flash === null) {
            return;
        }
        $msg = $flash['message'];
        match ($flash['type'] ?? 'info') {
            'success' => style_success($msg),
            'error'   => style_error($msg),
            'warning' => style_warning($msg),
            default   => style_info($msg),
        };
    }
}