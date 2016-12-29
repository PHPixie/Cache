<?php

namespace PHPixie\Tests\Cache\Driver\File;

use PHPixie\Tests\Cache\DriverTest;

class MemoryTest extends DriverTest
{
    protected $configData = array(
        'default' => array(
            'driver' => 'memory',
        )
    );
}