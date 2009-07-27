<?php
class Parables_Application_Resource_Headmetas extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * HeadMeta view helper initialization
     *
     * @return void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setHeadMetas();
    }

    /**
     * Set <meta> elements
     *
     * @return void
     */
    public function setHeadMetas()
    {
        foreach ($this->getOptions() as $headMeta => $options) {
            $content = ((array_key_exists('content', $options)) && 
                (isset($options['content'])))
                    ? $options['content']
                    : null;

            $keyValue = ((array_key_exists('keyValue', $options)) && 
                (isset($options['keyValue'])))
                    ? $options['keyValue']
                    : null;

            $keyType = ((array_key_exists('keyType', $options)) && 
                (isset($options['keyType'])))
                    ? $options['keyType']
                    : null;

            $modifiers = ((array_key_exists('modifiers', $options)) && 
                (isset($options['modifiers'])))
                    ? $options['modifiers']
                    : array();

            $placement = ((array_key_exists('placement', $options)) && 
                (isset($options['placement'])))
                    ? $options['placement']
                    : 'APPEND';

            $this->_view->headMeta($content, $keyValue, $keyType, $modifiers, 
                $placement);
        }
    }
}
