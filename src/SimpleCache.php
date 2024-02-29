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
        if (!is_string($key) || strpos($key, '..') !== false) {
            throw new \InvalidArgumentException('Invalid key.');
        }

        $filename = $this->getCacheFilePath($key);
        $this->ensureDirectoryExists(dirname($filename));
        $this->writeCacheFile($filename, $value);
    }

    public function clear($pattern = '*')
    {
        $files = glob($this->cacheDir . $pattern);
        $files[] = $this->getCacheFilePath($pattern);
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
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
                    throw new \Exception('Failed to create parent directory for cache: ' . $parentDir);
                }
            }

            if (!is_writable($parentDir)) {
                throw new \Exception("Parent directory ($parentDir) is not writable.");
            }

            if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
                throw new \Exception('Failed to create cache directory: ' . $cacheDir);
            }
        }

        $this->cacheDir = $cacheDir;
    }


    private function getCacheFilePath($key)
    {
        return $this->cacheDir . $key . '.txt';
    }

    private function isCacheValid($filename, $delayMinutes)
    {
        return is_readable($filename) && !$this->isCacheExpired($filename, $delayMinutes);
    }

    private function readCacheFile($filename)
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }

        $cachedValue = @unserialize($content);
        if ($cachedValue === false && $content !== serialize(false)) {
            return null;
        }

        return $cachedValue;
    }

    private function writeCacheFile($filename, $value)
    {
        $encodedValue = serialize($value);
        if (file_put_contents($filename, $encodedValue, LOCK_EX) === false) {
            throw new \Exception('Failed to write cache file.');
        }
    }

    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \Exception('Failed to create directory: ' . $directory);
        }
    }

    private function isCacheExpired($filename, $delayMinutes)
    {
        if($delayMinutes <= 0) {
            return false;
        }
        $filetime = filemtime($filename);
        return $filetime === false || (time() - $filetime) / 60 > $delayMinutes;
    }
}
