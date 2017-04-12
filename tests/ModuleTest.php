<?php

namespace BjyProfilerTest;

use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;
use BjyProfiler\Module;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfConfigProviderInterface()
    {
        $instance = new Module();
        self::assertInstanceOf(ConfigProviderInterface::class, $instance);
    }

    public function testGetConfig()
    {
        $instance = new Module();
        $config = $instance->getConfig();
        self::assertArrayHasKey('service_manager', $config);
        self::assertArrayHasKey('factories', $config['service_manager']);
        self::assertArrayHasKey(AdapterInterface::class, $config['service_manager']['factories']);
        self::assertEquals(ProfilingAdapterFactory::class, $config['service_manager']['factories'][AdapterInterface::class]);
    }
}
