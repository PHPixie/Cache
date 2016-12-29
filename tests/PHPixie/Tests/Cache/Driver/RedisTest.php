<?php

namespace PHPixie\Tests\Cache\Driver\File;

use PHPixie\Tests\Cache\DriverTest;

class RedisTest extends DriverTest
{
    protected $configData = array(
        'default' => array(
            'driver'     => 'redis',
            'connection' => array(
                'scheme' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => 6379
            )
        )
    );
}