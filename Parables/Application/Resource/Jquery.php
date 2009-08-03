<?php
class Parables_Application_Resource_Jquery extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * Initialize JQuery view helper
     *
     * @return  void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setJquery();
    }

    /**
     * Set Jquery
     *
     * @return void
     */
    public function setJquery()
    {
        ZendX_JQuery::enableView($this->_view);
        
        $options = $this->getOptions();

        foreach ($options as $key => $value) {
            switch (strtolower($key))
            {
                case 'cdnssl':
                    $this->_view->jQuery()->setCdnSsl($value);
                    break;

                case 'javascriptfiles':
                    foreach ($value as $name => $path) {
                        $this->_view->jQuery()->addJavascriptFile($path);
                    }
                    break;

                case 'localpath':
                    $this->_view->jQuery()->setLocalPath($value);
                    break;

                case 'rendermode':
                    $this->_view->jQuery()->setRenderMode($value);
                    break;

                case 'stylesheets':
                    foreach ($value as $name => $path) {
                        $this->_view->jQuery()->addStylesheet($path);
                    }
                    break;

                case 'uilocalpath':
                    $this->_view->jQuery()->setUiLocalPath($value);
                    break;

                case 'uiversion':
                    $this->_view->jQuery()->setUiVersion($value);
                    break;

                case 'version':
                    $this->_view->jQuery()->setVersion($value);
                    break;
            }
        }

        if ((array_key_exists('enable', $options)) && ($options['enable'])) {
            $this->_view->jQuery()->enable();
        } else {
            $this->_view->jQuery()->disable();
        }

        if ((array_key_exists('uiEnable', $options)) && ($options['uiEnable'])) {
            $this->_view->jQuery()->uiEnable();
        } else {
            $this->_view->jQuery()->uiDisable();
        }
    }
}
