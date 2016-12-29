<?php

namespace PHPixie\Tests\Cache;

use PHPixie\Cache;
use PHPixie\Filesystem;
use PHPixie\Slice;
use PHPixie\Test\Testcase;

abstract class DriverTest extends Testcase
{
    protected $configData;

    protected $slice;

    /** @var  Filesystem */
    protected $filesystem;

    protected $config;

    /** @var  Filesystem\Root */
    protected $filesystemRoot;

    /** @var  Cache */
    protected $cache;

    public function setUp()
    {
        $this->slice = new Slice();
        $this->filesystem = new Filesystem();

        $path = sys_get_temp_dir().'/phpixie_cache/';
        $this->filesystemRoot = $this->filesystem->root($path);

        $this->filesystem->actions()->remove($path);
        mkdir($path);

        $this->cache = new Cache(
            $this->slice->arraySlice($this->configData),
            $this->filesystemRoot
        );
    }

    public function testSimple()
    {
        $this->cache->set('pixie', 'trixie');
        $this->assertGet('pixie', 'trixie');

        $this->cache->set('stella', 5, 1);
        $this->assertGet('stella', 5);

        sleep(2);

        $this->assertGet('stella', 4, 4);
        $this->assertGet('pixie', 'trixie');

        $this->cache->setMultiple(array(
            'blum'  => array(1, 2),
            'fairy' => 3
        ));

        $this->cache->storage('default')->driver()->cleanup();

        $this->assertGetMultiple(array(
            'blum'  => array(1, 2),
            'fairy' => 3,
            'none'  => null
        ));

        $this->cache->deleteMultiple(array('blum', 'fairy'));

        $this->assertGetMultiple(array(
            'blum'  => null,
            'fairy' => null,
            'pixie' => 'trixie'
        ));

        $this->cache->clear();
        $this->assertGet('pixie', null);
    }

    protected function assertGet($key, $value, $default = null)
    {
        $this->assertSame($value, $this->cache->get($key, $default));
    }

    protected function assertGetMultiple($data)
    {
        $values = $this->cache->getMultiple(array_keys($data));
        $this->assertEquals($data, $values);
    }

    public function tearDown()
    {
        //$this->filesystem->actions()->remove($this->filesystemRoot->path());
    }
}
