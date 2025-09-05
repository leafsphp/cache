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

        $store = \Leaf\Config::get('cache');

        if ($key === null) {
            return $store;
        }

        if ($ttl === null) {
            return $store->get($key);
        }

        if ($value === null) {
            $value = $ttl;
            $ttl = null;
        }

        if (is_callable($value)) {
            $value = $value();
        }

        $store->put($key, $value, $ttl);

        return $value;
    }
}
