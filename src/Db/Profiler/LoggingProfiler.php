<?php
/**
 * User: Vladimir Garvardt
 * Date: 4/22/13
 * Time: 5:54 PM
 */

namespace BjyProfiler\Db\Profiler;


use Zend\Log\Logger;

class LoggingProfiler extends Profiler
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var int
     */
    protected $priority = Logger::DEBUG;
    /**
     * How many query profiles could be stored in memory.
     * Useful for long-running scripts with tons of queries that can take all the memory.
     * -1 - store all profiles
     * 0 - do not store any profiles
     * N > 0 - store N profiles, discard when there are more than N
     *
     * @var int
     */
    protected $maxProfiles = 100;
    /**
     * Query parameters to log on query start
     *
     * @var array
     * @see Query
     */
    protected $parametersStart = array('sql', 'parameters');
    /**
     * Query parameters to log on query finish
     *
     * @var array
     * @see Query
     */
    protected $parametersFinish = array('elapsed');

    public function __construct(Logger $logger, $enabled = true, array $options = array())
    {
        parent::__construct($enabled);
        $this->setLogger($logger);

        if (isset($options['priority'])) $this->setPriority($options['priority']);
        if (isset($options['maxProfiles'])) $this->setMaxProfiles($options['maxProfiles']);
        if (isset($options['parametersStart'])) $this->setParametersStart($options['parametersStart']);
        if (isset($options['parametersFinish'])) $this->setParametersFinish($options['parametersFinish']);
    }

    public function startQuery($sql, $parameters = null, $stack = null) {
        parent::startQuery($sql, $parameters, $stack);
        $this->logStart();
    }

    public function endQuery() {
        parent::endQuery();
        $this->logEnd();
        $this->trimToMaxQueries();
    }

    private function logStart() {
        /** @var Query $lastQuery */
        $lastQuery = end($this->profiles);
        $this->getLogger()->log(
            $this->getPriority(),
            'Query started',
            array_intersect_key($lastQuery->toArray(), array_flip($this->getParametersStart()))
        );
    }

    private function logEnd() {
        /** @var Query $lastQuery */
        $lastQuery = end($this->profiles);
        $this->getLogger()->log(
            $this->getPriority(),
            'Query finished',
            array_intersect_key($lastQuery->toArray(), array_flip($this->getParametersFinish()))
        );
    }

    private function trimToMaxQueries() {
        $maxProfiles = $this->getMaxProfiles();
        if ($maxProfiles > -1 && count($this->profiles) > $maxProfiles) {
            array_shift($this->profiles);
        }
    }

    /**
     * @param int $level
     */
    public function setPriority($level)
    {
        $this->priority = $level;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param \Zend\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Zend\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param int $maxProfiles
     */
    public function setMaxProfiles($maxProfiles)
    {
        $this->maxProfiles = $maxProfiles;
    }

    /**
     * @return int
     */
    public function getMaxProfiles()
    {
        return $this->maxProfiles;
    }

    /**
     * @param array $parametersFinish
     */
    public function setParametersFinish(array $parametersFinish)
    {
        $this->parametersFinish = $parametersFinish;
    }

    /**
     * @return array
     */
    public function getParametersFinish()
    {
        return $this->parametersFinish;
    }

    /**
     * @param array $parametersStart
     */
    public function setParametersStart(array $parametersStart)
    {
        $this->parametersStart = $parametersStart;
    }

    /**
     * @return array
     */
    public function getParametersStart()
    {
        return $this->parametersStart;
    }
}
