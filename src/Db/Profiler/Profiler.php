<?php

namespace BjyProfiler\Db\Profiler;

use BjyProfiler\Exception\RuntimeException;
use Zend\Db\Adapter\Profiler\ProfilerInterface;
use Zend\Db\Adapter\StatementContainerInterface;

class Profiler implements ProfilerInterface
{
    /**
     * Logical OR these together to get a proper query type filter
     */
    const CONNECT = 1;
    const QUERY = 2;
    const INSERT = 4;
    const UPDATE = 8;
    const DELETE = 16;
    const SELECT = 32;
    const TRANSACTION = 64;

    /**
     * @var Query[]
     */
    protected $profiles = [];

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $filterTypes;

    /**
     * Profiler constructor.
     * @param bool $enabled
     */
    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
        $this->filterTypes = 127;
    }

    /**
     * @return static
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * @return static
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * @param int $queryTypes
     * @return static
     */
    public function setFilterQueryType($queryTypes = null)
    {
        $this->filterTypes = $queryTypes;
        return $this;
    }

    /**
     * @return int
     */
    public function getFilterQueryType()
    {
        return $this->filterTypes;
    }

    /**
     * @param string     $sql
     * @param array|null $parameters
     * @param array|null $stack
     * @return int|bool
     */
    public function startQuery($sql, $parameters = null, $stack = null)
    {
        if (! $this->enabled) {
            return false;
        }

        if (null === $stack) {
            if (version_compare('5.3.6', phpversion(), '<=')) {
                $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            } else {
                $stack = [];
            }
        }

        // try to detect the query type
        switch (strtolower(substr(ltrim($sql), 0, 6))) {
            case 'select':
                $queryType = static::SELECT;
                break;
            case 'insert':
                $queryType = static::INSERT;
                break;
            case 'update':
                $queryType = static::UPDATE;
                break;
            case 'delete':
                $queryType = static::DELETE;
                break;
            default:
                $queryType = static::QUERY;
                break;
        }

        $profile = new Query($sql, $queryType, $parameters, $stack);
        $this->profiles[] = $profile;
        $profile->start();

        end($this->profiles);
        return key($this->profiles);
    }

    /**
     * @return bool
     */
    public function endQuery()
    {
        if (! $this->enabled) {
            return false;
        }

        if (empty($this->profiles)) {
            throw new RuntimeException('Query was not started.');
        }

        end($this->profiles)->end();
        return true;
    }

    /**
     * @param int|null $queryTypes
     * @return Query[]
     */
    public function getQueryProfiles($queryTypes = null)
    {
        if (empty($this->profiles)) {
            return [];
        }

        $profiles = [];

        foreach ($this->profiles as $id => $profile) {
            if (null === $queryTypes) {
                $queryTypes = $this->filterTypes;
            }

            if ($profile->getQueryType() & $queryTypes) {
                $profiles[$id] = $profile;
            }
        }

        return $profiles;
    }

    /**
     * @param string|StatementContainerInterface $target
     * @return static
     */
    public function profilerStart($target)
    {
        if ($target instanceof StatementContainerInterface) {
            $sql = $target->getSql();
            $params = $target->getParameterContainer()->getNamedArray();
        } else {
            $sql = $target;
            $params = [];
        }
        $this->startQuery($sql, $params);
        return $this;
    }

    /**
     * @return static
     */
    public function profilerFinish()
    {
        $this->endQuery();
        return $this;
    }
}
