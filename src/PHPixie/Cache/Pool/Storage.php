<?php

namespace PHPixie\Cache\Pool;

use PHPixie\Cache\Builder;
use PHPixie\Cache\Drivers\Driver;
use PHPixie\Cache\Exception;
use PHPixie\Cache\Item;
use PHPixie\Cache\Pool;
use PHPixie\Slice\Data;
use Psr\Cache\CacheItemInterface;

class Storage implements Pool
{
    /** @var  Builder */
    protected $builder;

    /** @var  string */
    protected $name;

    /** @var  Driver */
    protected $driver;

    /** @var  \DateInterval */
    protected $defaultInterval;

    protected $deferredItems = array();

    /**
     * @param Builder $builder
     * @param string $name
     * @param Driver $driver
     * @param Data $configData
     */
    public function __construct($builder, $name, $driver, $configData)
    {
        $this->builder = $builder;
        $this->name = $name;
        $this->driver = $driver;

        $defaultExpiry = $configData->get('defaultExpiry');

        if($defaultExpiry !== null) {
            if(is_int($defaultExpiry)) {
                $defaultExpiry = 'PT'.$defaultExpiry.'S';
            }

            $this->defaultInterval = new \DateInterval($defaultExpiry);
        }
    }

    public function name()
    {
        return $this->name;
    }

    /**
     * @return Driver
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * Create an Item without getting it from cache first
     * @param string $key
     * @param mixed $value
     * @return Item
     */
    public function createItem($key, $value = null)
    {
        return $this->driver()->buildItem($key, false, $value);
    }

    public function prefixedPool($prefix)
    {
        return $this->builder->prefixedPool($this, $prefix);
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $item = $this->getItem($key);
        if(!$item->isHit()) {
            return $default;
        }

        return $item->get();
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $item = $this->createItem($key, $value);
        $item->expiresAfter($ttl);
        return $this->save($item);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return $this->deleteItem($key);
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        $items = $this->driver()->getItems($keys);
        $result = array();

        /** @var Item $item */
        foreach($items as $item) {
            $key = $item->getKey();

            if($item->isHit()) {
                $result[$key] = $item->get();
            } else {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $item = $this->createItem($key, $value);
            $item->expiresAfter($ttl);
            $this->saveDeferred($item);
        }

        $this->commit();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        return $this->deleteItems($keys);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return $this->hasItem($key);
    }

    /**
     * @inheritdoc
     */
    public function getItem($key)
    {
        return $this->driver()->getItem($key);
    }

    public function getItems(array $keys = array())
    {
        return $this->driver()->getItems($keys);
    }

    /**
     * @inheritdoc
     */
    public function hasItem($key)
    {
        return $this->driver()->hasItem($key);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->driver()->clear();
    }

    /**
     * @inheritdoc
     */
    public function deleteItem($key)
    {
        return $this->driver()->deleteItem($key);
    }

    /**
     * @inheritdoc
     */
    public function deleteItems(array $keys)
    {
        return $this->driver()->deleteItems($keys);
    }

    /**
     * @inheritdoc
     */
    public function save(CacheItemInterface $item)
    {
        $item = $this->assertItem($item);
        return $this->driver()->saveItem($item, $this->expiresAt($item));
    }

    /**
     * @inheritdoc
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredItems[]= $this->assertItem($item);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        $expiryTimes = array();
        /** @var Item $item */
        foreach($this->deferredItems as $item) {
            $expiryTimes[$item->getKey()] = $this->expiresAt($item);
        }

        $this->driver()->saveMultiple($this->deferredItems, $expiryTimes);
        $this->deferredItems = array();
        return true;
    }

    protected function expiresAt(Item $item)
    {
        $time = $item->getExpiresAt();

        if($time === null && $this->defaultInterval !== null) {
            $time = (new \DateTime())->add($this->defaultInterval);
        }

        return $time;
    }

    /**
     * @param CacheItemInterface $item
     * @return Item
     * @throws Exception
     */
    protected function assertItem(CacheItemInterface $item)
    {
        if(!($item instanceof Item)) {
            throw new Exception('Only instances of \PHPixie\Cache\Item are allowed');
        }

        return $item;
    }
}