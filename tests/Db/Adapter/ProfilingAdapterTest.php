<?php

namespace BjyProfilerTest\Db\Adapter;

use BjyProfiler\Db\Adapter\ProfilingAdapter;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;

class ProfilingAdapterTest extends TestCase
{
    public function testInstanceOfAdapter()
    {
        $instance = new ProfilingAdapter(['driver' => 'Pdo']);
        self::assertInstanceOf(Adapter::class, $instance);
    }
}
