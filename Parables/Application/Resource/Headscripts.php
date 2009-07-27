<?php
class Parables_Application_Resource_Headscripts extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * HeadScript view helper initialization
     *
     * @return void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setHeadScripts();
    }

    /**
     * Set <script> elements
     *
     * @return void
     */
    public function setHeadScripts()
    {
        foreach ($this->getOptions() as $headScript => $options) {
            $mode = ((array_key_exists('mode', $options)) && 
                (isset($options['mode'])))
                    ? $options['mode']
                    : 'FILE';

            $spec = ((array_key_exists('spec', $options)) && 
                (isset($options['spec'])))
                    ? $options['spec']
                    : null;

            $placement = ((array_key_exists('placement', $options)) && 
                (isset($options['placement'])))
                    ? $options['placement']
                    : 'APPEND';

            $this->_view->headScript($mode, $spec, $placement);
        }
    }
}
