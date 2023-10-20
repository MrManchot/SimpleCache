<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/SimpleCache.php';

class SimpleCacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        // Initialisez le cache avec un répertoire temporaire pour les tests
        $this->cache = new SimpleCache(__DIR__ . '/../cache/');
    }

    protected function tearDown(): void
    {
        // Nettoyez le cache après chaque test
        $this->cache->clear();
    }

    public function testSetAndGetCache()
    {
        $this->cache->set('key', 'value');
        $this->assertEquals('value', $this->cache->get('key'));
    }

    public function testCacheExpiration()
    {
        $this->cache->set('key', 'value');
        sleep(3);  // Attendre 3 secondes
        $this->assertFalse($this->cache->get('key', 'string', 1 / 30));  // 1/30 minute = 2 secondes
    }

    public function testShouldBypassCache()
    {
        $this->cache->shouldBypassCache = true;
        $this->cache->set('key', 'value');
        $this->assertFalse($this->cache->get('key'));
    }

    public function testClearCache()
    {
        $this->cache->set('key', 'value');
        $this->cache->clear();
        $this->assertFalse($this->cache->get('key'));
    }

    public function testArraySerialization()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->cache->set('array_key', $array);
        $this->assertEquals($array, $this->cache->get('array_key', 'array'));
    }
}
