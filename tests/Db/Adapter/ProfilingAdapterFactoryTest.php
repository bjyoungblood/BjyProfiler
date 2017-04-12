<?php

namespace BjyProfilerTest\Db\Adapter;

use BjyProfiler\Db\Adapter\ProfilingAdapter;
use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;
use BjyProfiler\Db\Profiler\LoggingProfiler;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class ProfilingAdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $config = [
        'factories' => [
            AdapterInterface::class => ProfilingAdapterFactory::class,
        ]
    ];

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ProfilingAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->serviceManager = new ServiceManager();
        (new ServiceManagerConfig($this->config))->configureServiceManager($this->serviceManager);
        $this->serviceManager->setService('Configuration', ['db' => ['driver' => 'Pdo']]);
        $this->adapter = $this->serviceManager->get(AdapterInterface::class);
    }

    public function testInstanceOfProfilingAdapter()
    {
        self::assertInstanceOf(ProfilingAdapter::class, $this->adapter);
    }

    public function testProfilerInstanceOfLoggingProfiler()
    {
        self::assertInstanceOf(LoggingProfiler::class, $this->adapter->getProfiler());
    }
}
