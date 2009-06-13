<?php
class Parables_Application_Resource_Doctrine_Paths extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return array
     * @todo Handle modularity and model loading
     */
    public function init()
    {
        return $this->getOptions();
    }
}
