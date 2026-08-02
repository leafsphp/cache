<?php

if (!function_exists('cache')) {
    /**
     * Cache data for an expensive operation
     * Usage:
     *   cache()->put('foo', 'bar', 600);
     *   cache()->get('foo');
     *   cache('foo'); // shorthand get
     *   cache('foo', 600, 'bar'); // shorthand put
     */
    function cache(?string $key = null, $ttl = null, $value = null)
    {
        if (!\Leaf\Config::getStatic('cache')) {
            \Leaf\Config::singleton('cache', function () {
                return (new \Leaf\Cache())->init();
            });
        }

        /** @var \Leaf\Cache */
        $cache = \Leaf\Config::get('cache');

        if ($key === null) {
            return $cache->store();
        }

        if ($ttl === null) {
            $cached = $cache->store()->get($key);

            if ($cached === null && function_exists('crash')) {
                crash()->leaveCrumb("cache miss: $key", 'cache', [], false);
            }

            return $cached;
        }

        if ($cache->store()->has($key)) {
            return $cache->store()->get($key);
        }

        if ($value === null) {
            $value = $ttl;
            $ttl = null;
        }

        if ($value instanceof \Closure) {
            $value = $value();
        }

        $cache->store()->put($key, $value, $ttl);

        if (function_exists('crash')) {
            crash()->leaveCrumb("cache write: $key", 'cache', [], false);
        }

        return $value;
    }
}
