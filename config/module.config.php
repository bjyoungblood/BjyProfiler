<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'BjyProfiler\Db\Adapter\ProfilingAdapterFactory'
        ),
    ),
);
