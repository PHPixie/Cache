<?php

namespace PHPixie;

use PHPixie\Cache\Builder;
use PHPixie\Cache\Pool\Storage;
use PHPixie\Cache\Storages;
use PHPixie\Filesystem\Root;
use PHPixie\Slice\Data;
use PHPixie\Cache\Pool\Proxy;

class Cache extends Proxy
{
    protected $builder;

    /**
     * @param Data $configData
     * @param Root$filesystemRoot
     */
    public function __construct($configData, $filesystemRoot = null)
    {
        $this->builder = $this->buildBuilder($configData, $filesystemRoot);
    }

    /**
     * @return Storages
     */
    public function storages()
    {
        return $this->builder->storages();
    }

    /**
     * @param $name
     * @return Storage
     */
    public function storage($name)
    {
        return $this->builder->storages()->get($name);
    }

    /**
     * @return Builder
     */
    public function builder()
    {
        return $this->builder;
    }


    /**
     * @return Storage
     */
    protected function pool()
    {
        return $this->storage('default');
    }

    /**
     * @param Data $configData
     * @param Root $filesystemRoot
     * @return Builder
     */
    protected function buildBuilder($configData, $filesystemRoot = null)
    {
        return new Builder($configData, $filesystemRoot);
    }
}