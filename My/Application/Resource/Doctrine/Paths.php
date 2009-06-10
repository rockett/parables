<?php
class My_Application_Resource_Doctrine_Paths extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return array
     * @todo Model loading
     * @todo Handle modularity
     */
    public function init()
    {
        return $this->getOptions();
    }
}
