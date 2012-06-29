<?php

namespace BjyProfiler\Db\Profiler;

class Query
{
    protected $sql = '';
    protected $queryType = 0;
    protected $startTime = null;
    protected $endTime = null;

    public function __construct($sql, $queryType)
    {
        $this->sql = $sql;
        $this->queryType = $queryType;
    }

    public function start()
    {
        $this->startTime = microtime(true);
        return $this;
    }

    public function end()
    {
        $this->endTime = microtime(true);
        return $this;
    }

    public function hasEnded()
    {
        return ($this->endTime !== null);
    }

    public function getElapsedTime()
    {
        if (!$this->hasEnded()) {
            return false;
        }
        return $this->endTime - $this->startTime;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function getQueryType()
    {
        return $this->queryType;
    }
}
