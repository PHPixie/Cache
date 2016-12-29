<?php

namespace PHPixie\Cache;

use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{
    protected $key;
    protected $isHit;
    protected $value;
    protected $expiresAt;

    public function __construct($key, $isHit, $value = null, $expiresAt = null)
    {
        $this->key = $key;
        $this->isHit = $isHit;
        $this->value = $value;
        $this->expiresAt = $expiresAt;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->isHit;
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function expiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    public function expiresAfter($interval)
    {
        if($interval === null) {
            $this->expiresAt = null;
            return;
        }

        $expires = new \DateTime();
        if(is_int($interval)) {
            $interval = new \DateInterval('PT'.$interval.'S');
        }

        $this->expiresAt = $expires->add($interval);
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}