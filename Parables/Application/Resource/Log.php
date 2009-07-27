<?php
class Parables_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Log
     */
    protected $_log = null;

    /**
     * Initialize log
     *
     * @return  Zend_Log
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $log = $this->getLog();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtolower($key)) {
                case 'filters': // @todo Filter support
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Unsupported 
                        option.');
                    break;

                case 'formatters': // @todo Formatter support
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Unsupported 
                        option.');
                    break;

                case 'writers':
                    $this->getWriters($value);
                    break;

                default:
                    break;
            }
        }

        return $log;
    }

    /**
     * Retrieve log instance
     *
     * @return  Zend_Log
     */
    public function getLog()
    {
        if (null === $this->_log) {
            $this->_log = new Zend_Log();
        }

        return $this->_log;
    }

    /**
     * Retrieve writers
     *
     * @param   array $writers
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    public function getWriters(array $writers = null)
    {
        foreach ($writers as $key => $value) {
            switch (strtolower($key)) {
                case 'stream':
                    if (!empty($value['path'])) {
                        $path = $value['path'];
                        $mode = 'a';
                        if (!empty($value['mode'])) {
                            $mode = $value['mode'];
                        }

                        $this->_log->addWriter(new 
                            Zend_Log_Writer_Stream($path, $mode));
                    }
                    break;

                case 'db':
                    // @todo Zend_Db writer support
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Unsupported 
                        writer.');
                    break;

                case 'doctrine':
                    if (!empty($value['modelClass'])) {
                        $columnMap = null;
                        if (!empty($value['columnMap'])) {
                            $columnMap = $value['columnMap'];
                        }
                        $modelClass = $value['modelClass'];

                        require_once 'Parables/Log/Writer/Doctrine.php';
                        $this->_log->addWriter(new 
                            Parables_Log_Writer_Doctrine($modelClass, 
                            $columnMap));
                    }
                    break;

                case 'email':
                    // @todo Email writer support
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Unsupported 
                        writer.');
                    break;

                case 'firebug':
                    $this->_log->addWriter(new Zend_Log_Writer_Firebug());
                    break;

                case 'mock':
                    $this->_log->addWriter(new Zend_Log_Writer_Mock());
                    break;

                case 'null':
                    $this->_log->addWriter(new Zend_Log_Writer_Null());
                    break;

                default:
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Invalid 
                        writer.');
            }
        }
    }
}
