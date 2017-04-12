<?php

namespace BjyProfilerTest\Db\Profiler;

use BjyProfiler\Db\Profiler\Profiler;
use BjyProfiler\Db\Profiler\Query;
use BjyProfiler\Exception\RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Profiler */
    protected $profiler;

    /** @var \ReflectionProperty */
    protected $enabledProperty;

    /** @var \ReflectionProperty */
    protected $filterTypesProperty;

    /** @var \ReflectionProperty */
    protected $profilesProperty;

    protected function setUp()
    {
        $this->profiler = new Profiler();
        $tgReflection = new \ReflectionClass(get_class($this->profiler));
        $this->enabledProperty = $tgReflection->getProperty('enabled');
        $this->enabledProperty->setAccessible(true);
        $this->filterTypesProperty = $tgReflection->getProperty('filterTypes');
        $this->filterTypesProperty->setAccessible(true);
        $this->profilesProperty = $tgReflection->getProperty('profiles');
        $this->profilesProperty->setAccessible(true);
    }

    public function testDefaultConstructor()
    {
        $profiler = new Profiler();
        self::assertTrue($this->enabledProperty->getValue($profiler));
    }

    public function testConstructorWithParameter()
    {
        $profiler = new Profiler(false);
        self::assertFalse($this->enabledProperty->getValue($profiler));
    }

    public function testEnable()
    {
        $profiler = new Profiler(false);
        $profiler->enable();
        self::assertTrue($this->enabledProperty->getValue($profiler));
    }

    public function testDisable()
    {
        $profiler = new Profiler();
        $profiler->disable();
        self::assertFalse($this->enabledProperty->getValue($profiler));
    }

    public function testReturnInSetFilterQueryType()
    {
        self::assertEquals($this->profiler, $this->profiler->setFilterQueryType());
    }

    public function testSetFilterQueryType()
    {
        $this->profiler->setFilterQueryType(-1);
        self::assertEquals(-1, $this->filterTypesProperty->getValue($this->profiler));
    }

    public function testGetFilterQueryType()
    {
        $value = 123;
        $this->profiler->setFilterQueryType($value);
        self::assertEquals($value, $this->profiler->getFilterQueryType());
    }

    public function testStartQueryReturnFalseWhenDisabled()
    {
        $profiler = new Profiler(false);
        self::assertFalse($profiler->startQuery(''));
    }

    public function testStartQueryReturnKey()
    {
        self::assertEquals(0, $this->profiler->startQuery('SELECT * FROM foo'));
        self::assertEquals(1, $this->profiler->startQuery('SELECT * FROM foo'));
    }

    public function startQueryProvider()
    {
        return [
            'query'  => [
                'CREATE TABLE foo (id INTEGER, name VARCHAR(50))',
                Profiler::QUERY,
            ],
            'select' => [
                'SELECT * FROM foo',
                Profiler::SELECT,
            ],
            'insert' => [
                "INSERT INTO foo (1, 'baz')",
                Profiler::INSERT,
            ],
            'update' => [
                "UPDATE foo SET name = 'bar' WHERE id = 1",
                Profiler::UPDATE,
            ],
            'delete' => [
                'DELETE FROM foo WHERE id = 1',
                Profiler::DELETE,
            ],
        ];
    }

    /**
     * @dataProvider startQueryProvider
     *
     * @param string $query     incoming query
     * @param int    $queryType result query type
     */
    public function testStartQuery($query, $queryType)
    {
        $this->profiler->startQuery($query);
        /** @var Query[] $profiles */
        $profiles = $this->profilesProperty->getValue($this->profiler);
        self::assertEquals(1, count($profiles));
        self::assertArrayHasKey(0, $profiles);
        self::assertInstanceOf(Query::class, $profiles[0]);
        self::assertEquals($queryType, $profiles[0]->getQueryType());
    }

    public function testEndQueryRaiseException()
    {
        $this->setExpectedException(RuntimeException::class, 'Query was not started.');
        $profiler = new Profiler();
        $profiler->endQuery();
    }

    public function testEndQueryReturnTrue()
    {
        $profiler = new Profiler();
        $profiler->startQuery('SELECT * FROM foo');
        self::assertTrue($profiler->endQuery());
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
    public function testGetQueryProfiles($queries, $queryTypes, $actualProfiles)
    {
        foreach ($queries as $query) {
            $this->profiler->startQuery($query);
            $this->profiler->endQuery();
        }
        $result = $this->profiler->getQueryProfiles($queryTypes);
        self::assertEquals(count($actualProfiles), count($result));
        if (! empty($result)) {
            foreach ($result as $key => $profile) {
                self::assertEquals($actualProfiles[$key], $profile->getSql());
            }
        }
    }

    public function testProfilerStartWithString()
    {
        $query = 'SELECT * FROM foo';
        $this->profiler->profilerStart($query);
        $result = $this->profiler->getQueryProfiles();
        self::assertEquals($query, $result[0]->getSql());
    }

    public function testProfilerStartWithStatement()
    {
        if (! extension_loaded('pdo_sqlite') || ! getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_SQLITE_ENABLED')) {
            $this->markTestSkipped('I cannot test without the pdo_sqlite extension');
        }
        $adapter = new Adapter(['driver' => 'Pdo', 'dsn' => sprintf('sqlite:%s', getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_SQLITE_DATABASE'))]);
        $sql = new Sql($adapter);
        $select = $sql->select()->from('foo')->where(['id' => 1]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $this->profiler->profilerStart($statement);
        $result = $this->profiler->getQueryProfiles();
        self::assertEquals($statement->getSql(), $result[0]->getSql());
        self::assertEquals($statement->getParameterContainer()->getNamedArray(), $result[0]->toArray()['parameters']);
    }

    public function testReturnSelfInProfilerFinish()
    {
        $this->profiler->profilerStart('SELECT * FROM foo');
        self::assertEquals($this->profiler, $this->profiler->profilerFinish());
    }
}
