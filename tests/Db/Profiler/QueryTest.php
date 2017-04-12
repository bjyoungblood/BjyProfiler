<?php

namespace BjyProfilerTest\Db\Profiler;

use BjyProfiler\Db\Profiler\Profiler;
use BjyProfiler\Db\Profiler\Query;
use BjyProfiler\Exception\RuntimeException;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /** @var Query */
    protected $query;

    protected function getQuery($sql, $queryType, $parameters = null, $stack = [])
    {
        return new Query($sql, $queryType, $parameters, $stack);
    }

    protected function setUp()
    {
        $this->query = $this->getQuery('SELECT * FROM foo', Profiler::SELECT);
    }

    public function testGetStartTimeReturnNullWhenQueryNoStarted()
    {
        self::assertNull($this->query->getStartTime());
    }

    public function testGetEndTimeReturnNullWhenQueryNoFinished()
    {
        self::assertNull($this->query->getEndTime());
    }

    public function testStart()
    {
        $this->query->start();
        self::assertInternalType('float', $this->query->getStartTime());
    }

    public function testEndRaiseExceptionWhenQueryTryFinishBeforeStart()
    {
        $this->setExpectedException(RuntimeException::class, 'Query was not started.');
        $this->query->end();
    }

    public function testEnd()
    {
        $this->query->start();
        $this->query->end();
        self::assertInternalType('float', $this->query->getEndTime());
    }

    public function testHasEnded()
    {
        self::assertFalse($this->query->hasEnded());
        $this->query->start();
        $this->query->end();
        self::assertTrue($this->query->hasEnded());
    }

    public function testGetElapsedTime()
    {
        self::assertFalse($this->query->getElapsedTime());
        $this->query->start();
        $this->query->end();
        self::assertInternalType('float', $this->query->getElapsedTime());
    }

    public function testGetSql()
    {
        $query = 'SELECT * FROM bar';
        $instance = $this->getQuery($query, Profiler::SELECT);
        self::assertEquals($query, $instance->getSql());
    }

    public function testToArray()
    {
        $actual = [
            'sql'        => 'SELECT * FROM baz WHERE id = :where1',
            'queryType'  => Profiler::SELECT,
            'parameters' => ['where1' => 1],
            'stack'      => ['stack'],
        ];
        $instance = $this->getQuery($actual['sql'], $actual['queryType'], $actual['parameters'], $actual['stack']);
        $instance->start();
        $instance->end();
        $result = $instance->toArray();
        self::assertEquals('SELECT', $result['type']);
        self::assertEquals($actual['sql'], $result['sql']);
        self::assertInternalType('float', $result['start']);
        self::assertInternalType('float', $result['end']);
        self::assertInternalType('float', $result['elapsed']);
        self::assertEquals($actual['parameters'], $result['parameters']);
        self::assertEquals($actual['stack'], $result['stack']);
    }
}
