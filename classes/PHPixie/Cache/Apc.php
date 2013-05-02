<?php

namespace PHPixie\Cache;

/**
 * APC cache driver.
 * @package Cache
 */
class Apc extends Cache {

	protected function _set($key, $value, $lifetime) {
		apc_store($key, $value, $lifetime);
	}
	
	protected function _get($key) {
		$data = apc_fetch($key, $success);
		if ($success)
			return $data;
	}
	
	public function clear() {
		apc_clear_cache('user');
	}
	
	protected function _delete($key) {
		apc_delete($key);
	}
}