<?php

class SimpleCache
{
    private $cacheDir;
    public $shouldBypassCache = false;

    public function __construct($cacheDir)
    {
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->cacheDir = $cacheDir;
    }


    public function get($key, $type = 'string', $delayMinutes = 0)
    {
        if ($this->shouldBypassCache) {
            return false;
        }

        $filename = $this->getCacheFilePath($key);

        if (file_exists($filename)) {
            $stock_cache_time = (time() - @filectime($filename)) / 60;

            if ($delayMinutes > 0 && $stock_cache_time > $delayMinutes) {
                $this->clear($key);
                return false;
            }

            $content = @file_get_contents($filename);
            if ($content === false || strlen($content) === 0) {
                $this->clear($key);
                return false;
            }

            return $this->decodeContent($content, $type, $key);
        }

        return false;
    }


    public function set($key, $value)
    {
        $filename = $this->getCacheFilePath($key);

        if (strpos($key, '/') !== false) {
            $this->ensureDirectoryExists(dirname($filename));
        }

        $encodedValue = $this->encodeContent($value);
        if (file_put_contents($filename, $encodedValue, LOCK_EX) === false) {
            error_log('Failed to write cache item with key: ' . $key);
        }
    }

    public function clear($pattern = '*')
    {
        $files = glob($this->cacheDir . $pattern);
        $files[] = $this->getCacheFilePath($pattern);

        array_map(function ($file) {
            if (is_file($file)) {
                unlink($file);
            }
        }, $files);
    }

    private function getCacheFilePath($key)
    {
        return $this->cacheDir . $key . '.txt';
    }

    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function encodeContent($value)
    {
        if (is_array($value) || is_object($value)) {
            return serialize($value);
        }

        return $value;
    }

    private function decodeContent($content, $type, $key)
    {
        if ($type === 'array' || $type === 'object') {
            $unserialized = @unserialize($content);
            if ($unserialized === false && $content !== 'b:0;') {
                error_log('Failed to unserialize cache item with key: ' . $key);
                $this->clear($key);
                return false;
            }
            return $unserialized;
        }

        return $content;
    }
}
