<?php

namespace BjyProfilerTest\Db\Profiler;

use BjyProfiler\Db\Profiler\LoggingProfiler;
use BjyProfiler\Db\Profiler\Profiler;
use Zend\Log;

class LoggingProfilerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Log\Writer\Mock */
    protected $writer;

    /** @var Log\Logger */
    protected $logger;

    /** @var LoggingProfiler */
    protected $profiler;

    /** @var \ReflectionProperty */
    protected $enabledProperty;

    protected function setUp()
    {
        $this->writer = new Log\Writer\Mock();
        $this->logger = new Log\Logger();
        $this->logger->addWriter($this->writer);
        $this->profiler = new LoggingProfiler($this->logger);
        $tgReflection = new \ReflectionClass(get_class($this->profiler));
        $this->enabledProperty = $tgReflection->getProperty('enabled');
        $this->enabledProperty->setAccessible(true);
    }

    public function testInstanceOfProfiler()
    {
        self::assertInstanceOf(Profiler::class, $this->profiler);
    }

    public function testReturnSelfInSetPriority()
    {
        self::assertEquals($this->profiler, $this->profiler->setPriority(Log\Logger::NOTICE));
    }

    public function testSetGetPriority()
    {
        $level = Log\Logger::NOTICE;
        $this->profiler->setPriority($level);
        self::assertEquals($level, $this->profiler->getPriority());
    }

    public function testReturnSelfInSetLogger()
    {
        self::assertEquals($this->profiler, $this->profiler->setLogger(new Log\Logger()));
    }

    public function testSetGetLogger()
    {
        $logger = new Log\Logger();
        $this->profiler->setLogger($logger);
        self::assertEquals($logger, $this->profiler->getLogger());
    }

    public function testReturnSelfInSetMaxProfiles()
    {
        self::assertEquals($this->profiler, $this->profiler->setMaxProfiles(1));
    }

    public function testSetGetMaxProfiles()
    {
        $maxProfiles = 1;
        $this->profiler->setMaxProfiles($maxProfiles);
        self::assertEquals($maxProfiles, $this->profiler->getMaxProfiles());
    }

    public function testReturnSelfInSetParametersFinish()
    {
        self::assertEquals($this->profiler, $this->profiler->setParametersFinish([]));
    }

    public function testSetGetParametersFinish()
    {
        $parameterFinish = ['foo'];
        $this->profiler->setParametersFinish($parameterFinish);
        self::assertEquals($parameterFinish, $this->profiler->getParametersFinish());
    }

    public function testReturnSelfInSetParametersStart()
    {
        self::assertEquals($this->profiler, $this->profiler->setParametersStart([]));
    }

    public function testSetGetParametersStart()
    {
        $parameterStart = ['bar'];
        $this->profiler->setParametersStart($parameterStart);
        self::assertEquals($parameterStart, $this->profiler->getParametersStart());
    }

    public function testConstructorParameters()
    {
        $logger = new Log\Logger();
        $enables = false;
        $priority = Log\Logger::NOTICE;
        $maxProfiles = 1;
        $parametersStart = ['bar'];
        $parametersFinish = ['foo'];
        $profiler = new LoggingProfiler($logger, $enables, compact('priority', 'maxProfiles', 'parametersStart', 'parametersFinish'));
        self::assertEquals($logger, $profiler->getLogger());
        self::assertFalse($this->enabledProperty->getValue($profiler));
        self::assertEquals($priority, $profiler->getPriority());
        self::assertEquals($maxProfiles, $profiler->getMaxProfiles());
        self::assertEquals($parametersStart, $profiler->getParametersStart());
        self::assertEquals($parametersFinish, $profiler->getParametersFinish());
    }

    public function getQueryProfilesProvider()
    {
        return [
            'empty' => [
                [],
                null,
                [],
            ],
            'only query'  => [
                [
                    'CREATE TABLE foo (id INTEGER, name VARCHAR(50))',
                    'SELECT * FROM foo',
                    "INSERT INTO foo (1, 'baz')",
                    "UPDATE foo SET name = 'bar' WHERE id = 1",
                    'DELETE FROM foo WHERE id = 1',
                ],
                Profiler::QUERY,
                [
                    'CREATE TABLE foo (id INTEGER, name VARCHAR(50))'
                ],
            ],
            'query & select'  => [
                [
                    'CREATE TABLE foo (id INTEGER, name VARCHAR(50))',
                    'SELECT * FROM foo',
                    "INSERT INTO foo (1, 'baz')",
                    "UPDATE foo SET name = 'bar' WHERE id = 1",
                    'DELETE FROM foo WHERE id = 1',
                ],
                Profiler::QUERY | Profiler::SELECT,
                [
                    'CREATE TABLE foo (id INTEGER, name VARCHAR(50))',
                    'SELECT * FROM foo',
                ],
            ],
            'select & update'  => [
                [
                    'CREATE TABLE foo (id INTEGER, name VARCHAR(50))',
                    'SELECT * FROM foo',
                    "INSERT INTO foo (1, 'baz')",
                    "UPDATE foo SET name = 'bar' WHERE id = 1",
                    'DELETE FROM foo WHERE id = 1',
                ],
                Profiler::SELECT | Profiler::UPDATE,
                [
                    1 => 'SELECT * FROM foo',
                    3 => "UPDATE foo SET name = 'bar' WHERE id = 1",
                ],
            ],
        ];
    }

    /**
     * @dataProvider getQueryProfilesProvider
     *
     * @param string[] $queries        incoming queries
     * @param int      $queryTypes     type of query
     * @param array    $actualProfiles actual query
     */
    public function testStartQueryAndEndQuery($queries, $queryTypes, $actualProfiles)
    {
        foreach ($queries as $query) {
            $this->profiler->startQuery($query);
            $this->profiler->endQuery();
        }
        $result = $this->profiler->getQueryProfiles($queryTypes);
        self::assertEquals(count($actualProfiles), count($result));
        self::assertEquals(2 * count($queries), count($this->writer->events));
        if (empty($result)) {
            return;
        }
        foreach ($result as $key => $profile) {
            self::assertEquals($actualProfiles[$key], $profile->getSql());
        }
        foreach ($queries as $key => $query) {
            self::assertEquals('Query started', $this->writer->events[2 * $key]['message']);
            self::assertEquals('Query finished', $this->writer->events[2 * $key + 1]['message']);
            self::assertEquals($query, $this->writer->events[2 * $key]['extra']['sql']);
        }
    }
}
