<?php

namespace PHPixie\Cache\Drivers\Type;

use PHPixie\Cache\Drivers\Driver;
use PHPixie\Cache\Item;

class Memory extends Driver
{
    protected $values = array();
    protected $expiryTimes = array();

    public function hasItem($key)
    {
        $this->cleanupCheck();
        if(!isset($this->values[$key])) {
            return false;
        }

        $time = $this->expiryTimes[$key];

        if($time !== null && $time < time()) {
            unset($this->values[$key]);
            unset($this->expiryTimes[$key]);
            return false;
        }

        return true;
    }

    public function getItems(array $keys = array())
    {
        $this->cleanupCheck();
        $items = array();
        foreach($keys as $key) {
            $items[$key] = $this->getItemWithoutCleanup($key);
        }

        return $items;
    }

    public function deleteItems(array $keys = array())
    {
        $this->cleanupCheck();
        $this->removeKeys($keys);
        return true;
    }

    public function clear()
    {
        $this->values = array();
        $this->expiryTimes = array();
        return true;
    }

    public function saveItem($item, $expiresAt)
    {
        $this->cleanupCheck();
        $key = $item->getKey();
        $this->values[$key] = $item->get();

        if($expiresAt !== null) {
            $expiresAt = $expiresAt->getTimestamp();
        }

        $this->expiryTimes[$key] = $expiresAt;
        return true;
    }

    public function getItem($key)
    {
        $this->cleanupCheck();
        return $this->getItemWithoutCleanup($key);
    }

    public function cleanup()
    {
        $remove = array();
        $now = time();

        foreach($this->expiryTimes as $key => $time) {
            if($time !== null && $time < $now) {
                $remove[]= $key;
            }
        }

        $this->removeKeys($remove);
        return true;
    }

    protected function getItemWithoutCleanup($key)
    {
        if($this->hasItem($key)) {
            /** @var Item $item */
            return $this->buildItem($key, true, $this->values[$key]);
        }

        return $this->buildItem($key, false);
    }

    protected function removeKeys($keys)
    {
        foreach($keys as $key) {
            unset($this->values[$key]);
            unset($this->expiryTimes[$key]);
        }
    }
}