<?php
class Xcache_Cache extends Cache {

	protected function _set($key, $value, $lifetime) {
		xcache_set($key, $value, $lifetime);
	}
	
	protected function _get($key, $default) {
		return xcache_get($key);
	}
	
	protected function _delete_all() {
		xcache_clear_cache(XC_TYPE_VAR, -1);
	}
	
	protected function _delete($key) {
		xcache_unset($key);
	}
}