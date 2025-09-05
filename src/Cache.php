<?php

namespace Leaf;

use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;

/**
 * Leaf Cache
 * ---
 * Store and retrieve cached data, avoid repetitive expensive operation
 */
class Cache
{
    /**
     * The cache store instance
     */
    protected $store;

    protected $repository;

    /**
     * Set the cache store instance
     * @param array $config
     * @return void
     */
    public function init(array $config = [])
    {
        $defaultStorePath = function_exists('StoragePath') ? StoragePath('framework/cache') : '/cache';

        $mvcConfig = function_exists('MvcConfig') ? MvcConfig('cache') ?? [] : [];
        $config = array_merge([
            'default' => 'file',
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => $defaultStorePath,
                ],
            ],
            'prefix' => 'leaf_cache',
        ], $mvcConfig, $config);

        $container = new Container;

        $container->singleton('files', function () {
            return new Filesystem;
        });

        $container->instance('config', new Config([
            'cache' => $config,
        ]));

        $container->singleton('cache', function ($container) {
            return new CacheManager($container);
        });

        $this->store = $container;
        $this->repository = new Repository(new FileStore(
            $this->store->make('files'),
            $this->store['config']['cache.stores.file']['path']
        ));

        return $this;
    }

    /**
     * Get the cache store instance
     * @return \Illuminate\Cache\Repository
     */
    public function store()
    {
        return $this->repository;
    }
}
