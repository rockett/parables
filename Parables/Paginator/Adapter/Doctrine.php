<?php
class Parables_Paginator_Adapter_Doctrine implements Zend_Paginator_Adapter_Interface
{
    const ROW_COUNT_COLUMN = 'zend_paginator_row_count';

    /**
     * @var Doctrine_Query
     */
    protected $_query = null;

    /**
     * @var integer
     */
    protected $_rowCount = null;

    /**
     * Constructor
     *
     * @param Doctrine_Query $query
     * @return void
     */
    public function __construct(Doctrine_Query $query)
    {
        $this->_query = $query;
    }

    /**
     * Set the total row count, either directly or through a supplied query
     *
     * @param  Doctrine_Query|integer $totalRowCount
     * @return Zend_Paginator_Adapter_Doctrine
     * @throws Zend_Paginator_Exception
     */
    public function setRowCount($rowCount)
    {
        if (is_integer($rowCount)) {
            $this->_rowCount = $rowCount;
        } elseif ($rowCount instanceof Doctrine_Query) {
            if (false === strpos($rowCount->getSql(), self::ROW_COUNT_COLUMN)) {
                require_once 'Zend/Paginator/Exception.php';
                throw new Zend_Paginator_Exception('Row count column not found');
            }

            $result = $rowCount->fetchOne()->toArray();
            $this->_rowCount = count($result) > 0 ? $result[self::ROW_COUNT_COLUMN] : 0;
        } else {
            require_once 'Zend/Paginator/Exception.php';
            throw new Zend_Paginator_Exception('Invalid row count');
        }

        return $this;
    }

    /**
     * Returns an array of items for a page
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->_query->limit($itemCountPerPage)->offset($offset);
        return $this->_query->execute();
    }

    /**
     * Returns the total number of rows in the result set
     *
     * @return integer
     */
    public function count()
    {
        if (null === $this->_rowCount) {
            $rowCount = $this->_query->count();
            $this->setRowCount($rowCount);
        }

        return $this->_rowCount;
    }
}
