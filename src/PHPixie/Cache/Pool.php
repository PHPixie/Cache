<?php

namespace PHPixie\Cache;

use PHPixie\Cache\Pool\Prefixed;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface Pool extends CacheItemPoolInterface, CacheInterface
{
    /**
     * Create an Item without getting it from cache first
     * @param string $key
     * @param mixed $value
     * @return Item
     */
    public function createItem($key, $value = null);

    /**
     * create a namespaced pool with a defined key prefix
     * @param $prefix
     * @return Prefixed
     */
    public function prefixedPool($prefix);
}