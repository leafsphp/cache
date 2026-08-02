<?php

beforeEach(function () {
    $this->tmpDir = cacheTestScratchDir() . '/store-' . uniqid();
    mkdir($this->tmpDir, 0777, true);
});

afterEach(function () {
    deleteCacheTestDir($this->tmpDir);
});

test('init returns the cache instance', function () {
    $cache = new \Leaf\Cache();

    expect($cache->init([
        'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
    ]))->toBe($cache);
});

test('store returns an illuminate cache repository', function () {
    $cache = (new \Leaf\Cache())->init([
        'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
    ]);

    expect($cache->store())->toBeInstanceOf(\Illuminate\Cache\Repository::class);
});

test('put/get/has/forget round-trip through the file store', function () {
    $store = (new \Leaf\Cache())->init([
        'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
    ])->store();

    expect($store->has('name'))->toBeFalse();

    $store->put('name', 'leaf', 600);

    expect($store->has('name'))->toBeTrue();
    expect($store->get('name'))->toBe('leaf');

    // real cache files should have been written into the tmp dir
    $files = iterator_to_array(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmpDir, FilesystemIterator::SKIP_DOTS)
        )
    );
    expect(count(array_filter($files, fn ($f) => $f->isFile())))->toBeGreaterThan(0);

    $store->forget('name');

    expect($store->has('name'))->toBeFalse();
});

test('init honors a configured file path', function () {
    $store = (new \Leaf\Cache())->init([
        'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
    ])->store();

    $store->put('configured', 'yes', 600);

    $files = array_filter(
        iterator_to_array(new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmpDir, FilesystemIterator::SKIP_DOTS)
        )),
        fn ($f) => $f->isFile()
    );

    expect(count($files))->toBeGreaterThan(0);
});

test('init with no custom config uses cwd storage path', function () {
    $cwd = getcwd();
    $scratch = cacheTestScratchDir() . '/cwd-' . uniqid();
    mkdir($scratch, 0777, true);
    chdir($scratch);

    try {
        $store = (new \Leaf\Cache())->init()->store();
        $store->put('default-store', 'works', 600);

        expect($store->get('default-store'))->toBe('works');
        expect(is_dir($scratch . '/storage/framework/cache'))->toBeTrue();
    } finally {
        chdir($cwd);
        deleteCacheTestDir($scratch);
    }
});

test('values expire after their ttl', function () {
    $store = (new \Leaf\Cache())->init([
        'stores' => ['file' => ['driver' => 'file', 'path' => $this->tmpDir]],
    ])->store();

    $store->put('short-lived', 'value', 1);
    expect($store->get('short-lived'))->toBe('value');

    sleep(2);

    expect($store->get('short-lived'))->toBeNull();
});
