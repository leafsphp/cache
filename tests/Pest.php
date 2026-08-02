<?php

namespace Leaf {
    // leafs/cache does not declare leafs/leaf as a dependency, but the
    // cache() helper uses \Leaf\Config. Provide a minimal polyfill with
    // the same behaviour when the real class is not installed.
    if (!class_exists(\Leaf\Config::class)) {
        class Config
        {
            protected static array $items = [];
            protected static array $singletons = [];

            public static function set($key, $value = null): void
            {
                static::$items[$key] = $value;
            }

            public static function getStatic($key)
            {
                return static::$items[$key] ?? static::$singletons[$key] ?? null;
            }

            public static function singleton($key, callable $callback): void
            {
                static::$singletons[$key] = $callback;
            }

            public static function get($key)
            {
                if (array_key_exists($key, static::$items)) {
                    return static::$items[$key];
                }

                if (array_key_exists($key, static::$singletons)) {
                    return static::$items[$key] = (static::$singletons[$key])();
                }

                return null;
            }

            public static function reset(): void
            {
                static::$items = [];
                static::$singletons = [];
            }
        }
    }
}

namespace {
    function cacheTestScratchDir(): string
    {
        $dir = sys_get_temp_dir() . '/leaf-cache-tests-' . getmypid();

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    function deleteCacheTestDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
