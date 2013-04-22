<?php

namespace PHPixie\Cache;

/**
 * Database cache driver.
 *
 * To use this driver you need to specify a database connection in your cache.php
 * configuration file like this:
 * <code>
 *  return array(
 *	    'default' => array(
 *	        'driver' => 'database',
 *	        'connection' => 'default'
 *	    )
 *  );
 * </code>
 * By default the 'default' connection will be used
 *
 * @package Cache
 */
class Database extends Cache {
	
	/**
	 * Database connection
	 * @var DB   
	 * @access protected
	 */
	protected $_db;
	
	/**
	 * Creates a database cache instance. Will automatically create cache table if it 
	 * is not present yet.
	 * 
	 * @param  string  $config    Name of the configuration to initialize
	 * @access public 
	 */
	public function __construct($pixie, $config) {
		parent::__construct($pixie, $config);
		$config = $this->pixie->config->get("cache.{$config}.connection", 'default');
		$this->_db = $this->pixie->db->get($config);
		$this->_db->execute("CREATE TABLE IF NOT EXISTS cache (
			name VARCHAR(255) NOT NULL PRIMARY KEY, 
			value TEXT, 
			expires INT
		)");
		
	}
	protected function _set($key, $value, $lifetime) {
		$this->_db->execute("REPLACE INTO cache(name,value,expires) values (?, ?, ?)", array(
			$key,serialize($value),time()+$lifetime
		));
	}
	
	protected function _get($key) {
		$this->garbage_collect();
		$data = $this->_db->execute("SELECT value FROM cache where name = ?", array($key))->get('value');
		if ($data !== null)
			return unserialize($data);
	}
	
	public function clear() {
		$this->_db->execute("DELETE FROM cache");
	}
	
	protected function _delete($key) {
		$this->_db->execute("DELETE FROM cache WHERE name = ?",array($key));
	}
	
	public function garbage_collect() {
		$this->_db->execute("DELETE FROM cache WHERE expires < ?",array(time()));
	}
}