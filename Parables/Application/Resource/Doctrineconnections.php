<?php
class Parables_Application_Resource_Doctrineconnections extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Doctrine_Connection_Common
     */
    protected $_currentConn = null;

    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $manager = Doctrine_Manager::getInstance();
        foreach ($this->getOptions() as $key => $value) {
            if ((!is_array($value)) || (!array_key_exists('dsn', $value))) {
                require_once 'Zend/Application/Resource/Exception.php';
                throw new Zend_Application_Resource_Exception('A valid DSN is 
                    required.');
            }

            $dsn = null;
            if (is_array($value['dsn'])) {
                $dsn = $this->_buildDsnFromArray($value['dsn']);
            } else {
                $dsn = $value['dsn'];
            }
            $this->_currentConn = $manager->openConnection($dsn, $key);

            if (array_key_exists('attributes', $value)) {
                $this->_setConnectionAttributes($value['attributes']);
            }

            if (array_key_exists('listeners', $value)) {
                $this->_setConnectionListeners($value['listeners']);
            }
        }
    }

    /**
     * Set connection attributes
     *
     * @param   array $attributes
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setConnectionAttributes(array $attributes = array())
    {
        $reflect = new ReflectionClass('Doctrine');
        $doctrineConstants = $reflect->getConstants();

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

            $this->_currentConn->setAttribute($attrIdx, $attrVal);
        }
    }

    /**
     * Set connection listeners
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _setConnectionListeners(array $options = array())
    {
        foreach ($options as $alias => $class) {
            if (class_exists($class)) {
                $this->_currentConn->addListener(new $class(), $alias);
            }
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

    /**
     * Build the DSN string
     * 
     * @param   array $dsn
     * @return  string
     * @throws  Zend_Application_Resource_Exception
     */
    protected function _buildDsnFromArray(array $dsn = array())
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
    protected function _buildDsnOptionsFromArray(array $options = array())
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
