<?php
/**
 * Created by Inditel Meedia OÜ
 * User: Oliver Leisalu
 */

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProfilingAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Configuration');
        $adapter = new ProfilingAdapter($config['db']);

        if (php_sapi_name() == 'cli') {
            $logger = new Zend\Log\Logger();
            // write queries profiling info to stdout in CLI mode
            $writer = new Zend\Log\Writer\Stream('php://output');
            $logger->addWriter($writer, Zend\Log\Logger::DEBUG);
            $adapter->setProfiler(new Profiler\LoggingProfiler($logger));
        } else {
            $adapter->setProfiler(new Profiler\Profiler());
        }
        if (isset($dbParams['options']) && is_array($dbParams['options'])) {
            $options = $dbParams['options'];
        } else {
            $options = array();
        }
        $adapter->injectProfilingStatementPrototype($options);
        return $adapter;
    }
}