<?php

namespace BjyProfiler\Collector;

use BjyProfiler\Db\Profiler\Profiler;
use ZendDeveloperTools\Collector\DbCollector as ZDTDbCollector;
use Zend\Mvc\MvcEvent;

class DbCollector extends ZDTDbCollector
{
    protected $profiler;

    public function getName()
    {
        return 'db';
    }

    public function getPriority()
    {
        return 99;
    }

    public function collect(MvcEvent $mvcEvent)
    {
        return array();
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    public function setProfiler($profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    public function getQueries()
    {
        return count($this->profiler->getQueryProfiles());
    }

    public function getCreateQueries()
    {
        return count($this->profiler->getQueryProfiles(Profiler::INSERT));
    }

    public function getReadQueries()
    {
        return count($this->profiler->getQueryProfiles(Profiler::SELECT));
    }

    public function getUpdateQueries()
    {
        return count($this->profiler->getQueryProfiles(Profiler::UPDATE));
    }

    public function getDeleteQueries()
    {
        return count($this->profiler->getQueryProfiles(Profiler::DELETE));
    }

    public function getTime()
    {
        return $this->sumQueryTime($this->profiler->getQueryProfiles());
    }

    public function getCreateTime()
    {
        return $this->sumQueryTime($this->profiler->getQueryProfiles(Profiler::INSERT));
    }

    public function getReadTime()
    {
        return $this->sumQueryTime($this->profiler->getQueryProfiles(Profiler::SELECT));
    }

    public function getUpdateTime()
    {
        return $this->sumQueryTime($this->profiler->getQueryProfiles(Profiler::UPDATE));
    }

    public function getDeleteTime()
    {
        return $this->sumQueryTime($this->profiler->getQueryProfiles(Profiler::DELETE));
    }

    protected function sumQueryTime(array$queries)
    {
        $time = 0;
        foreach ($queries as $q) {
            $time += $q->getElapsedTime();
        }

        return $time;
    }

    public function serialize()
    {
        $this->profiler = serialize($this->profiler);
        return $this->profiler;
    }

    public function unserialize($serialized)
    {
        $this->profiler = unserialize($serialized);
    }
}
