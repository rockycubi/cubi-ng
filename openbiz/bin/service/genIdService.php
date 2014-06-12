<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: genIdService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * class genIdService is the plug-in service of generating ID for new record
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class genIdService
{

    /**
     * Initialize excelService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get new Id
     *
     * @param string $idGeneration
     * @param Zend_Db_Adapter_Abstract $conn database connection
     * @param string $dbType database type
     * @param string $table table name
     * @param string $column column name
     * @return mixed
     */
    public function getNewID($idGeneration, $conn, $dbType, $table=null, $column=null)
    {
        try
        {
            if (!$idGeneration || $idGeneration == 'Openbiz')
            {
                $newid = $this->getNewSYSID($conn, $table, true);
            }
            elseif ($idGeneration == 'Identity')
            {
                //$newid = $this->getNewIdentity($conn, $dbtype, $table, $column);
                $newid = $conn->lastInsertId($table, $column);   // user zend_db method
            }
            elseif (strpos($idGeneration, 'Sequence:')===0)
            {
                $seqname = substr($idGeneration, 9);
                //$newid = $this->getNewSequence($conn, $dbtype, $seqname);
                $newid = $conn->nextSequenceId($seqname);   // user zend_db method
            }
            elseif ($idGeneration == 'GUID')
            {
                $newid = $this->getNewGUID($conn, $dbType, $table, $column);
            }
            elseif ($idGeneration == 'UUID')
            {
                $newid = $this->getNewUUID();
            }
            else
            {
                throw new Exception("Error in generating new ID: unsupported generation type.");
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        return $newid;
    }

    /**
     * Get a new SYSID from the id_table. You can get SYSID for a table with prefix and base converting
     *
     * @param Zend_Db_Adapter_Abstract $conn database connection
     * @param string $tableName table name
     * @param boolean $includePrefix
     * @param integer $base to encode
     * @return string
     **/
    protected function getNewSYSID($conn, $tableName, $includePrefix=false, $base=-1)
    {
        $maxRetry = 10;
        // try to update the table idbody column
        for ($try=1; $try <= $maxRetry; $try++)
        {
            $sql = "SELECT * FROM ob_sysids WHERE TABLENAME='$tableName'";
            try
            {
                $rs = $conn->query($sql);
            }
            catch (Exception $e)
            {
                throw new Exception("Error in query: " . $sql . ". " . $e->getMessage());
                return false;
            }
            $row = $rs->fetch();
            unset($rs);
            list($tblname, $prefix, $idbody) = $row;
            if (!$row)
                throw new Exception("Error in generating new system id: '$tableName' is not in ob_sysids table.");
            if ($row)
            {
                if ($idbody == null && $prefix) // idbody is empty, return false
                    throw new Exception("Error in generating new system id: ob_sysids table does not have a valid sequence for '$tableName'.");
            }
            // try to update the table idbody column
            $sql = "UPDATE ob_sysids SET IDBODY=IDBODY+1 WHERE TABLENAME='$tableName' AND IDBODY=$idbody";
            try
            {
                $rs = $conn->query($sql);
            }
            catch (Exception $e)
            {
                throw new Exception("Error in query: " . $sql . ". " . $e->getMessage());
                return false;
            }
            if ($rs->rowCount() > 0)
            {
                $idbody += 1;
                break;
            }
        }
        if ($try <= $maxRetry)
        {
            if ($base>=2 && $base<=36)
                $idbody = dec2base($idbody, $base);
            if ($includePrefix)
                return $prefix."_".$idbody;
            return $idbody;
        }
        else
            throw new Exception("Error in generating new system id: unable to get a valid id.");
        return false;
    }

    /**
     * Get new Identity     *
     * NOTE: ID is generated after insert sql is executed
     *
     * @param Zend_Db_Adapter_Abstract $conn database connection
     * @param string $dbType database type
     * @param string $table table name
     * @param string $column column name
     * @return mixed new Id
     */
    protected function getNewIdentity($conn, $dbType, $table=null, $column=null)
    {
        $dbType = strtoupper($dbType);
        if ($dbType == 'mysql' || $dbType == 'PDO_MYSQL')
        {
            $sql = "select last_insert_id()";
        }
        else if ($dbType == 'mssql' || $dbType == 'PDO_DBLIB')
            $sql = "select @@identity";
        else if ($dbType == 'sybase' || $dbType == 'PDO_DBLIB')
            $sql = "select @@identity";
        else if ($dbType == 'db2' || $dbType == 'PDO_ODBC')
            $sql = "values identity_val_local()";
        else if ($dbType == 'postgresql' || $dbType == 'PDO_PGSQL')
            $sql = "select currval('$table_$column_seq')";
        else
            throw new Exception("Error in generating new identity: unsupported database type.");
        // execute sql to get the id
        $newid = $this->_getIdWithSql($conn, $sql);
        if ($newid === false)
            throw new Exception("Error in generating new identity: unable to get a valid id.");
        return $newid;
    }


    /**
     * Get new sequence
     * NOTE: // ID is generated before executing insert sql
     *
     * @param Zend_Db_Adapter_Abstract $conn database connection
     * @param string $dbType database type
     * @param string $sequenceName
     * @return mixed
     */
    protected function getNewSequence($conn, $dbtype, $sequenceName)
    {
        $dbtype = strtoupper($dbtype);
        if ($dbtype == 'oracle' || $dbtype == 'oci8' || $dbtype == 'PDO_OCI')
            $sql = "select $sequenceName.nextval from dual";
        else if ($dbtype == 'db2' || $dbtype == 'PDO_ODBC')
            $sql = "values nextval for $sequenceName";
        else if ($dbtype == 'postgresql' || $dbtype == 'PDO_PGSQL')
            $sql = "select nextval('$sequenceName')";
        else if ($dbtype == 'informix' || $dbtype == 'PDO_INFORMIX')
            "select $sequenceName.nextval from systables where tabid=1";
        else
            throw new Exception("Error in generating new sequence: unsupported database type.");
        // execute sql to get the id
        $newId = $this->_getIdWithSql($conn, $sql);
        if ($newId === false)
            throw new Exception("Error in generating new sequence: unable to get a valid id.");
        return $newId;
    }

    /**
     * Get new GUID
     * ID is generated before executing insert sql
     *
     * @param Zend_Db_Adapter_Abstract $conn database connection
     * @param string $dbType database type
     * @param string $table table name
     * @param string $column column name
     * @return mixed new Id
     */
    protected function getNewGUID($conn, $dbType, $table=null, $column=null)
    {
        $dbType = strtoupper($dbType);
        if ($dbType == 'mysql' || $dbType == 'PDO_DBLIB')
            $sql = "select uuid()";
        else if ($dbType == 'oracle' || $dbType == 'oci8' || $dbType == 'PDO_OCI')
            $sql = "select rawtohex(sys_guid()) from dual";
        else if ($dbType == 'msql' || $dbType == 'PDO_MYSQL')
            $sql = "select newid()";
        else
            throw new Exception("Error in generating new GUID: unsupported database type.");
        // execute sql to get the id
        $newId = $this->_getIdWithSql($conn, $sql);
        if ($newId === false)
            throw new Exception("Error in generating new GUID: unable to get a valid id.");
        return $newId;
    }

    /**
     * Get new UUID
     * ID is generated before executing insert sql
     *
     * @return string the unique identifier, as a string.
     */
    protected function getNewUUID()
    {
        return uniqid();
    }

    /**
     * Get ID with SQL
     *
     * @param Zend_Db_Adapter_Abstract $conn
     * @param string $sql
     * @return mixed
     */
    private function _getIdWithSql($conn, $sql)
    {
        try
        {
            $rs = $conn->query($sql);
            BizSystem::log(LOG_DEBUG, "DATAOBJ", "Get New Id: $sql");
        }
        catch (Exception $e)
        {
            $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
            return false;
        }

        if (($row = $rs->fetch()) != null)
        {
            //print_r($row);
            return $row[0];
        }
        return false;
    }
}

?>