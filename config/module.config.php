<?php

namespace BjyProfiler;

use Laminas\Db\Adapter\Adapter;
use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;

return [
    'service_manager' => [
        'aliases' =>[
            Adapter::class => null
        ],
        'factories' => [
            Adapter::class => ProfilingAdapterFactory::class
        ],
    ],
];
