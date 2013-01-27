<?php

abstract class Cache {

	private static $_instances=array();
	
	protected $_default_lifetime;
	
	public function __construct($config) {
		$this->_default_lifetime=Config::get("cache.{$config}.default_lifetime",3600);
	}

	public function set($key, $value, $lifetime = null){
		if ($lifetime === null)
			$lifetime = $this->_default_lifetime;
		$this->_set($this->sanitize($key), $value, $lifetime);
	}
	
	public function get($key, $default = null) {
		$data = $this->_get($this->sanitize($key));
		if ($data !== null)
			return $data;
		return $default;
	}
	
	public function delete($key) {
		$this->_delete($this->sanitize($key));
	}
	
	public function delete_all() {
		$this->_delete_all();
	}
	
	
	protected abstract function _set($key, $value, $lifetime);
	protected abstract function _get($key, $default);
	protected abstract function _delete($key);
	protected abstract function _delete_all();
	
	public static function instance($config='default'){
		if (!isset(Cache::$_instances[$config])) {
			$driver = Config::get("cache.{$config}.driver");
			$driver="{$driver}_Cache";
			Cache::$_instances[$config] = new $driver($config);
		}
		return Cache::$_instances[$config];
	}

}