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

        // Get Doctrine constants
        $reflect = new ReflectionClass('Doctrine');
        $doctrineConstants = $reflect->getConstants();

        // Get manager instance
        $manager = Doctrine_Manager::getInstance();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtoupper($key)) {
                case 'ATTR_RESULT_CACHE':
                    if ($cache = $this->_getCache($value)) {
                        $manager->setAttribute(Doctrine::ATTR_RESULT_CACHE, 
                            $cache);
                    }
                    break;

                case 'ATTR_QUERY_CACHE':
                    if ($cache = $this->_getCache($value)) {
                        $manager->setAttribute(Doctrine::ATTR_QUERY_CACHE, 
                            $cache);
                    }
                    break;

                default:
                    if (array_key_exists(strtoupper($key), 
                    $doctrineConstants)) {
                        $numericAttr = $doctrineConstants[strtoupper($key)];

                        if (is_int($value)) {
                            $manager->setAttribute($numericAttr, $value);
                        } elseif (is_string($value)) {
                            if (!array_key_exists(strtoupper($value), 
                                $doctrineConstants)) {
                                require_once 
                                    'Zend/Application/Resource/Exception.php';
                                throw new 
                                    Zend_Application_Resource_Exception('Invalid 
                                        manager attribute.');
                            }

                            $numericValue = 
                                $doctrineConstants[strtoupper($value)];
                            $manager->setAttribute($numericAttr, 
                                $numericValue);
                        } elseif (is_array($value)) {
                            $options = array();
                            foreach ($value as $subKey => $subValue) {
                                $options[$subKey] = $subValue;
                            }
                            $manager->setAttribute($numericAttr, $options);
                        } else {
                            require_once 
                                'Zend/Application/Resource/Exception.php';
                            throw new 
                                Zend_Application_Resource_Exception('Invalid 
                                    manager attribute.');
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Retrieve a Doctrine_Cache instance
     * 
     * @param   array $options
     * @return  bool|Doctrine_Cache
     */
    protected function _getCache(array $options = null)
    {
        if ((is_array($options)) && (array_key_exists('class', $options))) {
            $class = $options['class'];
            if (class_exists($class)) {
                $cacheOptions = array();
                if ((is_array($options['options'])) && 
                    (array_key_exists('options', $options))) {
                    $cacheOptions = $options['options'];
                }
                return new $class($cacheOptions);
            }
        }

        return false;
    }
}
