<?php

namespace PHPixie\Cache\Pool;

use PHPixie\Cache\Exception;
use PHPixie\Cache\Item;
use PHPixie\Cache\Pool;
use Psr\Cache\CacheItemInterface;

class Prefixed implements Pool
{
    /** @var Storage */
    protected $storage;

    /** @var  string */
    protected $prefix;

    /**
     * Prefix constructor.
     * @param Storage $storage
     * @param string $prefix
     */
    public function __construct($storage, $prefix)
    {
        $this->storage = $storage;
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function prefixedPool($prefix)
    {
        return $this->storage->prefixedPool($this->normalizeKey($prefix));
    }

    public function storage()
    {
        return $this->storage;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $this->storage->get($this->normalizeKey($key), $default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->storage->set($this->normalizeKey($key), $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->storage->delete($this->normalizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $data = $this->storage->getMultiple($this->normalizeKeys($keys));
        return $this->normalizeDataKeys($data, true);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $values = $this->normalizeDataKeys($values);
        return $this->storage->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        return $this->storage->deleteMultiple($this->normalizeKeys($keys));
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->storage->has($this->normalizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function getItem($key)
    {
        return $this->storage->convertItem($this->getItem($key), true);
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = array())
    {
        $items = $this->storage->getItems($this->normalizeKeys($keys));

        $result = array();
        foreach ($items as $item) {
            $item = $this->convertItem($item, true);
            $result[$item->getKey()] = $item;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key)
    {
        return $this->storage->hasItem($this->normalizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        throw new Exception("Prefixed pools cannot be cleared");
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key)
    {
        return $this->storage->deleteItem($this->normalizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys)
    {
        return $this->storage->deleteItems($this->normalizeKeys($keys));
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item)
    {
        $item = $this->convertItem($item);
        return $this->storage->save($item);
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $item = $this->convertItem($item);
        return $this->storage->saveDeferred($item);
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        return $this->storage->commit();
    }

    /**
     * @inheritDoc
     */
    public function createItem($key, $value = null)
    {
        return $this->storage->createItem($key, $value);
    }
    
    protected function convertItem(CacheItemInterface $item, $trim = false)
    {
        if(!($item instanceof Item)) {
            throw new Exception('Only instances of \PHPixie\Cache\Item are allowed');
        }

        $new = $this->storage->driver()->buildItem(
            $this->normalizeKey($item->getKey(), $trim),
            $item->isHit(),
            $item->get()
        );
        
        $new->expiresAt($item->getExpiresAt());
        return $new;
    }

    protected function normalizeKeys($keys)
    {
        $fullKeys = array();
        foreach($keys as $key) {
            $fullKeys[]= $this->normalizeKey($key);
        }

        return $fullKeys;
    }

    protected function normalizeDataKeys($data, $trim = false)
    {
        $normalized = array();

        foreach ($data as $key => $value) {
            $normalized[$this->normalizeKey($key, $trim)] = $value;
        }

        return $normalized;
    }

    protected function normalizeKey($key, $trim = false)
    {
        if($trim) {
            return substr($key, strlen($this->prefix)+1);
        }

        return $this->prefix.'.'.$key;
    }
}
