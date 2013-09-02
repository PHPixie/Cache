<?php
return array(
	'default' => array(
		
		//Supported drivers are: apc, database, file, xcache and memcache
		'driver' => 'file',
		
		//Default liefetime for cached objects in seconds
		'default_lifetime' => 3600,
		//Cache directory for 'file' driver
		'cache_dir' => ROOTDIR.'/modules/cache/cache/',
		
		//Database connection name for 'database' driver
		'connection' => 'default',
		
		//Memcached host for 'memcache' driver
		'memcached_host' => '127.0.0.1',
		
		//Memcached port for 'memcache' driver
		'memcached_port' => '11211',
	)
);
