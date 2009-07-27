<?php
class Parables_Log_Writer_Doctrine extends Zend_Log_Writer_Abstract
{
    /**
     * @var null|string
     */
    private $_modelClass = null;

    /**
     * @var null|array
     */
    private $_columnMap = null;

    /**
     * Constructor
     *
     * @param   string $modelClass
     * @param   array $columnMap
     * @return  void
     * @throws  Zend_Log_Exception
     */
    public function __construct($modelClass, $columnMap = null)
    {
        if ((!is_string($modelClass)) || (!class_exists($modelClass))) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Invalid model class.');
        }
        $this->_modelClass = $modelClass;
        $this->_columnMap = $columnMap;
    }

    /**
     * Disable formatting
     *
     * @param   mixed $formatter
     * @return  void
     * @throws  Zend_Log_Exception
     */
    public function setFormatter($formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class() . ' does not support formatting');
    }

    /**
     * Write a message to the log
     *
     * @param   array $event
     * @return  void
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

        $entry = new $this->_modelClass();
        $entry->fromArray($data);
        $entry->save();
    }
}
