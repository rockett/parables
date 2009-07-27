<?php
class Parables_Application_Resource_Doctrineconnections extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Doctrine_Connection_Common
     */
    protected $_current;

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

        $manager = Doctrine_Manager::getInstance();

        foreach ($this->getOptions() as $key => $value) {
            if ((!is_array($value)) || (!array_key_exists('dsn', $value))) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception('Invalid 
                    Doctrine connection resource.');
            }

            if ($dsn = $this->_getDsn($value['dsn'])) {
                $this->_current = $manager->openConnection($dsn, $key);

                if (array_key_exists('attributes', $value)) {
                    $this->_setAttributes($value['attributes']);
                }

                if (array_key_exists('listeners', $value)) {
                    $this->_setListeners($value['listeners']);
                }
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

    /**
     * Get DSN string
     * 
     * @param   string|array $dsn
     * @return  string
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _getDsn($dsn = null)
    {
        if (is_string($dsn)) {
            return $dsn;
        } elseif (is_array($dsn)) {
            $options = null;
            if (array_key_exists('options', $dsn)) {
                $options = $this->_getDsnOptions($dsn['options']);
            }

            return sprintf('%s://%s:%s@%s/%s?%s',
                $dsn['adapter'],
                $dsn['user'],
                $dsn['pass'],
                $dsn['hostspec'],
                $dsn['database'],
                $options);
        } else {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception('Invalid Doctrine 
                connection resource dsn.');
        }
    }

    /**
     * Get connection options string from an array
     * 
     * @param   array $options
     * @return  string
     */
    protected function _getDsnOptions(array $options = null)
    {
        $optionsString  = '';

        foreach ($options as $key => $value) {
            if ($value == end($options)) {
                $optionsString .= "$key=$value";
            } else {
                $optionsString .= "$key=$value&";
            }
        }

        return $optionsString;
    }

    /**
     * Set connection attributes
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setAttributes(array $options = null)
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'query_cache':
                case 'result_cache':
                    if ($cache = $this->_getCache($value)) {
                        $this->_current->setAttribute($key, $cache);
                    }
                    break;

                default:
                    if (is_string($value)) {
                        $this->_current->setAttribute($key, $value);
                    } elseif (is_array($value)) {
                        $options = array();
                        foreach ($value as $subKey => $subValue) {
                            $options[$subKey] = $subValue;
                        }
                        $this->_current->setAttribute($key, $options);
                    } else {
                        require_once 'Zend/Application/Resource/Exception.php';
                        throw new Zend_Application_Resource_Exception('Invalid 
                            Doctrine resource attribute.');
                    }
                    break;
            }
        }
    }

    /**
     * Set connection listeners
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setListeners(array $options = null)
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key))
            {
                case 'connection':
                    foreach ($value as $alias => $class) {
                        if (class_exists($class)) {
                            $this->_current->addListener(new $class, $alias);
                        }
                    }
                    break;

                case 'record':
                    foreach ($value as $alias => $class) {
                        if (class_exists($class)) {
                            $this->_current->addRecordListener(new $class(), $alias);
                        }
                    }
                    break;

                default:
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Invalid 
                        Doctrine resource listener.');
            }
        }
    }
}
