<?php

declare(strict_types=1);

/**
 * Security + Input Helpers
 */

// -------------------------------------------------------
// Output escaping
// -------------------------------------------------------
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// -------------------------------------------------------
// Request method helpers
// -------------------------------------------------------
function is_get(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// -------------------------------------------------------
// Input helpers
// -------------------------------------------------------
function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function request(string $key, $default = null)
{
    return $_REQUEST[$key] ?? $default;
}

function clean(mixed $value)
{
    if (is_array($value)) {
        return array_map('clean', $value);
    }

    return trim(stripslashes((string) $value));
}

// -------------------------------------------------------
// CSRF Protection
// -------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = post('_token');

    if (!$token || empty($_SESSION['_csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['_csrf_token'], $token);
}