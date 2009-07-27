<?php
class Parables_Application_Resource_Headlinks extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * HeadLink view helper initialization
     *
     * @return void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setHeadLinks();
    }

    /**
     * Set <link> elements
     *
     * @return void
     */
    public function setHeadLinks()
    {
        foreach ($this->getOptions() as $headLink => $options) {
            $attributes = ((array_key_exists('attributes', $options)) && 
                (isset($options['attributes'])))
                    ? $options['attributes']
                    : array();

            $placement = ((array_key_exists('placement', $options)) && 
                (isset($options['placement'])))
                    ? $options['placement']
                    : 'APPEND';

            $this->_view->headLink($attributes, $placement);
        }
    }
}
