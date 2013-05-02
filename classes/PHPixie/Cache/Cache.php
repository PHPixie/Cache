<?php

namespace PHPixie\Cache;
/**
 * Abstract driver class that actual drivers extend.
 * Defines the basic functionality that each driver must provide and
 * provides methods that proxy driver calls.
 * @package Cache
 */
abstract class Cache {

	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
	/**
	 * Default lifetime for current configuration. Defaults to 3600.
	 * @var int
	 */
	protected $_default_lifetime;
	
	/**
	 * Creates the cache instance.
	 * 
	 * @param  string  $config    Name of the configuration to initialize
	 */
	public function __construct($pixie, $config) {
		$this->pixie = $pixie;
		$this->_default_lifetime=$pixie->config->get("cache.{$config}.default_lifetime",3600);
	}
	
	/**
	 * Caches a value for the duration of the specified lifetime.
	 * 
	 * @param  string  $key       Name to store the object under
	 * @param  mixed   $value     Object to store
	 * @param  int     $lifetime  Validity time for this object in seconds. 
	 *                            Default's to the value specified in config, or to 3600
	 *                            if it was not specified.
	 */
	public function set($key, $value, $lifetime = null){
		if ($lifetime === null)
			$lifetime = $this->_default_lifetime;
		$this->_set($this->sanitize($key), $value, $lifetime);
	}
	
	/**
	 * Gets a stored cache value.
	 * 
	 * @param  string  $key       Name of the object to retrieve
	 * @param  mixed   $default   Default value to return if the object is not found
	 * @return mixed   The requested object, or, if it was not found, the default value.
	 * @access public 
	 */
	public function get($key, $default = null) {
		$data = $this->_get($this->sanitize($key));
		if ($data !== null)
			return $data;
		return $default;
	}
	
	/**
	 * Deletes an object from cache
	 * 
	 * @param  string  $key       Name of the object to remove
	 */
	public function delete($key) {
		$this->_delete($this->sanitize($key));
	}
	
	/**
	 * Sanitizes the name of the cached object, 
	 * preparing it to be passed to the driver.
	 * 
	 * @param  string  $key  Name to sanitize
	 * @return string  Sanitized name
	 */
	protected function sanitize($key) {
		return str_replace(array('/', '\\', ' '), '_', $key);
	}
	
	/**
	 * Driver implementation of the set() method
	 * 
	 * @param  string  $key       Sanitized name to store the object under
	 * @param  mixed   $value     Object to store
	 * @param  int     $lifetime  Validity time for this object in seconds. 
	 * 							  Default's to the value specified in config, or to 3600
	 *                            if it was not specified.
	 */
	protected abstract function _set($key, $value, $lifetime);
	
	/**
	 * Driver implementation of the get() method. 
	 * If it returns NULL a default value will be applied by get().
	 * 
	 * @param  string  $key       Sanitized name of the object to retrieve
	 * @return mixed   The requested object or NULL if it is not found.
	 */
	protected abstract function _get($key);
	
	/**
	 * Driver implementation of the delete() method
	 * 
	 * @param  string  $key       Sanitized name of the object to remove
	 */
	protected abstract function _delete($key);
	
	/**
	 * Clears cache
	 * 
	 * @return void
	 */
	public abstract function clear();
	
	/**
	 * Checks and removes expired objects.
	 * Not required for the driver to implement.
	 * 
	 * @return void
	 */
	public function garbage_collect(){}


}