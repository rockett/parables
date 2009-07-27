<?php
class Parables_Application_Resource_Doctrineconnections extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Doctrine_Connection_Common
     */
    protected $_currentConn = null;

    /**
     * @var array of reflected constants
     */
    protected $_doctrineConstants = array();

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
        $this->_doctrineConstants = $reflect->getConstants();

        // Get manager instance
        $manager = Doctrine_Manager::getInstance();

        // Setup connections, connection attributes and listeners
        foreach ($this->getOptions() as $key => $value) {
            // Fail if a DSN isn't provided
            if ((!array_key_exists('dsn', $value)) || (!is_array($value))) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception('A valid DSN is 
                    required.');
            }

            if ($dsn = $this->_getDsn($value['dsn'])) {
                // Open a connection
                $this->_currentConn = $manager->openConnection($dsn, $key);

                // Set connection attributes if provided
                if (array_key_exists('attributes', $value)) {
                    $this->setConnectionAttributes($value['attributes']);
                }

                // Set connection listeners if provided
                if (array_key_exists('listeners', $value)) {
                    $this->setConnectionListeners($value['listeners']);
                }
            }
        }
    }

    /**
     * Set connection attributes
     *
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    public function setConnectionAttributes()
    {
        $options = $this->getOptions();

        if (array_key_exists('attributes', $options)) {
            foreach ($options['attributes'] as $key => $value) {
                switch (strtoupper($key)) {
                    case 'ATTR_RESULT_CACHE':
                        if ($cache = $this->_getCache($value)) {
                            $this->_currentConn->setAttribute(Doctrine::ATTR_RESULT_CACHE, 
                                $cache);
                        }
                        break;

                    case 'ATTR_QUERY_CACHE':
                        if ($cache = $this->_getCache($value)) {
                            $this->_currentConn->setAttribute(Doctrine::ATTR_QUERY_CACHE, 
                                $cache);
                        }
                        break;

                    default:
                        if (array_key_exists(strtoupper($key), 
                        $this->_doctrineConstants)) {

                            $numericAttr = 
                                $this->_doctrineConstants[strtoupper($key)];

                            if (is_int($value)) {
                                $this->_currentConn->setAttribute($numericAttr, 
                                    $value);
                            } elseif (is_string($value)) {
                                if (!array_key_exists(strtoupper($value), 
                                $this->_doctrineConstants)) {
                                    require_once 
                                        'Zend/Application/Resource/Exception.php';
                                    throw new 
                                        Zend_Application_Resource_Exception('Invalid 
                                            connection attribute.');
                                }

                                $numericValue = 
                                    $this->_doctrineConstants[strtoupper($value)];
                                $this->_currentConn->setAttribute($numericAttr, 
                                    $numericValue);
                            } elseif (is_array($value)) {
                                $options = array();
                                foreach ($value as $subKey => $subValue) {
                                    $options[$subKey] = $subValue;
                                }
                                $this->_currentConn->setAttribute($numericAttr, 
                                    $options);
                            } else {
                                require_once 
                                    'Zend/Application/Resource/Exception.php';
                                throw new 
                                    Zend_Application_Resource_Exception('Invalid 
                                        connection attribute.');
                            }
                        }
                        break;
                }
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
    public function setConnectionListeners(array $options = null)
    {
        foreach ($options as $key => $value) {
            switch (strtoupper($key))
            {
                case 'ATTR_LISTENER':
                    foreach ($value as $alias => $class) {
                        if (class_exists($class)) {
                            $this->_currentConn->addListener(new $class(), 
                            $alias);
                        }
                    }
                    break;

                case 'ATTR_RECORD_LISTENER':
                    foreach ($value as $alias => $class) {
                        if (class_exists($class)) {
                            $this->_currentConn->addRecordListener(new 
                                $class(), $alias);
                        }
                    }
                    break;

                default:
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Invalid 
                        connection listener.');
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
            throw new Zend_Application_Resource_Exception('Invalid DSN.');
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
}
