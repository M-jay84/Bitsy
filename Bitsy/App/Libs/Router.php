<?php

declare(strict_types=1);

namespace App\Libs;

class Router
{
    public function dispatch(): void
    {
        $url = $this->getUrl();

        // Default controller
        $controllerName = 'Home';

        if (!empty($url[0])) {
            $controllerName = $this->sanitize($url[0]);
            unset($url[0]);
        }

        // Full file paths
        $userFile  = CONTROLLER_PATH . '/User/' . $controllerName . '.php';
        $adminFile = CONTROLLER_PATH . '/Admin/' . $controllerName . '.php';

        // Decide which controller to load
        if (is_file($userFile)) {
            $class = 'App\\Controllers\\User\\' . $controllerName;
        } elseif (is_file($adminFile)) {
            $class = 'App\\Controllers\\Admin\\' . $controllerName;
        } else {
// inside dispatch(), when controller file missing:
http_response_code(404);
style_header(LANG['NOTHING_FOUND'] ?? 'Not found');
style_begin_block(LANG['NOTHING_FOUND'] ?? 'Not found');
style_error(LANG['NOTHING_FOUND'] ?? 'Page not found');
style_end_block();
style_footer();
exit;
        }

        // Create controller object
        $controller = new $class();

        // Default method
        $method = 'index';

        if (isset($url[1]) && method_exists($controller, $url[1])) {
            $method = $this->sanitize($url[1]);
            unset($url[1]);
        }

        // Remaining parts of the URL are parameters
        $params = $url ? array_values($url) : [];

        // Call the method
        call_user_func_array([$controller, $method], $params);
    }

    private function getUrl(): array
    {
        if (empty($_GET['url']) || !is_string($_GET['url'])) {
            return [];
        }

        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return $url === '' ? [] : explode('/', $url);
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_]/', '', $value) ?? '';
        return $value === '' ? 'Home' : ucfirst($value);
    }
}