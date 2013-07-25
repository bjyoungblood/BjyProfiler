BjyProfiler
===========
Provides Zend\Db adapters with extensions for database query profiling, as well as a profiler similar to ZF1's Zend\_Db\_Profiler.
I ported much of this code from ZF1's Zend_Db.

Note: this module now works with Zend\Db's built-in profiler.

**Note**: PHP >= 5.3.6 is required for stack traces with query profiles.

Composer/Packagist Users
========================

Please note the name of this project's package has changed to bjyoungblood/bjy-profiler
in order to match composer/packagist's new naming conventions. Please update your composer.json
to use the new package name.

Configuration & Usage
---------------------
Following is a sample database configuration:

```php
<?php

$dbParams = array(
    'database'  => 'changeme',
    'username'  => 'changeme',
    'password'  => 'changeme',
    'hostname'  => 'localhost',
    // buffer_results - only for mysqli buffered queries, skip for others
    'options' => array('buffer_results' => true)
);

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                $adapter = new BjyProfiler\Db\Adapter\ProfilingAdapter(array(
                    'driver'    => 'pdo',
                    'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
                    'database'  => $dbParams['database'],
                    'username'  => $dbParams['username'],
                    'password'  => $dbParams['password'],
                    'hostname'  => $dbParams['hostname'],
                ));

                if (php_sapi_name() == 'cli') {
                    $logger = new Zend\Log\Logger();
                    // write queries profiling info to stdout in CLI mode
                    $writer = new Zend\Log\Writer\Stream('php://output');
                    $logger->addWriter($writer, Zend\Log\Logger::DEBUG);
                    $adapter->setProfiler(new BjyProfiler\Db\Profiler\LoggingProfiler($logger));
                } else {
                    $adapter->setProfiler(new BjyProfiler\Db\Profiler\Profiler());
                }
                if (isset($dbParams['options']) && is_array($dbParams['options'])) {
                    $options = $dbParams['options'];
                } else {
                    $options = array();
                }
                $adapter->injectProfilingStatementPrototype($options);
                return $adapter;
            },
        ),
    ),
);
```

After you've run a couple queries (or before, if you so choose), you can use the service locator to grab the adapter using whatever alias you provide (using Zend\Db\Adapter\Adapter is a good way to simply replace Zend\Db's adapter with my profiling adapter.

```php
$profiler = $sl->get('Zend\Db\Adapter\Adapter')->getProfiler();
$queryProfiles = $profiler->getQueryProfiles();
```
