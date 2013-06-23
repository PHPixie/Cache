<?php

namespace PHPixie\Cache;

/**
 * Memcache cache driver.
 * @package Cache
 */
class Memcache extends Cache {

	/**
	 * Memcache instance
	 * @var Memcache   
	 * @access protected
	 */
	protected $_memcache;
	
	/**
	 * Creates a memcache cache instance.
	 * 
	 * @param  string  $config    Name of the configuration to initialize
	 * @access public 
	 */
	public function __construct($pixie, $config) {
		parent::__construct($pixie, $config);
		$this->_memcache = new \Memcache();
		
		$connected = $this->_memcache->connect(
			$this->pixie->config->get("cache.{$config}.memcached_host"), 
			$this->pixie->config->get("cache.{$config}.memcached_port")
		);
		
		if (!$connected)
			throw new Exception("Could not connect to memcached server");
		
	}
	protected function _set($key, $value, $lifetime) {
		if (!$this->_memcache->replace($key, $value, false, $lifetime))
			$this->_memcache->set($key, $value, false, $lifetime);
	}
	
	protected function _get($key) {
		if ($data = $this->_memcache->get($key))
			return $data;
	}
	
	public function clear() {
		$this->_memcache->flush();
	}
	
	protected function _delete($key) {
		$this->_memcache->delete($key);
	}
}
