<?php

beforeEach(function () {
    $this->tmpDir = cacheTestScratchDir() . '/helper-' . uniqid();
    mkdir($this->tmpDir, 0777, true);

    \Leaf\Config::reset();
    \Leaf\Config::singleton('cache', function () {
        return (new \Leaf\Cache())->init([
            'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
        ]);
    });
});

afterEach(function () {
    \Leaf\Config::reset();
    deleteCacheTestDir($this->tmpDir);
});

test('cache() with no args returns the repository', function () {
    expect(cache())->toBeInstanceOf(\Illuminate\Cache\Repository::class);
});

test('cache(key) returns null when key is unset', function () {
    expect(cache('missing'))->toBeNull();
});

test('cache(key, ttl, value) writes and returns the value', function () {
    expect(cache('key', 600, 'value'))->toBe('value');
    expect(cache('key'))->toBe('value');
});

test('shorthand put does not overwrite an existing value', function () {
    cache('key', 600, 'original');

    expect(cache('key', 600, 'replacement'))->toBe('original');
    expect(cache('key'))->toBe('original');
});

test('two-arg form stores forever', function () {
    expect(cache('key2', 'value'))->toBe('value');
    expect(cache('key2'))->toBe('value');
});

test('closure values are evaluated before storing', function () {
    expect(cache('key3', 600, fn () => 'computed'))->toBe('computed');
    expect(cache('key3'))->toBe('computed');
});

test('callable-named strings are stored literally, not executed', function () {
    // regression: is_callable('strtolower') is true, so the old helper
    // executed plain strings that happened to be function names
    expect(cache('key4', 600, 'strtolower'))->toBe('strtolower');
    expect(cache('key4'))->toBe('strtolower');
});
