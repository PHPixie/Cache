<?php
/**
 * Cache Module for PHPixie
 *
 * This module is not included by default, download it here:
 *
 * https://github.com/dracony/PHPixie-Cache
 * 
 * To enable it add 'cache' to modules array in /application/config/core.php.
 * Currently 4 drivers are provided: APC, Database, File and XCache. For information
 * on configuring your cache check out the /config/cache.php config file inside this
 * module.
 * 
 * @link https://github.com/dracony/PHPixie-Cache Download this module from Github
 * @package    Cache
 */
class Cache {

	/**
     * An associative array of cache instances
     * @var array   
     * @access private 
     * @static 
     */
	private static $_instances=array();
	
	/**
     * Caches a value for the duration of the specified lifetime.
     * 
	 * @param  string  $key       Name to store the object under
	 * @param  mixed   $value     Object to store
	 * @param  int     $lifetime  Validity time for this object in seconds. 
	 * 							  Default's to the value specified in config, or to 3600
	 *                            if it was not specified.
	 * @param  string  $config    Cache configuration to use
	 * @see Abstract_Cache::set()
     * @access public 
	 * @static
     */
	public function set($key, $value, $lifetime = null, $config = 'default') {
		Cache::instance($config)->set($key,$value,$lifetime);
	}
	
	/**
     * Gets a stored cache value of the cache specified by $config.
     * 
	 * @param  string  $key       Name of the object to retrieve
	 * @param  mixed   $default   Default value to return if the object is not found
	 * @param  string  $config    Cache configuration to use
	 * @return mixed   The requested object, or, if it was not found, the default value.
	 * @see Abstract_Cache::get()
     * @access public 
	 * @static
     */
	public static function get($key, $default = null, $config = 'default') {
		return Cache::instance($config)->get($key,$default);
	}
	
	/**
     * Deletes an object from the cache specified by $config.
     * 
	 * @param  string  $key       Name of the object to remove
	 * @param  string  $config    Cache configuration to use
	 * @see Abstract_Cache::delete()
	 * @access public 
	 * @static
     */
	public static function delete($key, $config = 'default') {
		Cache::instance($config)->delete($key);
	}
	
	/**
     * Clears cache specified by $condfig
     * 
	 * @param  string  $config    Cache configuration to use
	 * @see Abstract_Cache::clear()
	 * @access public 
	 * @static
     */
	public static function clear($config = 'default') {
		Cache::instance($config)->clear();
	}
	
	/**
     * Checks and removes expired objects from cache specified by $config
     * 
	 * @param  string  $config    Cache configuration to use
	 * @see Abstract_Cache::garbage_collect()
	 * @access public 
	 * @static
     */
	public static function garbage_collect($config = 'default') { 
		Cache::instance($config)->garbage_collect();
	}
	
	
	/**
     * Gets an instance of a cache configuration
     * 
     * @param string  $config Configuration name.
	 *                        Defaults to  'default'.
     * @return Abstract_Cache Driver implementation of Abstact_Cache
     * @access public 
     * @static 
     */
	public static function instance($config='default'){
		if (!isset(Cache::$_instances[$config])) {
			$driver = Config::get("cache.{$config}.driver");
			$driver="{$driver}_Cache";
			Cache::$_instances[$config] = new $driver($config);
		}
		return Cache::$_instances[$config];
	}

}