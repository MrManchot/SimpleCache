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
            $stock_cache_time = time() - @filectime($filename);

            $delaySeconds = $delayMinutes * 60;  // Convert minutes to seconds

            if ($delaySeconds > 0 && $stock_cache_time > $delaySeconds) {
                $this->clear($key);
                return false;
            }

            $content = file_get_contents($filename);
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
        return file_put_contents($filename, $encodedValue);
    }

    public function clear($pattern = '*')
    {
        $files = glob($this->cacheDir . $pattern);
        $files[] = $this->getCacheFilePath($pattern);

        array_map('unlink', array_filter($files, 'is_file'));
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

    private function decodeContent($content, $type)
    {
        if ($type === 'array' || $type === 'object') {
            try {
                return unserialize($content);
            } catch(Exception $e) {
                return false;
            }
        }

        return $content;
    }
}
