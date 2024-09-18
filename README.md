# SimpleCache

SimpleCache is a lightweight and efficient PHP caching library, designed for ease of use and flexibility. Whether you're caching strings, arrays, objects, or booleans, SimpleCache provides a straightforward and intuitive API to speed up your PHP applications. With features like cache expiration, subdirectory organization, and security measures, it's an ideal solution for both small projects and large-scale applications.

## Features

- Easy to use
- Supports multiple data types: strings, arrays, objects, and booleans
- Allows setting cache expiration time
- Option to bypass cache
- Sanitizes cache keys for security
- Graceful error handling with warnings instead of exceptions

## Installation

Use Composer to install this library:

```bash
composer require mrmanchot/simple-cache
```

## Usage

### Initialization

```php
use Mrmanchot\SimpleCache\SimpleCache;
require 'vendor/autoload.php';

$cache = new SimpleCache('/path/to/cache/directory/');
```

### Basic Usage

```php
// Set cache
$cache->set('key', 'value');

// Get cache
$value = $cache->get('key');
```

### Using Expiration Time

You can specify an expiration time in minutes using the `$delayMinutes` parameter.

```php
// Set cache
$cache->set('key', 'value');

// Get cache, valid for 10 minutes
$value = $cache->get('key', 10);
```

### Storing Arrays, Objects, and Booleans

You can also store arrays, objects, and booleans.

```php
// Storing an array
$cache->set('array_key', ['a' => 1, 'b' => 2]);

// Retrieving an array
$array = $cache->get('array_key');

// Storing an object
$object = new stdClass();
$object->property = 'value';
$cache->set('object_key', $object);

// Retrieving an object
$object = $cache->get('object_key');

// Storing a boolean
$cache->set('boolean_key', true);

// Retrieving a boolean
$boolean = $cache->get('boolean_key');
```

### Handling Cache Hits and Misses

When retrieving data from the cache, you can distinguish between a cache hit and a cache miss by checking if the returned value is null. If the value is null, it indicates that the data is not present in the cache (a cache miss), and you may need to compute or generate the value. If the value is not null, it is a cache hit, and you can use the cached value directly.

Here's an example of how to handle cache hits and misses:

```php
$cachedValue = $cache->get('some_key');
if ($cachedValue === null) {
    // The value is not in the cache, compute/generate the value
}
```


### Using Subdirectories as Keys

You can use subdirectories in keys for better organization. Note that keys are sanitized for security purposes, so only certain characters are allowed.

```php
// Set cache in a subdirectory
$cache->set('user/1', 'value');

// Get cache from a subdirectory
$value = $cache->get('user/1');
```

### Clearing the Cache

The `clear` method allows you to remove cached items based on a pattern. This is useful for batch invalidation of cache items.

```php
// Clear a specific cache item
$cache->clear('key');

// Clear all cache items in a subdirectory
$cache->clear('user/*');

// Clear all cache items
$cache->clear('*');
```

### Run PHPUnit

```
phpunit test/SimpleCacheTest.php 
```