<?php

namespace PHPixie\Tests\Cache\Driver\File;

use PHPixie\Tests\Cache\DriverTest;

class PHPTest extends DriverTest
{
    protected $configData = array(
        'default' => array(
            'driver' => 'phpfile',
            'path'   => 'pixie'
        )
    );
}