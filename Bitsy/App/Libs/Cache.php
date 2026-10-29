<?php
namespace App\Libs;

/**
 * Modernised Cache Class
 * Supports: Redis, Memcached, APCu, and Disk (File) Caching
 */
class Cache
{
    private string $type;
    private string $cacheDir;
    private string $prefix;
    private ?object $client = null;

    public function __construct()
    {
        $this->cacheDir = rtrim(defined('CACHE_PATH') ? CACHE_PATH : sys_get_temp_dir(), '/');
        $this->prefix   = (defined('CONFIG') && isset(CONFIG['SITENAME'])) ? CONFIG['SITENAME'] . '_' : 'app_';
        $requestedType  = defined('CONFIG') && isset(CONFIG['CACHE_TYPE']) ? strtolower(trim(CONFIG['CACHE_TYPE'])) : 'disk';

        switch ($requestedType) {
            case 'apcu':
                if (function_exists('apcu_enabled') && apcu_enabled()) {
                    $this->type = 'apcu';
                } else {
                    trigger_error("APCu extension is not enabled. Falling back to disk cache.", E_USER_WARNING);
                    $this->type = 'disk';
                }
                break;

            case 'redis':
                if (class_exists('\Redis')) {
                    try {
                        $this->client = new \Redis();
                        $host = CONFIG['REDIS_HOST'] ?? '127.0.0.1';
                        $port = (int)(CONFIG['REDIS_PORT'] ?? 6379);
                        
                        if ($this->client->connect($host, $port, 1.5)) { // 1.5s timeout
                            $this->type = 'redis';
                        } else {
                            throw new \Exception("Could not connect to Redis server.");
                        }
                    } catch (\Throwable $e) {
                        trigger_error("Redis connection failed: " . $e->getMessage() . " Falling back to disk cache.", E_USER_WARNING);
                        $this->type = 'disk';
                    }
                } else {
                    trigger_error("Redis extension missing. Falling back to disk cache.", E_USER_WARNING);
                    $this->type = 'disk';
                }
                break;

            case 'memcached':
            case 'memcache': // Legacy alias mapping
                if (class_exists('\Memcached')) {
                    $this->client = new \Memcached();
                    $host = CONFIG['MEMCACHE_HOST'] ?? '127.0.0.1';
                    $port = (int)(CONFIG['MEMCACHE_PORT'] ?? 11211);
                    
                    $this->client->addServer($host, $port);
                    // Check connection status by fetching server statistics
                    $stats = $this->client->getStats();
                    if (isset($stats["$host:$port"]) && $stats["$host:$port"]['pid'] > 0) {
                        $this->type = 'memcached';
                    } else {
                        trigger_error("Memcached server unreachable. Falling back to disk cache.", E_USER_WARNING);
                        $this->type = 'disk';
                    }
                } else {
                    trigger_error("Modern Memcached extension missing. Falling back to disk cache.", E_USER_WARNING);
                    $this->type = 'disk';
                }
                break;

            default:
                $this->type = 'disk';
        }
    }

    /**
     * Store data in cache
     */
    public function set(string $key, mixed $value, int $expire = 0): bool
    {
        if ($expire <= 0) {
            return false;
        }

        $prefixedKey = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                return apcu_store($prefixedKey, $value, $expire);

            case 'redis':
                return $this->client->setEx($prefixedKey, $expire, serialize($value));

            case 'memcached':
                return $this->client->set($prefixedKey, $value, $expire);

            case 'disk':
            default:
                $filePath = $this->getDiskPath($key);
                $data = [
                    'expires_at' => time() + $expire,
                    'payload'    => $value
                ];
                // Atomic save to prevent race-condition file corruption
                $tmpFile = tempnam($this->cacheDir, 'tmp_');
                if ($tmpFile && file_put_contents($tmpFile, serialize($data)) !== false) {
                    if (rename($tmpFile, $filePath)) {
                        return true;
                    }
                }
                @unlink($tmpFile);
                return false;
        }
    }

    /**
     * Retrieve data from cache
     */
    public function get(string $key): mixed
    {
        $prefixedKey = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                $success = false;
                $data = apcu_fetch($prefixedKey, $success);
                return $success ? $data : false;

            case 'redis':
                $data = $this->client->get($prefixedKey);
                return $data !== false ? unserialize($data) : false;

            case 'memcached':
                $data = $this->client->get($prefixedKey);
                return $this->client->getResultCode() === \Memcached::RES_SUCCESS ? $data : false;

            case 'disk':
            default:
                $filePath = $this->getDiskPath($key);
                if (!file_exists($filePath)) {
                    return false;
                }

                $content = @file_get_contents($filePath);
                if ($content === false) {
                    return false;
                }

                $data = @unserialize($content);
                if (!is_array($data) || !isset($data['expires_at'], $data['payload'])) {
                    $this->delete($key); // Corrupt file cleanup
                    return false;
                }

                if (time() > $data['expires_at']) {
                    $this->delete($key); // Expired cache cleanup
                    return false;
                }

                return $data['payload'];
        }
    }

    /**
     * Delete data from cache
     */
    public function delete(string $key): bool
    {
        $prefixedKey = $this->prefix . $key;

        switch ($this->type) {
            case 'apcu':
                return apcu_delete($prefixedKey);

            case 'redis':
                return (bool)$this->client->del($prefixedKey);

            case 'memcached':
                return $this->client->delete($prefixedKey);

            case 'disk':
            default:
                $filePath = $this->getDiskPath($key);
                if (file_exists($filePath)) {
                    return @unlink($filePath);
                }
                return false;
        }
    }

    /**
     * Generate file hash string to safe-guard against directory traversal attacks
     */
    private function getDiskPath(string $key): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }
}
