<?php

namespace Mrmanchot\SimpleCache;

class SimpleCache
{
    private $cacheDir;
    public $shouldBypassCache = false;

    public function __construct($cacheDir = null)
    {
        $this->initCacheDir($cacheDir);
    }

    public function get($key, $delayMinutes = 0)
    {
        if ($this->shouldBypassCache || !is_string($key)) {
            return null;
        }

        $filename = $this->getCacheFilePath($key);
        if ($this->isCacheValid($filename, $delayMinutes)) {
            return $this->readCacheFile($filename);
        }

        return null;
    }

    public function set($key, $value)
    {
        if (!is_string($key)) {
            trigger_error('Invalid key.', E_USER_WARNING);
            return;
        }

        $filename = $this->getCacheFilePath($key);
        $this->ensureDirectoryExists(dirname($filename));
        $this->writeCacheFile($filename, $value);
    }

    public function clear($pattern = '*')
    {
        $files = glob($this->cacheDir . $pattern, GLOB_NOSORT);
        $files[] = $this->getCacheFilePath($pattern);
        foreach ($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    trigger_error('Failed to delete cache file: ' . $file, E_USER_WARNING);
                }
            }
        }
    }

    private function initCacheDir($cacheDir)
    {
        $cacheDir = $cacheDir ?: __DIR__ . '/cache/';
        $parentDir = dirname($cacheDir);

        if (!is_dir($cacheDir)) {
            if (!is_dir($parentDir)) {
                if (!mkdir($parentDir, 0755, true) && !is_dir($parentDir)) {
                    trigger_error('Failed to create parent directory for cache: ' . $parentDir, E_USER_WARNING);
                    return;
                }
            }

            if (!is_writable($parentDir)) {
                trigger_error("Parent directory ($parentDir) is not writable.", E_USER_WARNING);
                return;
            }

            if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
                trigger_error('Failed to create cache directory: ' . $cacheDir, E_USER_WARNING);
                return;
            }
        }

        $this->cacheDir = rtrim($cacheDir, '/') . '/';
    }

    private function getCacheFilePath($key)
    {
        $initialKey = $key;
        $key = $this->sanitizeKey($key);
        $filePath = $this->cacheDir . $key . '.txt';

        $realCacheDir = realpath($this->cacheDir);
        if ($realCacheDir === false) {
            trigger_error('Invalid cache directory: ' . $this->cacheDir, E_USER_WARNING);
            return $filePath;
        }

        $fileDir = dirname($filePath);
        $realFileDir = realpath($fileDir);

        if ($realFileDir !== false && strpos($realFileDir, $realCacheDir) !== 0) {
            trigger_error('Invalid cache key or unauthorized access attempt : ' . $initialKey, E_USER_WARNING);
            return $filePath;
        }

        return $filePath;
    }

    private function sanitizeKey($key)
    {
        $key = str_replace(['..', '\\', "\0"], '', $key);
        $key = preg_replace('/[^A-Za-z0-9_\-\/\|\*]/', '_', $key);
        $key = preg_replace('/\/+/', '/', $key);
        $key = ltrim($key, '/');

        return $key;
    }

    private function isCacheValid($filename, $delayMinutes)
    {
        return is_readable($filename) && !$this->isCacheExpired($filename, $delayMinutes);
    }

    private function readCacheFile($filename)
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            trigger_error('Failed to read cache file: ' . $filename, E_USER_WARNING);
            return null;
        }

        if ($this->isSerialized($content)) {
            $cachedValue = @unserialize($content);
            if ($cachedValue === false && $content !== serialize(false)) {
                trigger_error('Failed to unserialize cache file: ' . $filename, E_USER_WARNING);
                return null;
            }

            return $cachedValue;
        } else {
            return null;
        }
    }

    private function isSerialized($str)
    {
        return ($str === serialize(false) || @unserialize($str) !== false);
    }

    private function writeCacheFile($filename, $value)
    {
        try {
            $encodedValue = serialize($value);
        } catch (\Throwable $e) {
            trigger_error('Value cannot be serialized: ' . $e->getMessage(), E_USER_WARNING);
            return;
        }

        if (file_put_contents($filename, $encodedValue, LOCK_EX) === false) {
            trigger_error('Failed to write cache file: ' . $filename, E_USER_WARNING);
        }
    }

    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                trigger_error('Failed to create directory: ' . $directory, E_USER_WARNING);
                return;
            }
        }
    }

    private function isCacheExpired($filename, $delayMinutes)
    {
        if ($delayMinutes <= 0) {
            return false;
        }
        $filetime = filemtime($filename);
        return $filetime === false || (time() - $filetime) / 60 > $delayMinutes;
    }
}
