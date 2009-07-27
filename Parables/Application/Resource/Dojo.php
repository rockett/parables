<?php
class Parables_Application_Resource_Dojo extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * Initialize Dojo view helper
     *
     * @return  void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setDojo();
    }

    /**
     * Set Dojo
     *
     * @return void
     */
    public function setDojo()
    {
        Zend_Dojo::enableView($this->_view);
        
        $options = $this->getOptions();

        foreach ($options as $key => $value) {
            switch (strtolower($key))
            {
                case 'cdnbase':
                    $this->_view->dojo()->setCdnBase($value);
                    break;

                case 'cdndojopath':
                    $this->_view->dojo()->setCdnDojoPath($value);
                    break;

                case 'cdnversion':
                    $this->_view->dojo()->setCdnVersion($value);
                    break;

                case 'djconfig':
                    $this->_view->dojo()->setDjConfig($value);
                    break;

                case 'layers':
                    foreach ($value as $path) {
                        $this->_view->dojo()->addLayer($path);
                    }
                    break;

                case 'localpath':
                    $this->_view->dojo()->setLocalPath($value);
                    break;

                case 'modules':
                    foreach ($value as $module) {
                        $this->_view->dojo()->requireModule($module);
                    }
                    break;

                case 'stylesheets':
                    foreach ($value as $path) {
                        $this->_view->dojo()->addStylesheet($path);
                    }
                    break;

                case 'stylesheetmodules':
                    foreach ($value as $module) {
                        $this->_view->dojo()->addStylesheetModule($module);
                    }
                    break;

                default:
                    break;
            }
        }

        if ((array_key_exists('enable', $options)) && 
            (isset($options['enable']))) {
            $this->_view->dojo()->enable();
        } else {
            $this->_view->dojo()->disable();
        }
    }
}
