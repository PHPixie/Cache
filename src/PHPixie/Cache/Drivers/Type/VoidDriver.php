<?php

namespace PHPixie\Cache\Drivers\Type;

use PHPixie\Cache\Drivers\Driver;

class VoidDriver extends Driver
{
    public function hasItem($key)
    {
        return false;
    }

    public function getItems(array $keys = array())
    {
        return $this->buildItems($keys, array());
    }

    public function deleteItems(array $keys = array())
    {
        return true;
    }

    public function clear()
    {
        return true;
    }

    public function saveItem($item, $expiresAt)
    {

    }

    public function getItem($key)
    {
        return $this->buildItem($key, false);
    }
}