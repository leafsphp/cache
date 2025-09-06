<!-- markdownlint-disable no-inline-html -->
<p align="center">
    <br><br>
    <img src="https://leafphp.dev/logo-circle.png" height="100"/>
    <br><br>
</p>

# Leaf Cache

Cache module for Leaf.

```bash
leaf install cache
```

Or with composer:

```bash
composer require leafs/cache
```

## Usage

You can use the cache module to store data, usually to avoid repeated expensive operations.

Cache data forever:

```php
$value = cache(
    'key',
    function() {
        // expensive operation
        return 'value';
    },
);
```

Cache data for a specific time (in seconds):

```php
$value = cache(
    'key',
    600, // 10 minutes
    function() {
        // expensive operation
        return 'value';
    },
);
```

Unlike other frameworks, the cache function will always return the cached value if it exists, meaning that the closure will only be executed if the cache key does not exist. So you don't need to check if the value exists before calling the cache function.

## Extended Usage

The cache module is built on top of the Illuminate Cache component, so you can use all the features of that component. To do that, you can call the `cache()` function without any parameters to get the cache repository instance:

```php
cache()->put('key', 'value', 600); // cache for 10 minutes
$value = cache()->get('key');
```

For most use cases, the `cache()` function is sufficient, but if you need more control, you can use the cache repository instance directly.

You can use this to manually overwrite a cache key:

```php
// will not set the cache if it already exists
cache('key', 600, 'new value'); // cache for 10 minutes

// will always set the cache, even if it already exists
cache()->put('key', 'new value', 600); // cache for 10 minutes
```
