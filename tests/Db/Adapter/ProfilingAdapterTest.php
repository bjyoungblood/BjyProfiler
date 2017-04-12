<?php

namespace BjyProfilerTest\Db\Adapter;

use BjyProfiler\Db\Adapter\ProfilingAdapter;
use Zend\Db\Adapter\Adapter;

class ProfilingAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfAdapter()
    {
        $instance = new ProfilingAdapter(['driver' => 'Pdo']);
        self::assertInstanceOf(Adapter::class, $instance);
    }
}
