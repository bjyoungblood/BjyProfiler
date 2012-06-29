<?php

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler\Profiler;
use Zend\Db\Adapter\Adapter;

class ProfilingAdapter extends Adapter
{
    protected $profiler;

    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE)
    {
        $queryId = $this->profiler->startQuery($sql);
        $result = parent::query($sql, $parametersOrQueryMode);
        $this->profiler->endQuery($queryId);

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
