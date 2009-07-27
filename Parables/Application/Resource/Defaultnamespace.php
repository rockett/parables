<?php
class Parables_Application_Resource_Defaultnamespace extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Session_Namespace
     */
    protected $_defaultNamespace = null;

    /**
     * Initialize session namespace
     *
     * @return  Zend_Session_Namespace
     */
    public function init()
    {
        // Ensure session is initialized
        $this->getBootstrap()->bootstrap('session');
        return $this->getDefaultnamespace();
    }

    /**
     * Retrieve session namespace
     *
     * @return  Zend_Session_Namespace
     */
    public function getDefaultnamespace()
    {
        if (null === $this->_defaultNamespace) {
            Zend_Session::start();
            $defaultNamespace = new Zend_Session_Namespace();
            $this->_defaultNamespace = $defaultNamespace;
        }

        if (!isset($this->_defaultNamespace->initialized)) {
            Zend_Session::regenerateId();
            $this->_defaultNamespace->initialized = true;
        }

        return $this->_defaultNamespace;
    }
}
