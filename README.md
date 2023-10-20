# SimpleCache

A simple and efficient PHP library for cache management.

## Features

- Easy to use
- Supports multiple data types: strings, arrays, objects, and booleans
- Allows setting cache expiration time
- Option to bypass cache

## Installation

Use Composer to install this library:

```bash
composer require mrmanchot/simple-cache
```

## Usage

### Initialization

```php
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
// Set cache with a 10-minute expiration time
$cache->set('key', 'value');

// Get cache, valid for 10 minutes
$value = $cache->get('key', 'string', 10);
```

### Storing Arrays, Objects, and Booleans

You can also store arrays, objects, and booleans.

```php
// Storing an array
$cache->set('array_key', ['a' => 1, 'b' => 2]);

// Retrieving an array
$array = $cache->get('array_key', 'array');

// Storing an object
$object = new stdClass();
$object->property = 'value';
$cache->set('object_key', $object);

// Retrieving an object
$object = $cache->get('object_key', 'object');

// Storing a boolean
$cache->set('boolean_key', true);

// Retrieving a boolean
$boolean = $cache->get('boolean_key', 'bool');
```

### Using Subdirectories as Keys

You can use subdirectories in keys for better organization.

```php
// Set cache in a subdirectory
$cache->set('user/1', 'value');

// Get cache from a subdirectory
$value = $cache->get('user/1');
```

### Clearing the Cache

The clear method allows you to remove cached items based on a pattern. This is useful for batch invalidation of cache items.

```php
// Clear a specific cache item
$cache->clear('key');

// Clear all cache items in a subdirectory
$cache->clear('user/*');

// Clear all cache items
$cache->clear('*');
```
