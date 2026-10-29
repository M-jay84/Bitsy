<?php

namespace App\Libs;

/*
 * Check IP, Proxies, Cloudflare, and VPN Connections
 */
class Ip
{
    /**
     * Resolves the true visitor IP address, extracting it safely if behind Cloudflare or trusted proxies.
     */
    public static function getIP(): string
    {
        // 1. Check Cloudflare first (if using Cloudflare, this is highly reliable)
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && self::validIP($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        // 2. Scan standard proxy headers ordered by commonality
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // Headers like X-Forwarded-For can contain a comma-separated chain of IPs
                foreach (explode(',', $_SERVER[$header]) as $ip) {
                    $ip = trim($ip);
                    // Filter out local, private, or invalid IPs to find the real public origin
                    if (self::validIP($ip)) {
                        return $ip;
                    }
                }
            }
        }

        // 3. Fallback to direct connection IP
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Validates if an IP is formatted correctly and is a PUBLIC address (ignores LAN/Private/Loopback ranges).
     */
    public static function validIP(string $ip): bool
    {
        // FILTER_FLAG_NO_PRIV_RANGE blocks private ranges (e.g. 10.0.0.0, 192.168.0.0, fe80::)
        // FILTER_FLAG_NO_RES_RANGE blocks reserved system ranges
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * Checks if the user is masking their true connection behind a proxy or VPN chain.
     */
    public static function isUsingProxyOrVpn(): bool
    {
        // If Cloudflare tells us a proxy was used to reach them
        if (!empty($_SERVER['HTTP_CF_VISITOR']) && str_contains($_SERVER['HTTP_CF_VISITOR'], 'proxy')) {
            return true;
        }

        // Check if any proxy headers are set, indicating a VPN/Proxy server forwarded the request
        $proxyHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_VIA',
            'HTTP_X_PROXY_ID'
        ];

        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Modernised IP Ban Check using native PHP range comparison (Works for IPv4 and IPv6 out of the box).
     */
    public static function checkIpBan(string $ip): void
    {
        $bans_query = "SELECT first, last FROM `bans` WHERE type = ?";
        $res = DB::run($bans_query, ['ip']);

        foreach ($res as $row) {
            if (self::ipInRange($ip, $row['first'], $row['last'])) {
                header("HTTP/1.1 403 Forbidden");
                echo '<html><head><title>Forbidden</title></head><body><h1>Forbidden</h1>Unauthorized IP address.</body></html>';
                exit;
            }
        }
    }

    /**
     * Checks maximum concurrent accounts per IP.
     */
    public static function checkIP(): void
    {
        $ip = self::getIP();
        $user_query = "SELECT count(id) FROM `users` WHERE ip = ?";
        $ipq = DB::run($user_query, [$ip])->fetchColumn();

        if ($ipq >= CONFIG['ACCOUNTMAX']) {
            showMessage('login', LANG["IP_USED"]);
        }
    }

    /**
     * Pure PHP helper to check if an IP falls inside a start/end range natively (handles IPv4 and IPv6).
     */
    private static function ipInRange(string $ip, string $start, string $end): bool
    {
        $ip_packed = @inet_pton($ip);
        $start_packed = @inet_pton($start);
        $end_packed = @inet_pton($end);

        // Ensure all IPs were parsed correctly and are of the same type (IPv4 vs IPv6)
        if (!$ip_packed || !$start_packed || !$end_packed || strlen($ip_packed) !== strlen($start_packed)) {
            return false;
        }

        // Standard string comparison works perfectly on packed binary formats
        return ($ip_packed >= $start_packed && $ip_packed <= $end_packed);
    }
}
