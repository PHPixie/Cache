<?php

namespace PHPixie\Cache;

/**
 * File cache driver.
 *
 * To use this driver you need to specify a cache folder in your cache.php
 * configuration file like this:
 * <code>
 *  return array(
 *		'default' => array(
 *			'driver' => 'file',
 *			'cache_dir' => '/assets/cache/'
 *		)
 *  );
 * </code>
 * By default /assets/cache/ will be used
 *
 * @package Cache
 */
class File extends Cache {

	/**
	 * Path to the cache directory.
	 * @var string
	 * @access protected
	 */
	protected $_cache_dir;
	
	/**
	 * Creates a file cache instance. Will automatically create cache directory if it 
	 * is not present yet.
	 * 
	 * @param  string  $config    Name of the configuration to initialize
	 * @access public 
	 */
	public function __construct($pixie, $config) {
		parent::__construct($pixie, $config);
		$this->_cache_dir = $pixie->root_dir.$this->pixie->config->get("cache.{$config}.cache_dir", '/assets/cache/');
	}

	protected function _set($key, $value, $lifetime) {
		if (!is_dir($key['dir']))
			mkdir($key['dir'], 0777, true);
		$expires=time()+$lifetime;
		file_put_contents($key['dir'].$key['file'],$expires."\n".serialize($value));
	}
	
	protected function _get($key) {
		$file = $key['dir'].$key['file'];
		
		if (file_exists($file) && $this->check_file($file)) {
			$data = file_get_contents($file);
			$data = substr($data, strpos($data, "\n")+1);
			return unserialize($data);
		}
		
		if (is_dir($key['dir']))
			$this->check_dir($key['dir']);
	}
	
	public function clear() {
		$dirs = array_diff(scandir($this->_cache_dir), array('.', '..')); 
		foreach($dirs as $dir) {
			$dir=$this->_cache_dir.'/'.$dir;
			$files=array_diff(scandir($dir), array('.', '..')); 
			foreach($files as $file)
				unlink($dir.'/'.$file);
			rmdir($dir);
		}
	}
	
	protected function _delete($key) {
		if (file_exists($key['dir'].$key['file']))
			unlink($key['dir'].$key['file']);
		if(is_dir($key['dir']))
			$this->check_dir($key['dir']);
	}
	
	public function garbage_collect() {
		$dirs = array_diff(scandir($this->_cache_dir), array('.', '..')); 
		foreach($dirs as $dir) {
			$dir=$this->_cache_dir.'/'.$dir;
			$files=array_diff(scandir($dir), array('.', '..')); 
			foreach($files as $file) 
				$this->check_file($dir.'/'.$file);
			$this->check_dir($dir);
		}
	}
	
	/**
	 * Checks if the cache subfolder is empty.
	 * If so, removes it.
	 * 
	 * @param  string  $dir  Directory to check
	 * @access protected If the file is not expired
	 */
	 
	 protected function check_dir($dir) {
		if (count(scandir($dir)) == 2)
			rmdir($dir);
	}
	
	/**
	 * Checks if the cache file is expired. 
	 * If so, removes it.
	 * 
	 * @param  string  $file  File to check
	 * @return bool    If the file is not expired
	 * @access protected 
	 */
	protected function check_file($file) {
		$fp = fopen($file, 'r');
		$expires = fgets($fp);
		fclose($fp);
		
		if ($expires < time()){
			unlink($file);
			return false;
		}

		return true;
	}
	
	/**
	 * Turns $key into an associatove array containing
	 * file directory and the name of the cache file.
	 * 
	 * @param  string  $key  Name to sanitize
	 * @return array   Associative array with file directory and name
	 * @access public 
	 */
	protected function sanitize($key) {
		$key = md5($key);
		return array(
			'dir' => $this->_cache_dir.'/'.substr($key, 0, 2).'/',
			'file' => $key
		);
	}
}