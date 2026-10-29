<?php

declare(strict_types=1);

namespace App\Libs;

/**
 * Cache: APCu, Redis, Memcached, or disk.
 * CONFIG['CACHE_TYPE'] = apcu | redis | memcached | disk
 */
class Cache
{
    private string $type = 'disk';
    private string $cacheDir;
    private string $prefix;
    private mixed $client = null;

    public function __construct()
    {
        $dir = defined('CACHE_PATH') ? CACHE_PATH : sys_get_temp_dir();
        $this->cacheDir = rtrim((string) $dir, "/\\");
        $this->prefix   = (defined('CONFIG') && !empty(CONFIG['SITENAME']))
            ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) CONFIG['SITENAME']) . '_'
            : 'app_';

        $requested = defined('CONFIG') && isset(CONFIG['CACHE_TYPE'])
            ? strtolower(trim((string) CONFIG['CACHE_TYPE']))
            : 'disk';

        switch ($requested) {
            case 'apcu':
                if (extension_loaded('apcu') && function_exists('apcu_store')) {
                    $this->type = 'apcu';
                } else {
                    trigger_error('APCu not available; using disk cache.', E_USER_WARNING);
                }
                break;

            case 'redis':
                // String name avoids "Undefined type Redis" in the IDE
                if (extension_loaded('redis') && class_exists('Redis')) {
                    try {
                        $class  = 'Redis';
                        $redis  = new $class();
                        $host   = CONFIG['REDIS_HOST'] ?? '127.0.0.1';
                        $port   = (int) (CONFIG['REDIS_PORT'] ?? 6379);
                        if (!$redis->connect($host, $port, 1.5)) {
                            throw new \RuntimeException('connect failed');
                        }
                        $this->client = $redis;
                        $this->type   = 'redis';
                    } catch (\Throwable $e) {
                        trigger_error('Redis failed: ' . $e->getMessage() . '; using disk.', E_USER_WARNING);
                    }
                } else {
                    trigger_error('Redis extension missing; using disk cache.', E_USER_WARNING);
                }
                break;

            case 'memcached':
            case 'memcache':
                if (extension_loaded('memcached') && class_exists('Memcached')) {
                    $host = CONFIG['MEMCACHE_HOST'] ?? '127.0.0.1';
                    $port = (int) (CONFIG['MEMCACHE_PORT'] ?? 11211);
                    $class = 'Memcached';
                    $m = new $class();
                    $m->addServer($host, $port);
                    $stats = $m->getStats();
                    if (is_array($stats) && isset($stats[$host . ':' . $port])) {
                        $this->client = $m;
                        $this->type   = 'memcached';
                    } else {
                        trigger_error('Memcached unreachable; using disk cache.', E_USER_WARNING);
                    }
                } else {
                    trigger_error('Memcached extension missing; using disk cache.', E_USER_WARNING);
                }
                break;
        }

        if ($this->type === 'disk' && !is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    public function set(string $key, mixed $value, int $expire = 3600): bool
    {
        if ($expire <= 0) {
            return false;
        }

        $k = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                return (bool) call_user_func('apcu_store', $k, $value, $expire);

            case 'redis':
                return (bool) $this->client->setEx($k, $expire, serialize($value));

            case 'memcached':
                return (bool) $this->client->set($k, $value, $expire);

            default:
                $data = serialize([
                    'expires_at' => time() + $expire,
                    'payload'    => $value,
                ]);
                $path = $this->diskPath($key);
                $tmp  = tempnam($this->cacheDir, 'tmp_');
                if ($tmp && file_put_contents($tmp, $data) !== false) {
                    if (is_file($path)) {
                        @unlink($path);
                    }
                    if (@rename($tmp, $path)) {
                        return true;
                    }
                }
                if (!empty($tmp)) {
                    @unlink($tmp);
                }
                return false;
        }
    }

    public function get(string $key): mixed
    {
        $k = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                $ok   = false;
                $data = call_user_func_array('apcu_fetch', [$k, &$ok]);
                return $ok ? $data : false;

            case 'redis':
                $data = $this->client->get($k);
                return $data !== false ? unserialize($data) : false;

            case 'memcached':
                $data = $this->client->get($k);
                // RES_SUCCESS = 0
                return $this->client->getResultCode() === 0 ? $data : false;

            default:
                $path = $this->diskPath($key);
                if (!is_file($path)) {
                    return false;
                }
                $content = @file_get_contents($path);
                if ($content === false) {
                    return false;
                }
                $data = @unserialize($content);
                if (!is_array($data) || !isset($data['expires_at'], $data['payload'])) {
                    $this->delete($key);
                    return false;
                }
                if (time() > $data['expires_at']) {
                    $this->delete($key);
                    return false;
                }
                return $data['payload'];
        }
    }

    public function delete(string $key): bool
    {
        $k = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                return (bool) call_user_func('apcu_delete', $k);

            case 'redis':
                return (bool) $this->client->del($k);

            case 'memcached':
                return (bool) $this->client->delete($k);

            default:
                $path = $this->diskPath($key);
                return !is_file($path) || @unlink($path);
        }
    }

    private function diskPath(string $key): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($this->prefix . $key) . '.cache';
    }
}