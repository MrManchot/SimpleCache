<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/SimpleCache.php';

class SimpleCacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        // Initialize the cache with a temporary directory for tests
        $this->cache = new SimpleCache(__DIR__ . '/../cache/');
    }

    protected function tearDown(): void
    {
        // Clear the cache after each test
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
        sleep(3);  // Wait for 3 seconds
        $this->assertFalse($this->cache->get('key', 'string', 1 / 30));  // 1/30 minute = 2 seconds
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

    public function testObjectSerialization()
    {
        $object = new stdClass();
        $object->property = 'value';
        $this->cache->set('object_key', $object);
        $retrievedObject = $this->cache->get('object_key', 'object');
        $this->assertEquals($object->property, $retrievedObject->property);
    }

    public function testBooleanSerialization()
    {
        $this->cache->set('boolean_key', true);
        $this->assertTrue($this->cache->get('boolean_key', 'bool'));
    }

    public function testSubdirectoryKeys()
    {
        $this->cache->set('user/1', 'value');
        $this->assertEquals('value', $this->cache->get('user/1'));
    }

    public function testClearCacheWithPattern()
    {
        $this->cache->set('user/1', 'value1');
        $this->cache->set('user/2', 'value2');
        $this->cache->clear('user/*');
        $this->assertFalse($this->cache->get('user/1'));
        $this->assertFalse($this->cache->get('user/2'));
    }
}
