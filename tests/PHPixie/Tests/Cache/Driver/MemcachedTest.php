<?php

namespace PHPixie\Tests\Cache\Driver\File;

use PHPixie\Tests\Cache\DriverTest;

class MemcachedTest extends DriverTest
{
    protected $configData = array(
        'default' => array(
            'driver' => 'memcached',
            'servers' => array(
                array(
                    '127.0.0.1'
                )
            )
        )
    );
}