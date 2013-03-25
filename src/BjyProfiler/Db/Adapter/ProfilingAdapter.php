<?php

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler\Profiler;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver as ZdbDriver;
use Zend\Db\Adapter\Profiler\ProfilerInterface;

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
                case 'Zend\Db\Adapter\Driver\IbmDb2\IbmDb2':
                    $statementPrototype = new Driver\IbmDb2\ProfilingStatement();
                    break;
                case 'Zend\Db\Adapter\Driver\Mysqli\Mysqli':
                    $defaults = array('buffer_results' => false);
                    $options = array_intersect_key(array_merge($defaults, $options), $defaults);

                    $statementPrototype = new Driver\Mysqli\ProfilingStatement($options['buffer_results']);
                    break;
                case 'Zend\Db\Adapter\Driver\Oci8\Oci8':
                    $statementPrototype = new Driver\Oci8\ProfilingStatement();
                    break;
                case 'Zend\Db\Adapter\Driver\Sqlsrv\Sqlsrv':
                    $statementPrototype = new Driver\Sqlsrv\ProfilingStatement();
                    break;
                case 'Zend\Db\Adapter\Driver\Pgsql\Pgsql':
                    $statementPrototype = new Driver\Pgsql\ProfilingStatement();
                    break;
                case 'Zend\Db\Adapter\Driver\Pdo\Pdo':
                default:
                    $statementPrototype = new ZdbDriver\Pdo\Statement();
            }

            $statementPrototype->setProfiler($this->getProfiler());
            $driver->registerStatementPrototype($statementPrototype);
        }
    }
}

