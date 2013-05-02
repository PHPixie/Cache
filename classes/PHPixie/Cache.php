<?php
namespace PHPixie;

/**
 * Cache Module for PHPixie
 *
 * This module is not included by default, install it using Composer
 * by adding
 * <code>
 * 		"phpixie/cache": "2.*@dev"
 * </code>
 * to your requirement definition. Or download it from
 * https://github.com/dracony/PHPixie-cache
 * 
 * To enable it add it to your Pixie class' modules array:
 * <code>
 * 		protected $modules = array(
 * 			//Other modules ...
 * 			'cache' => '\PHPixie\Cache',
 * 		);
 * </code>
 *
 * Currently 4 drivers are provided: APC, Database, File, Memcache and XCache. For information
 * on configuring your cache check out the /assets/config/cache.php config file inside this
 * module.
 *
 * @link https://github.com/dracony/PHPixie-Cache Download this module from Github
 * @package    Cache
 */
class Cache {

	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
	/**
	 * An associative array of cache instances
	 * @var array   
	 */
	private $_instances=array();

	/**
	 * Initializes the cache module
	 * 
	 * @param \PHPixie\Pixie $pixie Pixie dependency container
	 */
	public function __construct($pixie) {
		$this->pixie = $pixie;
	}
	
	/**
	 * Gets an instance of a cache configuration
	 * 
	 * @param string  $config Configuration name.
	 *                        Defaults to  'default'.
	 * @return \PHPixie\Cache\Abstract Driver implementation of Abstact_Cache
	 */
	public function instance($config='default'){
		if (!isset($this->_isntances[$config])) {
			$driver = $this->pixie->config->get("cache.{$config}.driver");
			$driver="\\PHPixie\\Cache\\{$driver}";
			$this->_isntances[$config] = new $driver($this->pixie, $config);
		}
		return $this->_isntances[$config];
	}
	
	/**
	 * Caches a value for the duration of the specified lifetime.
	 * 
	 * @param  string  $key       Name to store the object under
	 * @param  mixed   $value     Object to store
	 * @param  int     $lifetime  Validity time for this object in seconds. 
	 * 							  Default's to the value specified in config, or to 3600
	 *                            if it was not specified.
	 * @param  string  $config    Cache configuration to use
	 * @see \PHPixie\Cache\Abstract::set()
	 */
	public function set($key, $value, $lifetime = null, $config = 'default') {
		$this->instance($config)->set($key,$value,$lifetime);
	}
	
	/**
	 * Gets a stored cache value of the cache specified by $config.
	 * 
	 * @param  string  $key       Name of the object to retrieve
	 * @param  mixed   $default   Default value to return if the object is not found
	 * @param  string  $config    Cache configuration to use
	 * @return mixed   The requested object, or, if it was not found, the default value.
	 * @see \PHPixie\Cache\Abstract::get()
	 */
	public function get($key, $default = null, $config = 'default') {
		return $this->instance($config)->get($key,$default);
	}
	
	/**
	 * Deletes an object from the cache specified by $config.
	 * 
	 * @param  string  $key       Name of the object to remove
	 * @param  string  $config    Cache configuration to use
	 * @see \PHPixie\Cache\Abstract::delete()
	 */
	public function delete($key, $config = 'default') {
		$this->instance($config)->delete($key);
	}
	
	/**
	 * Clears cache specified by $condfig
	 * 
	 * @param  string  $config    Cache configuration to use
	 * @see \PHPixie\Cache\Abstract::clear()
	 */
	public function clear($config = 'default') {
		$this->instance($config)->clear();
	}
	
	/**
	 * Checks and removes expired objects from cache specified by $config
	 * 
	 * @param  string  $config    Cache configuration to use
	 * @see \PHPixie\Cache\Abstract::garbage_collect()
	 */
	public function garbage_collect($config = 'default') { 
		$this->instance($config)->garbage_collect();
	}
	
}