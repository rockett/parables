<?php
class Parables_Application_Resource_Doctrinemanager extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        // @bug The fallback autoloader must be enabled
        $autoloader = Zend_Loader_Autoloader::getInstance();
        if (!$autoloader->isFallbackAutoloader()) {
            $autoloader->setFallbackAutoloader(true);
        }

        $options = $this->getOptions();
        if (array_key_exists('attributes', $options)) {
            $this->_setManagerAttributes($options['attributes']);
        }
    }

    /**
     * Set manager attributes
     *
     * @param   array $attributes
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setManagerAttributes(array $attributes = array())
    {
        $reflect = new ReflectionClass('Doctrine');
        $doctrineConstants = $reflect->getConstants();

        $manager = Doctrine_Manager::getInstance();

        $attribs = array_change_key_case($attributes, CASE_UPPER);
        foreach ($attribs as $name => $value) {
            if (!array_key_exists($name, $doctrineConstants)) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception("$name is not a 
                    valid attribute.");
            }

            $attrIdx = $doctrineConstants[$name];
            $attrVal = $value;

            if (is_string($value)) {
                if (!array_key_exists(strtoupper($value), $doctrineConstants)) {
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception("$value is 
                        not a valid $name attribute value.");
                }

                $attrVal = $doctrineConstants[strtoupper($value)];
            }

            switch ($attrIdx)
            {
                case 150: // ATTR_RESULT_CACHE
                case 157: // ATTR_QUERY_CACHE
                    if (!$cache = $this->_getCache($value)) {
                        require_once 
                            'Zend/Application/Resource/Exception.php';
                        throw new Zend_Application_Resource_Exception('Unable 
                            to retrieve cache.');
                    }
                    $attrVal = $cache;
                    break;

                default:
                    if (is_array($value)) {
                        $options = array();
                        foreach ($value as $subKey => $subVal) {
                            $options[$subKey] = $subVal;
                        }
                        $attrVal = $options;
                    }
                    break;
            }

            $manager->setAttribute($attrIdx, $attrVal);
        }
    }

    /**
     * Retrieve a Doctrine_Cache instance
     * 
     * @param   array $options
     * @return  Doctrine_Cache
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _getCache(array $options = array())
    {
        if (!array_key_exists('class', $options)) {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception("Missing 'class' 
                cache option.");
        }

        $class = $options['class'];
        if (!class_exists($class)) {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception('Cache class does 
                not exist.');
        }

        $cacheOptions = array();
        if ((is_array($options['options'])) && (array_key_exists('options', 
            $options))) {
            $cacheOptions = $options['options'];
        }

        return new $class($cacheOptions);
    }
}
