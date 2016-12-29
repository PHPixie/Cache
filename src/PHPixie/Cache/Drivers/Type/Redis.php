<?php

namespace PHPixie\Cache\Drivers\Type;

use PHPixie\Cache\Drivers\Driver;
use Predis\Client;

class Redis extends Driver
{
    /** @var Client */
    protected $client;

    public function hasItem($key)
    {
        return (bool) $this->client()->exists($key);
    }

    public function getItems(array $keys = array())
    {
        if(empty($keys)) {
            return array();
        }

        $client = $this->client();

        $data = $client->mget($keys);
        $values = array();
        foreach($keys as $i => $key) {
            $values[$key] = unserialize($data[$i]);
        }

        return $this->buildItems($keys, $values);
    }

    public function deleteItems(array $keys = array())
    {
        $this->client()->del($keys);
        return true;
    }

    public function clear()
    {
        $this->client()->flushdb();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function saveItem($item, $expiresAt)
    {
        $client = $this->client();
        $key = $item->getKey();

        $value = serialize($item->get());
        $client->set($key, $value);
        if($expiresAt !== null) {
            $client->expireat($key, $expiresAt->getTimestamp());
        }

        return true;
    }

    public function getItem($key)
    {
        if(!$this->hasItem($key)) {
            return $this->buildItem($key, false);
        }

        $value = $this->client()->get($key);
        $value = unserialize($value);
        return $this->buildItem($key, true, $value);
    }

    /**
     * @return Client
     */
    public function client()
    {
        if($this->client === null) {
            $this->client = $this->buildClient();
        }

        return $this->client;
    }

    /**
     * @return Client
     */
    protected function buildClient()
    {
        $connection = $this->configData->getRequired('connection');
        $options = $this->configData->get('options', array());
        $client = new Client($connection, $options);
        return $client;
    }
}