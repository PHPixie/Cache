<?php

namespace PHPixie\Cache\Drivers;

use PHPixie\Cache\Builder;
use PHPixie\Cache\Item;
use DateTime;
use PHPixie\Slice\Data;

abstract class Driver
{
    /** @var  Builder */
    protected $builder;

    /** @var  Data */
    protected $configData;

    /** @var  int */
    protected $cleanupProbability;

    /**
     * @param Builder $builder
     * @param Data $configData
     */
    public function __construct($builder, $configData)
    {
        $this->builder    = $builder;
        $this->configData = $configData;
    }

    /**
     * Delete item
     *
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        $this->deleteItems(array($key));
        return true;
    }

    /**
     * Save multiple items
     *
     * @param array(Item) $items
     * @param array $expiryTimes corresponding expiry times
     * @return bool
     */
    public function saveMultiple($items, $expiryTimes)
    {
        /** @var Item $item */
        foreach($items as $item) {
            $this->saveItem($item, $expiryTimes[$item->getKey()]);
        }

        return true;
    }

    /**
     * Build Items
     *
     * @param array $keys
     * @param array $data
     * @return array
     */
    protected function buildItems($keys, $data)
    {
        $result = array();
        foreach($keys as $key) {
            if(array_key_exists($key, $data)) {
                $result[$key]= $this->buildItem($key, true, $data[$key]);
            } else {
                $result[$key]= $this->buildItem($key, false);
            }
        }

        return $result;
    }

    /**
     * Build Item object
     *
     * @param string $key
     * @param bool $isHit
     * @param mixed $value stored value
     * @return Item
     */
    public function buildItem($key, $isHit, $value = null)
    {
        return new Item($key, $isHit, $value);
    }

    /**
     * Check if cleanup is required. And perform cleanup(if required)/
     *
     * @return void
     */
    protected function cleanupCheck()
    {
        if($this->cleanupProbability === null) {
            $this->cleanupProbability = $this->configData->get('cleanupProbability', 10);
        }

        if(rand(1, 1000) <= $this->cleanupProbability) {
            $this->cleanup();
        }
    }

    /**
     * Default cleanup implementation.
     *
     * @return void
     */
    public function cleanup()
    {}

    /**
     * @param string $key
     * @return Item
     */
    abstract public function getItem($key);

    /**
     * @param Item $item
     * @param DateTime $expiresAt
     * @return mixed
     */
    abstract public function saveItem($item, $expiresAt);

    /**
     * @param $key
     * @return bool
     */
    abstract public function hasItem($key);

    /**
     * @param array $keys
     * @return array
     */
    abstract public function getItems(array $keys = array());


    /**
     * @param array $keys
     * @return bool
     */
    abstract public function deleteItems(array $keys = array());

    /**
     * @return bool
     */
    abstract public function clear();
}