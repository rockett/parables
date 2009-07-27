<?php
class Parables_Application_Resource_Doctrinemanager extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return  void
     */
    public function init()
    {
        // @bug The fallback autoloader must be enabled
        $autoloader = Zend_Loader_Autoloader::getInstance();
        if (!$autoloader->isFallbackAutoloader()) {
            $autoloader->setFallbackAutoloader(true);
        }

        $this->setManagerAttributes();
    }

    /**
     * Set manager attributes
     *
     * @return  void
     * @throws Zend_Application_Resource_Exception
     */
    public function setManagerAttributes()
    {
        $manager = Doctrine_Manager::getInstance();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtolower($key))
            {
                case 'query_cache':
                case 'result_cache':
                    if ($cache = $this->_getCache($value)) {
                        $manager->setAttribute($key, $cache);
                    }
                    break;

                default:
                    if (is_string($value)) {
                        $manager->setAttribute($key, $value);
                    } elseif (is_array($value)) {
                        $options = array();
                        foreach ($value as $subKey => $subValue) {
                            $options[$subKey] = $subValue;
                        }
                        $manager->setAttribute($key, $options);
                    } else {
                        require_once 'Zend/Application/Resource/Exception.php';
                        throw new Zend_Application_Resource_Exception('Invalid Doctrine resource attribute.');
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
                if ((is_array($options['options'])) && (array_key_exists('options', $options))) {
                    $cacheOptions = $options['options'];
                }
                return new $class($cacheOptions);
            }
        }

        return false;
    }
}
