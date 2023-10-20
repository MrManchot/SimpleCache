# SimpleCache

A simple and efficient PHP library for cache management.

## Caractéristiques

- Easy to use
- Supports multiple data types: strings, arrays, objects
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

### Setting a Cache Value

```php
$cache->set('cle', 'valeur');
```

### Retrieving a Cache Value

```php
$valeur = $cache->get('cle');
```

### Clearing the Cache

```php
$cache->clear();
```

### Bypassing the Cache

```php
$cache->shouldBypassCache = true;
```

## Tests

To run the tests, use PHPUnit:


```bash
phpunit test/
```