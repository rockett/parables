<?php
class My_Doctrine_Log_Writer_Table extends Zend_Log_Writer_Abstract
{
    /**
     * @var string
     */
    private $_class = null;

    /**
     * @var null|array
     */
    private $_columnMap = null;

    /**
     * Constructor
     *
     * @param string $class
     * @param array $columnMap
     * @return void
     */
    public function __construct($class, $columnMap = null)
    {
        $this->_class = $class;
        $this->_columnMap = $columnMap;
    }

    /**
     * Disable formatting
     *
     * @param mixed $formatter
     * @return void
     * @throws Zend_Log_Exception
     */
    public function setFormatter($formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class() . ' does not support formatting');
    }

    /**
     * Write a message to the log
     *
     * @param array $event
     * @return void
     */
    protected function _write($event)
    {
        $data = array();
        if (null === $this->_columnMap) {
            $data = $event;
        } else {
            foreach ($this->_columnMap as $columnName => $fieldKey) {
                $data[$columnName] = $event[$fieldKey];
            }
        }

        $entry = new $this->_class();
        $entry->fromArray($data);
        $entry->save();
    }
}
