<?php

namespace BjyProfiler\Db\Adapter\Driver\Pdo;

use BjyProfiler\Db\Profiler\Profiler;
use Zend\Db\Adapter\Driver\Pdo\Statement;

class ProfilingStatement extends Statement
{
    protected $profiler;

    public function execute($parameters = null)
    {
        $queryId = $this->getProfiler()->startQuery($this->getSql());
        $result = parent::execute($parameters);
        $this->getProfiler()->endQuery($queryId);

        return $result;
    }

    public function setProfiler(Profiler $p)
    {
        $this->profiler = $p;
        return $this;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }
}
