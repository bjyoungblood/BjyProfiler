<?php

namespace BjyProfiler;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Db\Adapter\Adapter' => function ($sm) {
                    $config = $sm->get('Configuration');
                    if (!isset($config['db'])) {
                        return false;
                    }
                    if (class_exists('BjyProfiler\Db\Adapter\ProfilingAdapter')) {
                        $adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter($config['db']);
                        $adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler);
                        if (isset($config['db']['options']) && is_array($config['db']['options'])) {
                            $options = $config['db']['options'];
                        } else {
                            $options = array();
                        }
                        $adapter->injectProfilingStatementPrototype($options);
                    } else {
                        $adapter = new \Zend\Db\Adapter\Adapter($config['db']);
                    }
                    return $adapter;
                },
            ),
        );
    }

}
