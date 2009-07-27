<?php
class Parables_Application_Resource_Headstyles extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * HeadStyle view helper initialization
     *
     * @return void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setHeadStyles();
    }

    /**
     * Set <style> elements
     *
     * @return void
     */
    public function setHeadStyles()
    {
        foreach ($this->getOptions() as $headStyle => $options) {
            $content = ((array_key_exists('content', $options)) && 
                (isset($options['content'])))
                    ? $options['content']
                    : null;

            $placement = ((array_key_exists('placement', $options)) && 
                (isset($options['placement'])))
                    ? $options['placement']
                    : 'APPEND';

            $attributes = ((array_key_exists('attributes', $options)) && 
                (isset($options['attributes'])))
                    ? $options['attributes']
                    : array();

            $this->_view->headStyle($content, $placement, $attributes);
        }
    }
}
