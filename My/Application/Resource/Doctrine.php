<?php
class My_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Doctrine_Manager
     */
    protected $_manager = null;

    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return array
     */
    public function init()
    {
        // @bug The fallback autoloader must be enabled
        $autoloader = Zend_Loader_Autoloader::getInstance();
        if (!$autoloader->isFallbackAutoloader()) {
            $autoloader->setFallbackAutoloader(true);
        }

        $manager = $this->getManager();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtolower($key))
            {
                case 'connections':
                    $resource = new My_Application_Resource_Doctrine_Connections($value);
                    $resource->init();
                    break;

                case 'paths':
                    $resource = new My_Application_Resource_Doctrine_Paths($value);
                    $paths = $resource->init();
                    break;

                default:
                    break;
            }
        }

        return array('paths' => $paths);
    }

    /**
     * Retrieve Doctrine_Manager instance
     *
     * @return Doctrine_Manager
     */
    public function getManager()
    {
        if (null === $this->_manager) {
            $this->_manager = Doctrine_Manager::getInstance();

            $options = $this->getOptions();
            if ((is_array($options)) && (array_key_exists('manager', $options))) {
                $this->_setAttributes($options['manager']);
            }
        }

        return $this->_manager;
    }

    /**
     * Retrieve a Doctrine_Cache instance
     * 
     * @param array $options
     * @return bool|Doctrine_Cache
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

    /**
     * Set manager attributes
     *
     * @param array $options
     * @return void
     */
    protected function _setAttributes(array $options = null)
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key))
            {
                case 'query_cache':
                case 'result_cache':
                    if ($cache = $this->_getCache($value)) {
                        $this->_manager->setAttribute($key, $cache);
                    }
                    break;

                default:
                    if (is_string($value)) {
                        $this->_manager->setAttribute($key, $value);
                    } elseif (is_array($value)) {
                        $options = array();
                        foreach ($value as $subKey => $subValue) {
                            $options[$subKey] = $subValue;
                        }
                        $this->_manager->setAttribute($key, $options);
                    } else {
                        require_once 'Zend/Application/Resource/Exception.php';
                        throw new Zend_Application_Resource_Exception('Invalid Doctrine resource attribute.');
                    }
                    break;
            }
        }
    }
}
