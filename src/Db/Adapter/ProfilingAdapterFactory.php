<?php

declare(strict_types=1);

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler\Profiler;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ProfilingAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Configuration');
        $dbParams = $config['db'];
        $adapter = new ProfilingAdapter($dbParams);
        $adapter->setProfiler(new Profiler);
        if (isset($dbParams['options']) && is_array($dbParams['options'])) {
            $options = $dbParams['options'];
        } else {
            $options = array();
        }
        $adapter->injectProfilingStatementPrototype($options);
        return $adapter;
    }
}
