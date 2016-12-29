<?php

namespace PHPixie\Cache\Pool;

use PHPixie\Cache\Pool;
use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\DateInterval;

abstract class Proxy implements Pool
{
    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $this->pool()->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->pool()->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->pool()->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->pool()->getMultiple($keys, $default);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->pool()->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        return $this->pool()->deleteMultiple($keys);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->pool()->has($key);
    }

    /**
     * @inheritDoc
     */
    public function getItem($key)
    {
        return $this->pool()->getItem($key);
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = array())
    {
        return $this->pool()->getItems($keys);
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key)
    {
        return $this->pool()->hasItem($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->pool()->clear();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key)
    {
        return $this->pool()->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys)
    {
        return $this->pool()->deleteItems($keys);
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item)
    {
        return $this->pool()->save($item);
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->pool()->saveDeferred($item);
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        return $this->pool()->commit();
    }

    /**
     * @inheritDoc
     */
    public function createItem($key, $value = null)
    {
        return $this->pool()->createItem($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function prefixedPool($prefix)
    {
        return $this->pool()->prefixedPool($prefix);
    }

    /**
     * @return Pool
     */
    abstract protected function pool();
}