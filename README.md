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
