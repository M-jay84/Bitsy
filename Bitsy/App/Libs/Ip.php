<?php

declare(strict_types=1);

namespace App\Libs;

/**
 * Visitor IP: IPv4/IPv6, Cloudflare, optional trusted proxies.
 * Default: CF-Connecting-IP or REMOTE_ADDR — no config required.
 */
class Ip
{
    /**
     * Client IP for logging, bans, account limits.
     */
    public static function getIP(): string
    {
        // Cloudflare edge sets this to the visitor IP
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $cfIp = self::normalize($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (self::isValidIp($cfIp)) {
                return $cfIp;
            }
        }

        // Optional: only if you put proxies in CONFIG['TRUSTED_PROXIES']
        $remote = self::normalize($_SERVER['REMOTE_ADDR'] ?? '');
        if ($remote !== '' && self::isTrustedProxy($remote)) {
            foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'] as $header) {
                if (empty($_SERVER[$header])) {
                    continue;
                }
                foreach (explode(',', (string) $_SERVER[$header]) as $part) {
                    $ip = self::normalize($part);
                    if (self::isValidIp($ip)) {
                        return $ip;
                    }
                }
            }
        }

        if ($remote !== '' && self::isValidIp($remote)) {
            return $remote;
        }

        return '127.0.0.1';
    }

    /** Valid IPv4 or IPv6 (including private/loopback for local dev). */
    public static function validIP(string $ip): bool
    {
        return self::isValidIp(self::normalize($ip));
    }

    /** Public only (no LAN / loopback). */
    public static function validPublicIP(string $ip): bool
    {
        $ip = self::normalize($ip);
        if ($ip === '') {
            return false;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Weak heuristic only — not proof of VPN. Optional to use.
     */
    public static function isUsingProxyOrVpn(): bool
    {
        foreach (['HTTP_VIA', 'HTTP_X_PROXY_ID', 'HTTP_PROXY_CONNECTION'] as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }

        return false;
    }

    public static function checkIpBan(?string $ip = null): void
    {
        $ip = $ip ?? self::getIP();
        if (!self::validIP($ip)) {
            return;
        }

        $res = DB::run('SELECT first, last FROM `bans` WHERE type = ?', ['ip']);

        foreach ($res as $row) {
            if (self::ipInRange($ip, (string) $row['first'], (string) $row['last'])) {
                http_response_code(403);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!DOCTYPE html><html><head><title>Forbidden</title></head><body>';
                echo '<h1>Forbidden</h1><p>Unauthorized IP address.</p></body></html>';
                exit;
            }
        }
    }

    public static function checkIP(): void
    {
        $ip = self::getIP();
        $count = (int) DB::run(
            'SELECT COUNT(id) FROM `users` WHERE ip = ?',
            [$ip]
        )->fetchColumn();

        $max = (int) (CONFIG['ACCOUNTMAX'] ?? 1);
        if ($count >= $max) {
            showMessage('login', LANG['IP_USED'] ?? 'This IP is already in use!');
        }
    }

    public static function ipInRange(string $ip, string $start, string $end): bool
    {
        $ipBin    = @inet_pton(self::normalize($ip));
        $startBin = @inet_pton(self::normalize($start));
        $endBin   = @inet_pton(self::normalize($end));

        if ($ipBin === false || $startBin === false || $endBin === false) {
            return false;
        }
        if (strlen($ipBin) !== strlen($startBin) || strlen($startBin) !== strlen($endBin)) {
            return false;
        }

        return $ipBin >= $startBin && $ipBin <= $endBin;
    }

    // -------------------------------------------------------------------------

    private static function normalize(string $ip): string
    {
        $ip = trim($ip);

        if (preg_match('/^\[([a-fA-F0-9:]+)\](?::\d+)?$/', $ip, $m)) {
            return $m[1];
        }
        if (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $ip, $m)) {
            return $m[1];
        }

        return $ip;
    }

    private static function isValidIp(string $ip): bool
    {
        return $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Off by default: no TRUSTED_PROXIES (or empty) → never trust X-Forwarded-For.
     * Only needed later if you add nginx/HAProxy in front.
     */
    private static function isTrustedProxy(string $ip): bool
    {
        $list = CONFIG['TRUSTED_PROXIES'] ?? null;
        if (!is_array($list) || $list === []) {
            return false;
        }

        foreach ($list as $trusted) {
            $trusted = trim((string) $trusted);
            if ($trusted === '') {
                continue;
            }
            if ($ip === $trusted) {
                return true;
            }
            if (str_contains($trusted, '/') && self::ipInCidr($ip, $trusted)) {
                return true;
            }
        }

        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        if ($subnet === null || $bits === null || !is_numeric($bits)) {
            return false;
        }

        $ipBin     = @inet_pton(self::normalize($ip));
        $subnetBin = @inet_pton(self::normalize($subnet));
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        $bits    = (int) $bits;
        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $bytes = intdiv($bits, 8);
        $rest  = $bits % 8;

        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }
        if ($rest === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $rest)) & 0xFF);

        return ($ipBin[$bytes] & $mask) === ($subnetBin[$bytes] & $mask);
    }
}