<?php

namespace PHPixie\Cache;

use PHPixie\Cache\Drivers\Driver;
use PHPixie\Cache\Pool\Prefixed;
use PHPixie\Cache\Pool\Storage;
use PHPixie\Filesystem\Root;
use PHPixie\Slice\Data;

class Builder
{
    /** @var  Data */
    protected $configData;

    /** @var  Root */
    protected $filesystemRoot;

    /** @var  Storages */
    protected $storages;

    /**
     * @param Data $configData
     * @param Root $filesystemRoot
     */
    public function __construct($configData, $filesystemRoot = null)
    {
        $this->configData = $configData;
        $this->filesystemRoot = $filesystemRoot;
    }

    protected $driverMap = array(
        'memcached' => '\PHPixie\Cache\Drivers\Type\Memcached',
        'memory'    => '\PHPixie\Cache\Drivers\Type\Memory',
        'void'      => '\PHPixie\Cache\Drivers\Type\VoidDriver',
        'phpfile'   => '\PHPixie\Cache\Drivers\Type\File\PHP',
        'redis'     => '\PHPixie\Cache\Drivers\Type\Redis',
    );

    /**
     * @return Storages
     */
    public function storages()
    {
        if($this->storages === null) {
            $this->storages = new Storages($this, $this->configData);
        }

        return $this->storages;
    }

    /**
     * @param string $name
     * @param Driver $driver
     * @param Data $configData
     *
     * @return Storage
     */
    public function storage($name, $driver, $configData)
    {
        return new Storage($this, $name, $driver, $configData);
    }

    /**
     * @param Storage $storage
     * @param string $prefix
     * @return Prefixed
     */
    public function prefixedPool($storage, $prefix)
    {
        return new Prefixed($storage, $prefix);
    }

    /**
     * @param string $type
     * @param Data $configData
     *
     * @return Driver
     */
    public function driver($type, $configData)
    {
        $class = $this->driverMap[$type];
        return new $class($this, $configData);
    }

    /**
     * @return Root
     * @throws Exception If the Filesystem Root was not passed to constructor
     */
    public function filesystemRoot()
    {
        if($this->filesystemRoot === null) {
            throw new Exception("Filesystem root has not been supplied when initializing.");
        }

        return $this->filesystemRoot;
    }
}