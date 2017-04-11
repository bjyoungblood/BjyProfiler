<?php

namespace BjyProfiler\Db\Profiler;

use BjyProfiler\Exception\RuntimeException;

class Query
{
    /**
     * @var string
     */
    protected $sql = '';
    /**
     * @var int
     */
    protected $queryType = 0;
    /**
     * @var float
     */
    protected $startTime = null;
    /**
     * @var float
     */
    protected $endTime = null;
    /**
     * @var array|null
     */
    protected $parameters = null;
    /**
     * @var array
     */
    protected $callStack = [];

    /**
     * Query constructor.
     * @param string $sql
     * @param int    $queryType
     * @param array  $parameters
     * @param array  $stack
     */
    public function __construct($sql, $queryType, $parameters = null, $stack = [])
    {
        $this->sql = $sql;
        $this->queryType = $queryType;
        $this->parameters = $parameters;
        $this->callStack = $stack;
    }

    /**
     * @return static
     */
    public function start()
    {
        $this->startTime = microtime(true);
        return $this;
    }

    /**
     * @return static
     */
    public function end()
    {
        if (null === $this->startTime) {
            throw new RuntimeException('Query was not started.');
        }
        $this->endTime = microtime(true);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasEnded()
    {
        return ($this->endTime !== null);
    }

    /**
     * @return bool|float
     */
    public function getElapsedTime()
    {
        if (! $this->hasEnded()) {
            return false;
        }
        return $this->endTime - $this->startTime;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return null|float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return null|float
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @return int
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        switch ($this->queryType) {
            case Profiler::SELECT:
                $type = 'SELECT';
                break;
            case Profiler::INSERT:
                $type = 'INSERT';
                break;
            case Profiler::UPDATE:
                $type = 'UPDATE';
                break;
            case Profiler::DELETE:
                $type = 'DELETE';
                break;
            case Profiler::CONNECT:
                $type = 'CONNECT';
                break;
            case Profiler::QUERY:
            default:
                $type = 'OTHER';
                break;
        }

        return [
            'type'       => $type,
            'sql'        => $this->sql,
            'start'      => $this->startTime,
            'end'        => $this->endTime,
            'elapsed'    => $this->getElapsedTime(),
            'parameters' => $this->parameters,
            'stack'      => $this->callStack
        ];
    }
}
