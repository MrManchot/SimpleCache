<?php

namespace Mrmanchot\SimpleCache;

class SimpleCache
{
    private $cacheDir;
    public $shouldBypassCache = false;

    public function __construct($cacheDir = null)
    {
        if (is_null($cacheDir)) {
            $cacheDir = __DIR__ . '/cache/';
        }
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->cacheDir = $cacheDir;
    }

    public function get($key, $delayMinutes = 0)
    {

        if ($this->shouldBypassCache) {
            return null;
        }

        $filename = $this->getCacheFilePath($key);

        if (is_readable($filename)) {
            if ($delayMinutes > 0 && $this->isCacheExpired($filename, $delayMinutes)) {
                $this->clear($key);
                return null;
            }

            $content = file_get_contents($filename);
            if ($content === false || $content === '') {
                $this->clear($key);
                return null;
            }

            $cachedValue = @unserialize($content);
            if ($cachedValue === false && $content !== serialize(false)) {
                return null;
            }
            return $cachedValue;
        }

        return null;
    }

    public function set($key, $value)
    {
        $filename = $this->getCacheFilePath($key);
        if (strpos($key, '/') !== false) {
            $directory = dirname($filename);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        $encodedValue = serialize($value);
        file_put_contents($filename, $encodedValue, LOCK_EX);
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

    private function getCacheFilePath($key)
    {
        return $this->cacheDir . $key . '.txt';
    }

    private function isCacheExpired($filename, $delayMinutes)
    {
        return (time() - filemtime($filename)) / 60 > $delayMinutes;
    }
}
