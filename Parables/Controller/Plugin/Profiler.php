<?php
class Parables_Controller_Plugin_Profiler extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Log
     */
    protected $_logger = null;

    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_logger = new Zend_Log(new Zend_Log_Writer_Firebug());
        $this->_logEvent('routeStartup');
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->_logEvent('routeShutdown');
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_logEvent('dispatchLoopStartup');
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $this->_logEvent('preDispatch');
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_logEvent('postDispatch');
    }

    public function dispatchLoopShutdown()
    {
        $this->_logEvent('dispatchLoopShutdown');
        $this->_logDoctrine(true);
    }

    private function _getTime()
    {
        return (microtime(true) - $_SERVER['REQUEST_TIME']);
    }

    private function _logDoctrine($enableQueries = false, $enableQueryParams = false)
    {
        $mgr = Doctrine_Manager::getInstance();
        $profilers = array();
        $queryCount = 0;
        $total = 0;

        foreach($mgr->getConnections() as $conn) {
            $listenerChain = $conn->getListener();
            if ($listenerChain instanceof Doctrine_EventListener_Chain) {
                if ($profiler = $listenerChain->get('profiler')) {
                    $profilers[] = $profiler;
                }
            }
        }

        foreach ($profilers as $profiler) {
            foreach ($profiler as $event) {
                if ($query = $event->getQuery()) {
                    $queryCount++;
                    if ($enableQueries) {
                        $this->_logger->info(($query));
                    }
                }

                if ($enableQueryParams) {
                    if ($params = $event->getParams()) {
                        foreach ($params as $param) {
                            $this->_logger->info($param);
                        }
                    }
                }

                $total += $event->getElapsedSecs();
            }
        }

        $this->_logger->info(sprintf('%d queries from %d connections in %s seconds', $queryCount, count($profilers), round($total, 4)));
    }

    private function _logEvent($data = '')
    {
        if (isset($this->_logger)) {
            $this->_logger->info(sprintf('%s @ %s', $data, round($this->_getTime(), 4)));
        }
    }
}
