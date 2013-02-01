<?php

namespace BjyProfiler\Db\Adapter\Driver\Pgsql;

use Zend\Db\Adapter\Driver\Pgsql\Statement;
use Zend\Db\Adapter\Profiler\ProfilerInterface;

class ProfilingStatement extends Statement
{
    protected $profiler;

    public function execute($parameters = null)
    {
        if ($parameters === null) {
            if ($this->parameterContainer != null) {
                $saveParams = (array) $this->parameterContainer->getNamedArray();
            } else {
                $saveParams = array();
            }
        } else {
            $saveParams = $parameters;
        }

        if (version_compare('5.3.6', phpversion(), '<=')) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $stack = array();
        }

        $queryId = $this->getProfiler()->startQuery($this->getSql(), $saveParams, $stack);
        $result = parent::execute($parameters);
        $this->getProfiler()->endQuery($queryId);

        return $result;
    }

    public function setProfiler(ProfilerInterface $p)
    {
        $this->profiler = $p;
        return $this;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }
}
