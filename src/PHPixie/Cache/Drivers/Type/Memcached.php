<?php

namespace PHPixie\Cache\Drivers\Type;

use PHPixie\Cache\Drivers\Driver;
use InvalidArgumentException;

class Memcached extends Driver
{
    /** @var \Memcached */
    protected $client;

    /**
     * @inheritdoc
     */
    public function hasItem($key)
    {
        $client = $this->client();
        $client->get($key);
        return $client->getResultCode() == \Memcached::RES_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    public function getItems(array $keys = array())
    {
        if(empty($keys)) {
            return array();
        }

        $client = $this->client();
        $data = $client->getMulti($keys);
        if($data === false) {
            $data = array();
        }

        return $this->buildItems($keys, $data);
    }

    /**
     * @inheritdoc
     */
    public function deleteItems(array $keys = array())
    {
        $this->client()->deleteMulti($keys);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->client()->flush();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function saveItem($item, $expiresAt)
    {
       if($expiresAt !== null) {
            $expiresAt = $expiresAt->getTimestamp();
        } else {
            $expiresAt = 0;
        }

        $this->client()->set(
            $item->getKey(),
            $item->get(),
            $expiresAt
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getItem($key)
    {
        $client = $this->client();
        $value = $client->get($key);
        if($client->getResultCode() !== \Memcached::RES_SUCCESS) {
            return $this->buildItem($key, false);
        }

        return $this->buildItem($key, true, $value);
    }

    /**
     * @return \Memcached
     */
    public function client()
    {
        if($this->client === null) {
            $this->client = $this->buildClient();
        }

        return $this->client;
    }

    /**
     * Prepare server config, because in app config it could be defined w/o port or weight
     *
     * @param array $value
     * @return array
     */
    protected function prepareServerConfig(array $value)
    {
        return $value + array(null, 11211, 1);
    }

    /**
     * Build client
     *
     * @return \Memcached
     */
    protected function buildClient()
    {
        $client = new \Memcached();
        $servers = $this->configData->getRequired('servers');
        foreach($servers as $key => $value) {
            $servers[$key] = $this->prepareServerConfig($value);
        }
        $client->addServers($servers);

        return $client;
    }
}