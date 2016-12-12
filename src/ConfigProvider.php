<?php
/**
 * @link      http://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BjyProfiler;

use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;
use Zend\Db\Adapter\AdapterInterface;

class ConfigProvider
{
    /**
     * Retrieve zend-db default configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Retrieve zend-db default dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                AdapterInterface::class => ProfilingAdapterFactory::class,
            ],
        ];
    }
}
