<?php

namespace BjyProfilerTest;

use BjyProfiler\Db\Adapter\ProfilingAdapter;
use BjyProfiler\Db\Adapter\ProfilingAdapterFactory;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Log\Logger;
use Zend\Log\Writer\Mock;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ProfilingAdapter
     */
    protected $adapter;

    /** @var Mock */
    protected $writer;

    protected function setUp()
    {
        if (! extension_loaded('pdo_sqlite') || ! getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_SQLITE_ENABLED')) {
            $this->markTestSkipped('I cannot test without the pdo_sqlite extension');
        }

        $config = [
            'factories' => [
                AdapterInterface::class => ProfilingAdapterFactory::class,
            ]
        ];

        $dbConfig = [
            'db' => [
                'driver' => 'Pdo',
                'dsn'    => sprintf('sqlite:%s', getenv('TESTS_ZEND_DB_ADAPTER_DRIVER_SQLITE_DATABASE')),
            ],
        ];

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig($config))->configureServiceManager($serviceManager);
        $serviceManager->setService('Configuration', $dbConfig);
        $this->adapter = $serviceManager->get(AdapterInterface::class);
        $logger = new Logger();
        $this->writer = new Mock();
        $logger->addWriter($this->writer);
        $this->adapter->getProfiler()->setLogger($logger);
    }

    public function testQueryViaConnection()
    {
        $query = 'CREATE TABLE foo(id INTEGER NOT NULL, name VARCHAR(50))';
        $this->adapter->getDriver()->getConnection()->execute($query);
        self::assertEquals($query, $this->writer->events[0]['extra']['sql']);
    }

    public function testQueryViaStatement()
    {
        $values = ['id' => 1, 'name' => 'bar'];
        $this->adapter->getDriver()->getConnection()->execute('CREATE TABLE foo(id INTEGER NOT NULL, name VARCHAR(50))');
        $sql = new Sql($this->adapter);
        $insert = $sql->insert('foo');
        $insert->values($values);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        // events[0] - Query started: CREATE TABLE
        // events[1] - Query finished
        self::assertEquals('INSERT INTO "foo" ("id", "name") VALUES (:id, :name)', $this->writer->events[2]['extra']['sql']);
        self::assertEquals($values, $this->writer->events[2]['extra']['parameters']);
        self::assertEquals('Query finished', $this->writer->events[3]['message']);
    }

    public function testQueryViaAdapter()
    {
        $query = 'SELECT * FROM foo';
        $this->adapter->getDriver()->getConnection()->execute('CREATE TABLE foo(id INTEGER NOT NULL, name VARCHAR(50))');
        $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        // events[0] - Query started: CREATE TABLE
        // events[1] - Query finished
        self::assertEquals($query, $this->writer->events[2]['extra']['sql']);
        self::assertEquals('Query finished', $this->writer->events[3]['message']);
    }
}
