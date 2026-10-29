<?php
declare(strict_types=1);

namespace App\Libs;

class Errors
{
    private bool $testing = false;

    public function register(bool $testing = false): void
    {
        $this->testing = $testing;

        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', LOG_PATH . '/php_errors.log');

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    public function handleException(\Throwable $e): void
    {
        $this->logException($e);

        http_response_code(500);

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }

        $userMessage = $this->getUserMessage($e);

        style_header(LANG['ERRORS'] ?? 'Error');
        style_begin_block(LANG['ERRORS'] ?? 'Error');
        style_error($userMessage);

        // Only show full details for real exceptions when testing
        if ($this->testing) {
            echo '<pre style="overflow:auto;font-size:0.85rem;color:#94a3b8;">';
            echo e((string) $e);
            echo '</pre>';
        }

        style_end_block();
        style_footer();
        exit;
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Respect @ operator
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Non-fatal → log only, never throw / never show
        $nonFatal = [
            E_NOTICE,
            E_USER_NOTICE,
            E_WARNING,
            E_USER_WARNING,
            E_DEPRECATED,
            E_USER_DEPRECATED,
            E_STRICT,
        ];

        if (in_array($errno, $nonFatal, true)) {
            $this->logPhpError($errno, $errstr, $errfile, $errline);
            return true;
        }

        // Fatal-style errors → exception
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    private function getUserMessage(\Throwable $e): string
    {
        if ($e instanceof \PDOException) {
            return LANG['OOPS_ERR'] ?? 'A database error occurred. Please try again later.';
        }

        return LANG['OOPS_ERR'] ?? 'Something went wrong. Please try again.';
    }

    /** Real exceptions (APPLICATION / DATABASE) */
    private function logException(\Throwable $e): void
    {
        if ($e instanceof \PDOException) {
            $file = LOG_PATH . '/database.log';
            $type = 'DATABASE';
        } else {
            $file = LOG_PATH . '/exceptions.log';
            $type = 'APPLICATION';
        }

        $message = sprintf(
            "[%s] [%s] %s\nFile: %s:%d\nTrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $type,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        error_log($message, 3, $file);
    }

    /** Notices / warnings / undefined vars etc. */
    private function logPhpError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $levels = [
            E_NOTICE          => 'NOTICE',
            E_USER_NOTICE     => 'NOTICE',
            E_WARNING         => 'WARNING',
            E_USER_WARNING    => 'WARNING',
            E_DEPRECATED      => 'DEPRECATED',
            E_USER_DEPRECATED => 'DEPRECATED',
            E_STRICT          => 'STRICT',
        ];

        $level = $levels[$errno] ?? 'ERROR';

        $message = sprintf(
            "[%s] [%s] %s\nFile: %s:%d\n\n",
            date('Y-m-d H:i:s'),
            $level,
            $errstr,
            $errfile,
            $errline
        );

        error_log($message, 3, LOG_PATH . '/php_errors.log');
    }
}