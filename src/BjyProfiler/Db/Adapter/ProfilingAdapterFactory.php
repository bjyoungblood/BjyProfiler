<?php
/**
 * Created by Inditel Meedia OÜ
 * User: Oliver Leisalu
 */

namespace BjyProfiler\Db\Adapter;


use BjyProfiler\Db\Profiler\Profiler;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProfilingAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createService($container);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Configuration');
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
