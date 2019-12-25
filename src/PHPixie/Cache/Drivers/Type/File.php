<?php

namespace PHPixie\Cache\Drivers\Type;

use PHPixie\Cache\Builder;
use PHPixie\Cache\Drivers\Driver;
use PHPixie\Cache\Item;
use PHPixie\Slice\Data;

abstract class File extends Driver
{
    protected $extension;

    /**
     * @var string
     */
    protected $directory;

    public function __construct(Builder $builder, Data $configData)
    {
        parent::__construct($builder, $configData);

        $dir = $configData->getRequired('path');
        $this->directory = $builder->filesystemRoot()->path($dir);

        if(!file_exists($this->directory)) {
            mkdir($this->directory);
        }
    }

    public function hasItem($key)
    {
        $this->cleanupCheck();
        $file = $this->filePath($key);
        if(!file_exists($file)) {
            return false;
        }

        return $this->checkFileExpiry($file, time());
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
        foreach($keys as $key) {
            $path = $this->filePath($key);
            if(file_exists($path)) {
                unlink($path);
            }
        }

        $this->cleanupCheck();

        return true;
    }

    public function getItem($key)
    {
        $this->cleanupCheck();
        return $this->getItemWithoutCleanup($key);
    }

    protected function getItemWithoutCleanup($key)
    {
        if(!$this->hasItem($key)) {
            return $this->buildItem($key, false);
        }

        $value = $this->getFileData($this->filePath($key));
        return $this->buildItem($key, true, $value);
    }

    public function clear()
    {
        foreach (scandir($this->directory) as $file) {
            if($file[0] == '.') {
                continue;
            }

            unlink($this->directory.'/'.$file);
        }
    }

    public function saveItem($item, $expiresAt)
    {
        if($expiresAt !== null) {
            $expiresAt = $expiresAt->getTimestamp();
        }
        $contents = $this->buildFileContents($item->get(), $expiresAt);
        file_put_contents($this->filePath($item->getKey()), $contents);
        return true;
    }

    protected function filePath($key)
    {
        return $this->directory.'/'.$key.'.'.$this->extension;
    }

    public function cleanup()
    {
        $now = time();

        foreach (scandir($this->directory) as $file) {
            if($file[0] == '.') {
                continue;
            }

            $file = $this->directory.'/'.$file;
            $this->checkFileExpiry($file, $now);
        }

        return true;
    }

    protected function checkFileExpiry($file, $time)
    {
        $timestamp = $this->getExpiryTimestamp($file);
        if($timestamp === null) {
            return true;
        }

        $expired = $timestamp < $time;

        if($expired) {
            unlink($file);
        }

        return !$expired;
    }

    abstract protected function getExpiryTimestamp($file);
    abstract protected function getFileData($file);
    abstract protected function buildFileContents($value, $timestamp);
}
