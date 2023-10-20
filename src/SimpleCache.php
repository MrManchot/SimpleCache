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

        if (is_readable($filename)) {
            if ($delayMinutes > 0) {
                $stock_cache_time = (time() - filemtime($filename)) / 60;
                if ($stock_cache_time > $delayMinutes) {
                    $this->clear($key);
                    return false;
                }
            }

            $content = file_get_contents($filename);
            if ($content === false || $content === '') {
                $this->clear($key);
                return false;
            }

            return $this->decodeContent($content, $type);
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
        file_put_contents($filename, $encodedValue, LOCK_EX);
    }

    public function clear($pattern = '*')
    {
        $files = glob($this->cacheDir . $pattern);
        $files[] = $this->getCacheFilePath($pattern);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
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
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return is_string($value) ? $value : json_encode($value);
    }

    private function decodeContent($content, $type)
    {
        if ($type === 'bool') {
            return $content === 'true';
        }
        return $type === 'string' ? $content : json_decode($content, $type === 'array');
    }
}
