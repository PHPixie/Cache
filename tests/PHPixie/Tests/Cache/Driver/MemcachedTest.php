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

    public function testServerConfig()
    {
        $driver = $this->cache->storage('default')->driver();

        $params = array(
            array('127.0.0.1'),
            array('192.168.1.100'),
            array('192.168.1.100', 11212),
            array('192.168.1.100', 11212, 3),
        );
        $expected = array(
            array('127.0.0.1', 11211, 1),
            array('192.168.1.100', 11211, 1),
            array('192.168.1.100', 11212, 1),
            array('192.168.1.100', 11212, 3),
        );

        foreach (array_keys($params) as $key) {
            $this->assertEquals(
                $expected[$key],
                $this->invokeMethod($driver, 'prepareServerConfig', array($params[$key]))
            );
        }
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}