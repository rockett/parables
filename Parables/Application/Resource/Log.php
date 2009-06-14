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
     */
    public function init()
    {
        $log = $this->getLog();

        foreach ($this->getOptions() as $key => $values) {
            switch (strtolower($key)) {
                case 'filters':
                    break;

                case 'formatters':
                    break;

                case 'writers':
                    if (!empty($values)) {
                        $writers = $this->getWriters($values);
                        foreach ($writers as $writer) {
                            $log->addWriter($writer);
                        }
                    }
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
     * @param   array $options
     * @return  array
     */
    public function getWriters(array $options = null)
    {
        $writers = array();

        foreach ($options as $writer => $writerOptions) {
            switch ($writer) {
                case 'stream':
                    if (!empty($writerOptions['path'])) {
                        $path = (string) $writerOptions['path'];
                        $mode = 'a';

                        if (!empty($writerOptions['mode'])) {
                            $mode = (string) $writerOptions['mode'];
                        }

                        $writers[] = new Zend_Log_Writer_Stream($path, $mode);
                    }
                    break;

                case 'db':
                    break;

                case 'doctrine':
                    if (!empty($writerOptions['class'])) {
                        $class = (string) $writerOptions['class'];

                        $columnMap = null;
                        if (!empty($writerOptions['columnMap'])) {
                            $columnMap = (array) $writerOptions['columnMap'];
                        }

                        $writers[] = new Parables_Log_Writer_Doctrine($class, $columnMap);
                    }
                    break;

                case 'email':
                    break;

                case 'firebug':
                    $writers[] = new Zend_Log_Writer_Firebug();
                    break;

                case 'mock':
                    $writers[] = new Zend_Log_Writer_Mock();
                    break;

                case 'null':
                    $writers[] = new Zend_Log_Writer_Null();
                    break;

                default:
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception('Invalid log writer.');
            }
        }

        return $writers;
    }
}
