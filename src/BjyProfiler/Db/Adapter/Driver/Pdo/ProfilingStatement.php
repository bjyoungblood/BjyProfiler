<?php

namespace BjyProfiler\Db\Adapter\Driver\Pdo;

use BjyProfiler\Db\Profiler\Profiler;
use Zend\Db\Adapter\Driver\Pdo\Statement;

class ProfilingStatement extends Statement
{
    protected $profiler;

    public function execute($parameters = null)
    {
        if ($parameters === null) {
            $saveParams = (array) $this->parameterContainer->getNamedArray();
        } else {
            $saveParams = $parameters;
        }

        if (function_exists('xdebug_get_function_stack')) {
            $stack = xdebug_get_function_stack();
        } else {
            $stack = array();
        }

        $queryId = $this->getProfiler()->startQuery($this->getSql(), $saveParams, $stack);
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
