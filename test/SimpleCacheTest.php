<?php

use Mrmanchot\SimpleCache\SimpleCache;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/SimpleCache.php';

class SimpleCacheTest extends TestCase
{
    private $cache;
    private $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/simple_cache_test/';
        $this->cache = new SimpleCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cache->clear('*');
    }

    public function testSetAndGetCache()
    {
        $key = 'testKey';
        $value = 'testValue';

        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testCacheExpiration()
    {
        $key = 'expireKey';
        $value = 'expireValue';

        $this->cache->set($key, $value);
        sleep(2);
        $this->assertNull($this->cache->get($key, 1 / 60));
    }

    public function testClearCache()
    {
        $key = 'clearKey';
        $value = 'clearValue';

        $this->cache->set($key, $value);
        $this->cache->clear($key);
        $this->assertNull($this->cache->get($key));
    }

    public function testCacheWithDifferentDataTypes()
    {
        $this->cache->set('string', 'Hello World');
        $this->cache->set('integer', 123);
        $this->cache->set('array', ['a' => 'apple', 'b' => 'banana']);
        $this->cache->set('object', (object) ['property' => 'value']);
        $this->cache->set('boolean', true);

        $this->assertSame('Hello World', $this->cache->get('string'));
        $this->assertSame(123, $this->cache->get('integer'));
        $this->assertEquals(['a' => 'apple', 'b' => 'banana'], $this->cache->get('array'));
        $this->assertEquals((object) ['property' => 'value'], $this->cache->get('object'));
        $this->assertTrue($this->cache->get('boolean'));
    }

    public function testCacheMissReturnsNull()
    {
        $this->assertNull($this->cache->get('nonExistentKey'));
    }

    public function testBypassCache()
    {
        $this->cache->shouldBypassCache = true;

        $this->cache->set('key', 'value');
        $this->assertNull($this->cache->get('key'));

        $this->cache->shouldBypassCache = false;
    }

    public function testCacheDirectoryCreation()
    {
        $nonExistentDir = $this->cacheDir . 'nonExistentDir/';
        $cache = new SimpleCache($nonExistentDir);

        $this->assertDirectoryExists($nonExistentDir);
        rmdir($nonExistentDir);
    }

    public function testPersistenceAfterReinstantiation()
    {
        $key = 'persistentKey';
        $value = 'persistentValue';

        $this->cache->set($key, $value);

        $newCacheInstance = new SimpleCache($this->cacheDir);
        $this->assertEquals($value, $newCacheInstance->get($key));
    }

    public function testInvalidCachePathHandling()
    {
        $invalidDir = '/invalid/path/';
        $this->expectException(\Exception::class);
        new SimpleCache($invalidDir);
    }

    public function testInvalidKeyHandling()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->cache->set(['arrayKey'], 'value');
    }
}
