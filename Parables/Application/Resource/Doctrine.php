<?php
class Zend_Application_Resource_Doctrine
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Doctrine_Manager
     */
     protected $_manager = null;

    /**
     * @var array
     */
     protected $_resources = array();

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $options = $this->getOptions();

        if (array_key_exists('manager', $options)) {
            $this->_initManager($options['manager']);
        }

        if (array_key_exists('connections', $options)) {
            $this->_initConnections($options['connections']);
        }

        if (array_key_exists('paths', $options)) {
            $this->_initPaths($options['paths']);
        }

        return $this->_resources;
    }

    /**
     * Retrieve the Doctrine_Manager instance
     *
     * @return  void
     */
    public function getManager()
    {
        if (null === $this->_manager) {
            $this->_manager = Doctrine_Manager::getInstance();
        }
        return $this->_manager;
    }

    /**
     * Initialize the Doctrine_Manager
     *
     * @param   array $options
     * @return  void
     */
    protected function _initManager(array $options)
    {
        if (array_key_exists('attributes', $options)) {
            $this->_setAttributes($this->getManager(), $options['attributes']);
        }
    }

    /**
     * Initialize Doctrine connections
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _initConnections(array $options)
    {
        foreach($options as $key => $value) {
            if ((!is_array($value)) || (!array_key_exists('dsn', $value))) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception("A valid DSN is 
                    required for connection $key.");
            }

            $dsn = (is_array($value['dsn']))
                ? $this->_buildDsnFromArray($value['dsn'])
                : $value['dsn'];

            $conn = $this->getManager()->openConnection($dsn, $key);
            $this->_resources['connections'] = $key;
            
            if (array_key_exists('attributes', $value)) {
                $this->_setAttributes($conn, $value['attributes']);
            }

            if (array_key_exists('listeners', $value)) {
                $this->_setConnectionListeners($conn, $value['listeners']);
            }
        }
    }

    /**
     * Initializes Doctrine paths
     *
     * @param   array $options
     * @return  void
     */
    protected function _initPaths(array $options)
    {
        $this->_resources['paths'] = $options;
    }

    /**
     * Set attributes of a Doctrine_Configurable instance
     *
     * @param   Doctrine_Configurable $object
     * @param   array $attributes
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setAttributes(Doctrine_Configurable $object, array 
        $attributes)
    {
        $reflect = new ReflectionClass('Doctrine');
        $doctrineConstants = $reflect->getConstants();

        $attributes = array_change_key_case($attributes, CASE_UPPER);
        foreach ($attributes as $key => $value) {
            if (!array_key_exists($key, $doctrineConstants)) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception("$key is not a 
                    valid attribute.");
            }

            $attrIdx = $doctrineConstants[$key];
            $attrVal = $value;

            if ((Doctrine::ATTR_RESULT_CACHE == $attrIdx) || 
                (Doctrine::ATTR_QUERY_CACHE == $attrIdx)) {
                if (!$cache = $this->_getCache($value)) {
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Unable to 
                        retrieve cache.');
                }

                $attrVal = $cache;
            }

            $object->setAttribute($attrIdx, $attrVal);
        }
    }

    /**
     * Retrieve a Doctrine_Cache instance
     *
     * @param   array $options
     * @return  Doctrine_Cache
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _getCache(array $options)
    {
        if (!array_key_exists('class', $options)) {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception('Missing class 
                option.');
        }

        $class = $options['class'];
        if (!class_exists($class)) {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception(
                "$class does not exist.");
        }

        $cacheOptions = array();
        if ((is_array($options['options'])) && (array_key_exists('options', 
            $options))) {
            $cacheOptions = $options['options'];
        }

        return new $class($cacheOptions);
    }

    /**
     * Set connection listeners
     *
     * @param   Doctrine_Connection_Common $conn
     * @param   array $options
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setConnectionListeners(Doctrine_Connection_Common 
        $conn, array $options)
    {
        foreach ($options as $alias => $class) {
            if (!class_exists($class)) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception(
                    "$class does not exist.");
            }

            $conn->addListener(new $class(), $alias);
        }
    }

    /**
     * Build the DSN string
     *
     * @param   array $dsn
     * @return  string
     */
    protected function _buildDsnFromArray(array $dsn)
    {
        $options = null;
        if (array_key_exists('options', $dsn)) {
            $options = $this->_buildDsnOptionsFromArray($dsn['options']);
        }

        return sprintf('%s://%s:%s@%s/%s?%s',
            $dsn['adapter'],
            $dsn['user'],
            $dsn['pass'],
            $dsn['hostspec'],
            $dsn['database'],
            $options);
    }

    /**
     * Build the DSN options string from an array
     *
     * @param   array $options
     * @return  string
     */
    protected function _buildDsnOptionsFromArray(array $options)
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
