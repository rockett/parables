<?php
class Parables_Application_Resource_Namespaces extends Zend_Application_Resource_ResourceAbstract
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
        // Ensure that session options have been set
        $this->getBootstrap()->bootstrap('session');
        return $this->getDefaultNamespace();
    }

    /**
     * Retrieve session namespace
     *
     * @return  Zend_Session_Namespace
     */
    public function getDefaultNamespace()
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
