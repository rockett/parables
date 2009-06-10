<?php
class My_Doctrine_Auth_Adapter_Table implements Zend_Auth_Adapter_Interface
{
    /**
     * @var Doctrine_Connection_Common
     */
    protected $_conn = null;

    /**
     * @var string
     */
    protected $_tableName = null;

    /**
     * @var string
     */
    protected $_identityColumn = null;

    /**
     * @var string
     */
    protected $_credentialColumn = null;

    /**
     * @var string
     */
    protected $_identity = null;

    /**
     * @var string
     */
    protected $_credential = null;

    /**
     * @var string
     */
    protected $_credentialTreatment = null;

    /**
     * @var array
     */
    protected $_authenticateResultInfo = null;
    
    /**
     * @var array
     */
    protected $_resultRow = null;

    /**
     * Sets configuration options
     *
     * @param  Zend_Db_Adapter_Abstract $zendDb
     * @param  string $tableName
     * @param  string $identityColumn
     * @param  string $credentialColumn
     * @param  string $credentialTreatment
     * @return void
     */
    public function __construct(Doctrine_Connection_Common $conn = null, $tableName = null, $identityColumn = null, $credentialColumn = null, $credentialTreatment = null)
    {
        if (null !== $conn) {
            $this->setConnection($conn);
        }

        if (null !== $tableName) {
            $this->setTableName($tableName);
        }

        if (null !== $identityColumn) {
            $this->setIdentityColumn($identityColumn);
        }

        if (null !== $credentialColumn) {
            $this->setCredentialColumn($credentialColumn);
        }

        if (null !== $credentialTreatment) {
            $this->setCredentialTreatment($credentialTreatment);
        }
    }
    
    /**
     * Set the connection to the database
     *
     * @param  Doctrine_Connection_Common $conn
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setConnection(Doctrine_Connection_Common $conn)
    {
        $this->_conn = $conn;
        return $this;
    }
    
    /**
     * Get the connection to the database
     * 
     * @return Doctrine_Connection_Common|null
     */
    public function getConnection()
    {
        if ((null === $this->_conn) && (null !== $this->_tableName)) {
            $this->_conn = Doctrine::getConnectionByTableName($this->_tableName);
        }
        
        return $this->_conn;
    }

    /**
     * Set the table name to be used in the select query
     *
     * @param  string $tableName
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;
        return $this;
    }

    /**
     * Set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    /**
     * Set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * Allows the developer to pass a parameterized string that is
     * used to transform or treat the input credential data
     * 
     * In many cases, passwords and other sensitive data are encrypted, 
     * hashed, encoded, obscured, or otherwise treated through some function 
     * or algorithm. By specifying a parameterized treatment string with this 
     * method, a developer may apply arbitrary SQL upon input credential data.
     *
     * @param  string $treatment
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setCredentialTreatment($treatment)
    {
        $this->_credentialTreatment = $treatment;
        return $this;
    }

    /**
     * Set the value to be used as the identity
     *
     * @param  string $value
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * Set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' 
     * or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Zend_Auth_Adapter_Doctrine_Table Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getResultRowObject($returnColumns = null, $omitColumns = null)
    {
        if (!$this->_resultRow) {
            return false;
        }
        
        $returnObject = new stdClass();

        if (null !== $returnColumns) {
            $availableColumns = array_keys($this->_resultRow);
            foreach ( (array) $returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                }
            }

            return $returnObject;
        } elseif (null !== $omitColumns) {
            $omitColumns = (array) $omitColumns;
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }

            return $returnObject;
        } else {
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }

            return $returnObject;
        }
    }

    /**
     * Defined by Zend_Auth_Adapter_Interface.  This method is called to 
     * attempt an authentication. Previous to this call, this adapter would 
     * have already been configured with all necessary information to 
     * successfully connect to a database table and attempt to find a record 
     * matching the provided identity
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        $dbSelect = $this->_authenticateCreateSelect();
        $resultIdentities = $this->_authenticateQuerySelect($dbSelect);
        
        if (($authResult = $this->_authenticateValidateResultset($resultIdentities)) instanceof Zend_Auth_Result) {
            return $authResult;
        }
        
        $authResult = $this->_authenticateValidateResult(array_shift($resultIdentities));
        return $authResult;
    }

    /**
     * This method abstracts the steps involved with making sure that this 
     * adapter was indeed setup properly with all required peices of 
     * information
     *
     * @throws Zend_Auth_Adapter_Exception
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;
        
        if ($this->getConnection() === null) {
            $exception = 'A database connection was not set, nor could one be created.';
        } elseif ($this->_tableName == '') {
            $exception = 'A table must be supplied for the Zend_Auth_Adapter_Doctrine_Table authentication adapter.';
        } elseif ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the Zend_Auth_Adapter_Doctrine_Table authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the Zend_Auth_Adapter_Doctrine_Table authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Zend_Auth_Adapter_Doctrine_Table.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Zend_Auth_Adapter_Doctrine_Table.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
        
        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array(),
        );
            
        return true;
    }

    /**
     * Create the query
     *
     * @return Doctrine_Query
     */
    protected function _authenticateCreateSelect()
    {
        if (empty($this->_credentialTreatment) || (false === strpos($this->_credentialTreatment, "?"))) {
            $this->_credentialTreatment = '?';
        }
        
        $q = Doctrine_Query::create($this->getConnection())
            ->from($this->_tableName)
            ->select('*, ('.$this->_credentialColumn.' = '.str_replace('?', $this->getConnection()->quote($this->_credential), $this->_credentialTreatment).') AS zend_auth_credential_match')
            ->addWhere($this->_identityColumn .' = ?', $this->_identity);

        return $q;
    }

    /**
     * Perform the query
     *
     * @param Doctrine_Query $q
     * @return array
     * @throws Zend_Auth_Adapter_Exception
     */
    protected function _authenticateQuerySelect(Doctrine_Query $dbSelect)
    {
        try {
            $resultIdentities = $q->execute()->toArray();
        } catch (Exception $e) {
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The supplied parameters to Zend_Auth_Adapter_Doctrine_Record failed to produce a valid sql statement, please check table and column names for validity.');
        }

        return $resultIdentities;
    }

    /**
     * This method attempts to make certian that only one record was returned 
     * in the result set
     *
     * @param array $resultIdentities
     * @return true|Zend_Auth_Result
     */
    protected function _authenticateValidateResultSet(array $resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }

        return true;
    }

    /**
     * This method attempts to validate that the record in the result set is 
     * indeed a record that matched the identity provided to this adapter
     *
     * @param array $resultIdentity
     * @return Zend_Auth_Result
     */
    protected function _authenticateValidateResult($resultIdentity)
    {
        if ($resultIdentity['zend_auth_credential_match'] != '1') {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }

        unset($resultIdentity['zend_auth_credential_match']);
        $this->_resultRow = $resultIdentity;

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }
    
    /**
     * This method creates a Zend_Auth_Result object from the information that 
     * has been collected during the authenticate() attempt
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
        );
    }
}
