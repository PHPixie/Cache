<?php
/**
 * Memcache cache driver.
 * @package Cache
 */
class Memcache_Cache extends Abstract_Cache {

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
	public function __construct($config) {
		parent::__construct($config);
		$this->_memcache = new Memcache();
		
		$connected = $this->_memcache->connect(
			Config::get("cache.{$config}.memcached_host"), 
			Config::get("cache.{$config}.memcached_port")
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