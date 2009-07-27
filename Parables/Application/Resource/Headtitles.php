<?php
class Parables_Application_Resource_Headtitles extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_View
     */
    protected $_view = null;

    /**
     * HeadTitle view helper initialization
     *
     * @return void
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('view');
        $this->_view = $this->getBootstrap()->getResource('view');
        $this->setHeadTitles();
    }

    /**
     * Set <title> element
     *
     * @return void
     */
    public function setHeadTitles()
    {
        $options = $this->getOptions();

        if ((array_key_exists('separator', $options)) && 
            (isset($options['separator']))) {
            $this->_view->headTitle()->setSeparator($options['separator']);
            unset($options['separator']);
        }

        foreach ($options as $headTitle => $headTitleOptions) {
            if ((array_key_exists('title', $headTitleOptions)) && 
                (isset($headTitleOptions['title']))) {
                $this->_view->headTitle($headTitleOptions['title']);
            }
        }
    }
}
