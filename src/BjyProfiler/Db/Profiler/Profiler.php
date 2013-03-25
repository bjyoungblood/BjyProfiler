<?php

namespace BjyProfiler\Db\Profiler;

use Zend\Db\Adapter\Profiler\ProfilerInterface;

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
     * @var array
     */
    protected $profiles = array();

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $filterTypes;

    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
        $this->filterTypes = 127;
    }

    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    public function setFilterQueryType($queryTypes = null)
    {
        $this->filterTypes = $queryTypes;
        return $this;
    }

    public function getFilterQueryType()
    {
        return $this->filterTypes;
    }

    public function startQuery($sql, $parameters = null, $stack = null)
    {
        if (!$this->enabled) {
            return null;
        }

        if (is_null($stack)) {
            if (version_compare('5.3.6', phpversion(), '<=')) {
                $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            } else {
                $stack = array();
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

    public function endQuery()
    {
        if (!$this->enabled) {
            return false;
        }

        end($this->profiles)->end();
        return true;
    }

    public function getQueryProfiles($queryTypes = null)
    {
        $profiles = array();

        if (count($this->profiles)) {
            foreach ($this->profiles as $id => $profile) {
                if ($queryTypes === null) {
                    $queryTypes = $this->filterTypes;
                }

                if ($profile->getQueryType() & $queryTypes) {
                    $profiles[$id] = $profile;
                }
            }
        }

        return $profiles;
    }

    public function profilerStart($target)
    {
        $sql = $target->getSql();
        $params = $target->getParameterContainer();
        $this->startQuery($sql, $params);
    }

    public function profilerFinish()
    {
        $this->endQuery();
    }
}
