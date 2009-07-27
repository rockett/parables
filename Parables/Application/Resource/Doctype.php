<?php
class Parables_Application_Resource_Doctype extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * Configure doctype
     *
     * @return  void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setDoctype();
    }

    /**
     * Set doctype
     *
     * @return  void
     */
    public function setDoctype()
    {
        $options = $this->getOptions();
        if (array_key_exists('doctype', $options)) {
            $this->_view->doctype($options['doctype']);
        }
    }
}
