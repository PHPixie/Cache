<?php

namespace PHPixie\Cache;

use PHPixie\Cache\Pool\Storage;
use PHPixie\Slice\Data;

class Storages
{
    /** @var  Builder */
    protected $builder;

    /** @var  Data */
    protected $configData;

    protected $storages = array();

    /**
     * @param Builder $builder
     * @param Data $configData
     */
    public function __construct($builder, $configData)
    {
        $this->builder = $builder;
        $this->configData = $configData;
    }

    /**
     * @param string $name
     * @return Storage
     */
    public function get($name)
    {
        if(!isset($this->storages[$name])) {
            $storageConfig = $this->configData->slice($name);
            $driverName = $storageConfig->getRequired('driver');
            $driver = $this->builder->driver($driverName, $storageConfig);
            $this->storages[$name] = $this->builder->storage($name, $driver, $storageConfig);
        }

        return $this->storages[$name];
    }
}