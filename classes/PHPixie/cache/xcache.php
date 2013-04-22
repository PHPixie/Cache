<?php

namespace PHPixie\Cache;

/**
 * XCache cache driver.
 * @package Cache
 */
class Xcache extends Cache {

	protected function _set($key, $value, $lifetime) {
		xcache_set($key, $value, $lifetime);
	}
	
	protected function _get($key) {
		return xcache_get($key);
	}
	
	public function clear() {
		xcache_clear_cache(XC_TYPE_VAR, -1);
	}
	
	protected function _delete($key) {
		xcache_unset($key);
	}
}