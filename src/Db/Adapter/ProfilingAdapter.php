<?php

declare(strict_types=1);

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler\Profiler;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver as ZdbDriver;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\Db\ResultSet;

class ProfilingAdapter extends Adapter
{
    protected $profiler;

    public function setProfiler(ProfilerInterface $p)
    {
        $this->profiler = $p;
        return $this;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE, ResultSet\ResultSetInterface $resultPrototype = null)
    {
        $this->getProfiler()->startQuery($sql);
        $return = parent::query($sql, $parametersOrQueryMode, $resultPrototype);
        $this->getProfiler()->endQuery();
        return $return;
    }

    public function injectProfilingStatementPrototype(array $options = array())
    {
        $profiler = $this->getProfiler();
        if (!$profiler instanceof Profiler) {
            throw new \InvalidArgumentException('No profiler attached!');
        }

        $driver = $this->getDriver();
        if (method_exists($driver, 'registerStatementPrototype')) {
            $driverName = get_class($driver);
            switch ($driverName) {
                case 'Laminas\Db\Adapter\Driver\IbmDb2\IbmDb2':
                    $statementPrototype = new ZdbDriver\IbmDb2\Statement();
                    break;
                case 'Laminas\Db\Adapter\Driver\Mysqli\Mysqli':
                    $defaults = array('buffer_results' => false);
                    $options = array_intersect_key(array_merge($defaults, $options), $defaults);

                    $statementPrototype = new ZdbDriver\Mysqli\Statement($options['buffer_results']);
                    break;
                case 'Laminas\Db\Adapter\Driver\Oci8\Oci8':
                    $statementPrototype = new ZdbDriver\Oci8\Statement();
                    break;
                case 'Laminas\Db\Adapter\Driver\Sqlsrv\Sqlsrv':
                    $statementPrototype = new ZdbDriver\Sqlsrv\Statement();
                    break;
                case 'Laminas\Db\Adapter\Driver\Pgsql\Pgsql':
                    $statementPrototype = new ZdbDriver\Pgsql\Statement();
                    break;
                case 'Laminas\Db\Adapter\Driver\Pdo\Pdo':
                default:
                    $statementPrototype = new ZdbDriver\Pdo\Statement();
            }

            $statementPrototype->setProfiler($this->getProfiler());
            $driver->registerStatementPrototype($statementPrototype);
        }
    }
}

