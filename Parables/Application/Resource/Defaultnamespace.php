<?php
class Parables_Application_Resource_Defaultnamespace extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Initialize session namespace
     *
     * @return  Zend_Session_Namespace
     */
    public function init()
    {
        Zend_Session::start();
        $defaultNamespace = new Zend_Session_Namespace('Default');

        if (!isset($defaultNamespace->initialized)) {
            Zend_Session::regenerateId();
            $defaultNamespace->initialized = true;
        }

        return $defaultNamespace;
    }
}
