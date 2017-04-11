<?php

namespace BjyProfilerTest;

use BjyProfiler\ConfigProvider;
use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\AdapterInterface;

class ConfigProviderTest extends TestCase
{
    public function testInvoke()
    {
        $instance = new ConfigProvider();
        $config = $instance();
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('factories', $config['dependencies']);
        self::assertArrayHasKey(AdapterInterface::class, $config['dependencies']['factories']);
    }

    public function testGetDependencyConfig()
    {
        $instance = new ConfigProvider();
        $config = $instance->getDependencyConfig();
        self::assertArrayHasKey('factories', $config);
        self::assertArrayHasKey(AdapterInterface::class, $config['factories']);
        self::assertEquals(ProfilingAdapterFactory::class, $config['factories'][AdapterInterface::class]);
    }
}
