<?php

namespace PHPixie\Tests\Cache\Driver\File;

use PHPixie\Tests\Cache\DriverTest;

class VoidTest extends DriverTest
{
    protected $configData = array(
        'default' => array(
            'driver' => 'void',
        )
    );

    public function testSimple()
    {
        $this->cache->set('pixie', 'trixie');
        $this->assertGet('pixie', null);

        $this->cache->set('stella', 5, 1);
        $this->assertGet('stella', null);

        $this->cache->setMultiple(array(
            'blum'  => array(1),
            'fairy' => 3
        ));

        $this->cache->storage('default')->driver()->cleanup();

        $this->assertGetMultiple(array(
            'blum'  => null,
            'fairy' => null,
            'none'  => null
        ));

        $this->cache->deleteMultiple(array('blum', 'fairy'));

        $this->cache->clear();
    }
}