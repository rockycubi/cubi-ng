<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: BizDataObj.php 4108 2011-05-08 06:01:30Z jixian2003 $
 */

//include_once(OPENBIZ_BIN.'data/BizDataObj_Lite.php');

/**
 * BizDataObj class is the base class of all data object classes
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class BizDataObj extends BizDataObj_Lite
{
    public $useTransaction = true;
    /**
     * Initialize BizDataObj_Abstract with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);
    }

    /**
     * Validate user input data and trigger error message and adjust BizField if invalid.
     *
     * @return boolean
     * @todo: throw BDOException
     **/
    public function validateInput()
    {
        $this->m_ErrorFields = array();
        foreach($this->m_BizRecord->m_InputFields as $fld)
        {

            /* @var $bizField BizField */
            $bizField = $this->m_BizRecord->get($fld);
            if($bizField->m_Encrypted=="Y"){
	            if ($bizField->checkRequired() == true &&
	                    ($bizField->m_Value===null || $bizField->m_Value === ""))
	            {
	                $this->m_ErrorMessage = $this->getMessage("DATA_FIELD_REQUIRED",array($fld));
	                $this->m_ErrorFields[$bizField->m_Name] = $this->m_ErrorMessage;
	            }
            	continue;
            }
            if ($bizField->checkRequired() == true &&
                    ($bizField->m_Value===null || $bizField->m_Value === ""))
            {
                $this->m_ErrorMessage = $this->getMessage("DATA_FIELD_REQUIRED",array($fld));
                $this->m_ErrorFields[$bizField->m_Name] = $this->m_ErrorMessage;
            }
            elseif ($bizField->m_Value!==null && $bizField->checkValueType() == false)
            {
                $this->m_ErrorMessage = $this->getMessage("DATA_FIELD_INCORRECT_TYPE", array($fld, $bizField->m_Type));
                $this->m_ErrorFields[$bizField->m_Name] = $this->m_ErrorMessage;
            }
            elseif ($bizField->m_Value!==null && $bizField->Validate() == false)
            {

                /* @var $validateService validateService */
                $validateService = BizSystem::getService(VALIDATE_SERVICE);
                $this->m_ErrorMessage = $validateService->getErrorMessage($bizField->m_Validator, $bizField->m_Name);
                if ($this->m_ErrorMessage == false)
                { //Couldn't get a clear error message so let's try this
                    $this->m_ErrorMessage = $this->getMessage("DATA_FIELD_INVALID_INPUT",array($fld,$value,$bizField->m_Validator));                //
                }
                $this->m_ErrorFields[$bizField->m_Name] = $this->m_ErrorMessage;
            }
        }
        if (count($this->m_ErrorFields)>0)
        {
            //print_r($this->m_ErrorFields);
            throw new ValidationException($this->m_ErrorFields);
            return false;
        }

        // validate uniqueness
        if ($this->validateUniqueness() == false)
            return false;

        return true;
    }

    /**
     * Validate uniqueness
     * Uniqueness = "fld1,fld2;fld3,fld4;..."
     *
     * @return boolean
     */
    protected function validateUniqueness()
    {
        if (!$this->m_Uniqueness)
            return true;
        $groupList = explode(";",$this->m_Uniqueness);
        foreach ($groupList as $group)
        {
            $searchRule = "";
            $needCheck = true;
            $fields = explode(",",$group);
            foreach ($fields as $fld)
            {
                $bizField = $this->m_BizRecord->get($fld);
                if ($bizField->m_Value===null || $bizField->m_Value === "" || $bizField->m_Value==$bizField->m_OldValue)
                {
                    $needCheck = false;
                    break;
                }
                if ($searchRule == "")
                    $searchRule = "[".$bizField->m_Name."]='".addslashes($bizField->m_Value)."'";
                else
                    $searchRule .= " AND [".$bizField->m_Name."]='".addslashes($bizField->m_Value)."'";
            }
            if ($needCheck)
            {
                $recordList = $this->directFetch($searchRule, 1);                
                if ($recordList->count()>0)
                {
                    $this->m_ErrorMessage = $this->getMessage("DATA_NOT_UNIQUE",array($group));
                    foreach ($fields as $fld)
                    {
                        $this->m_ErrorFields[$fld] = $this->m_ErrorMessage;
                    }
                }
            }
        }
        if (count($this->m_ErrorFields)>0)
        {
            throw new ValidationException($this->m_ErrorFields);
            return false;
        }
        return true;
    }

    /**
     * Check if the current record can be updated
     *
     * @return boolean
     */
    public function canUpdateRecord($record = null)
    {
    	
    	if($this->m_DataPermControl=='Y')
        {
	        $svcObj = BizSystem::GetService(DATAPERM_SERVICE);
	        if(!$record)
	        {
	        	$record = $this->getActiveRecord();
	        }
	        $result = $svcObj->checkDataPerm($record,2,$this);
	        if($result == false)
	        {
	        	return false;
	        }
        }
    	
        $result = $this->canUpdateRecordCondition();
        return $result;
    }
    
    public function canUpdateRecordCondition()
    {    	
        if ($this->m_UpdateCondition)
        {
            //return Expression::evaluateExpression($this->m_UpdateCondition,$this);
            return $this->allowAccess($this->m_UpdateCondition);
        }
        return true;
    }
    /**
     * Check if the current record can be deleted
     *
     * @return boolean
     */
    public function canDeleteRecord($record = null)
    {
    	if($this->m_DataPermControl=='Y')
        {
	        $svcObj = BizSystem::GetService(DATAPERM_SERVICE);
	        if(!$record)
	        {
	        	$record = $this->getActiveRecord();
	        }
	        $result = $svcObj->checkDataPerm($record,3,$this);
	        if($result == false)
	        {
	        	return false;
	        }
        }
    	
        $result = $this->canDeleteRecordCondition();
        return $result;
    }

    public function canDeleteRecordCondition()
    {    	
        if ($this->m_DeleteCondition)
        {
            // return Expression::evaluateExpression($this->m_DeleteCondition,$this);
            return $this->allowAccess($this->m_DeleteCondition);
        }
        return true;
    }    
    /**
     * Update record using given input record array
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @param array $oldRecord - associated array who is the old record field name / value pairs
     * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
     **/
    public function updateRecord($recArr, $oldRecord=null)
    {
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('record'=>$recArr,'old_record'=>$oldRecord));
		if (!$this->canUpdateRecord($oldRecord))
        {
            $this->m_ErrorMessage = BizSystem::getMessage("DATA_NO_PERMISSION_UPDATE",$this->m_Name);
            throw new BDOException($this->m_ErrorMessage);
            return false;
        }

        if (!$oldRecord)
            $oldRecord = $this->getActiveRecord();

        if (!$recArr["Id"])
            $recArr["Id"] = $this->getFieldValue("Id");

        // save the old values
        $this->m_BizRecord->saveOldRecord($oldRecord);
        // set the new values
        $this->m_BizRecord->setInputRecord($recArr);

        if (!$this->validateInput()) return false;

        $sql = $this->getSQLHelper()->buildUpdateSQL($this);

        if ($sql)
        {
            $db = $this->getDBConnection("WRITE");
            if ($this->useTransaction)
                $db->beginTransaction();

            try
            {
                $this->cascadeUpdate(); // cascade update
                
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Update Sql = $sql");
                $db->query($sql);

                if ($this->useTransaction)
                    $db->commit();
            }
            catch (Exception $e)
            {
                if ($this->useTransaction)
                    $db->rollBack();

                if ($e instanceof BDOException)
                    throw $e;
                else {
                    BizSystem::log(LOG_ERR, "DATAOBJ", "Query error : ".$e->getMessage());
                    $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
                    throw new BDOException($this->m_ErrorMessage);
                }
                return false;
            }

            $this->cleanCache(); //clean cached data
            $this->_postUpdateLobFields($recArr);
			$this->m_RecordId = $recArr["Id"];
            $this->m_CurrentRecord = null; 
            $this->_postUpdateRecord($recArr);
        } 
		else {
			$this->m_RecordId = $recArr["Id"];
			$this->m_CurrentRecord = $recArr; 
		}
		$this->events()->trigger(__FUNCTION__ . '.post', $this, array('record'=>$recArr,'old_record'=>$oldRecord));
        return true;
    }

    public function updateRecords($setValue, $condition = null)
    {
        if (!$this->canUpdateRecordCondition())
        {
            $this->m_ErrorMessage = BizSystem::getMessage("DATA_NO_PERMISSION_UPDATE",$this->m_Name);
            return false;
        }
		/*当$setValue是数组时转成[field]=value格式*/
		if(is_array($setValue)){
			$setValue_srt='';
			foreach($setValue as $key=>$value){
				if($value!=''){
					$setValue_srt.=$setValue_srt?",[$key]='$value'":"[$key]='$value'";
				}
			}
			$setValue=$setValue_srt; 
		}	
        $sql = $this->getSQLHelper()->buildUpdateSQLwithCondition($this,$setValue ,$condition);
        $db = $this->getDBConnection("WRITE");
 
        try
        {
            if($sql)
            { 	// delete joint table first then delete main table's data'
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Delete Sql = $sql"); 
                $db->query($sql);
            }
        }
        catch (Exception $e)
        {
            BizSystem::log(LOG_ERR, "DATAOBJ", "Query error : ".$e->getMessage());
            $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
            throw new BDOException($this->m_ErrorMessage);
            return false;
        }

        //clean cached data
        $this->cleanCache();
        return true;
    }
    /**
     * Check if the field is blob/clob type.
     * In the lob case, update (lob value only)
     *
     * @param array $recArr
     * @return mixed boolean or null
     */
    private function _postUpdateLobFields(&$recArr)
    {
        $searchRule = $this->m_BizRecord->getKeySearchRule(false, true);
        foreach ($this->m_BizRecord as $field)
        {
            if (isset($recArr[$field->m_Name]) && $field->isLobField() && $field->m_Column != "")
            {
                $db = $this->getDBConnection("WRITE");
                $sql = "UPDATE " . $this->m_MainTable . " SET " . $field->m_Column . "=? WHERE $searchRule";
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Update lob Sql = $sql");
                $stmt = $db->prepare($sql);

                $fp = fopen($recArr[$field->m_Name], 'rb');
                $stmt->bindParam(1, $fp, PDO::PARAM_LOB);

                try
                {
                    $stmt->execute();
                }
                catch (Exception $e)
                {
                    $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
                    BizSystem::log(LOG_ERR, "DATAOBJ", "Update lob error = $sql");
                    fclose($fp);
                    throw new BDOException($this->m_ErrorMessage);
                    return null;
                }

                fclose($fp);
                return true;
            }
        }
        return true;
    }

    /**
     * Action after update record is done
     *
     * @param array $recArr
     * @return void
     */
    private function _postUpdateRecord($recArr)
    {
        // run DO trigger
        $this->_runDOTrigger("UPDATE");
    }

    /**
     * Create an empty new record
     *
     * @return array - empty record array with default values
     **/
    public function newRecord()
    {
        $recArr = $this->m_BizRecord->getEmptyRecordArr();

        // if association is 1-M, set the field (pointing to the column) value as the FieldRefVal
        if ($this->m_Association["Relationship"] == "1-M")
        {
            foreach ($this->m_BizRecord as $field)
            {
                if ($field->m_Column == $this->m_Association["Column"] && !$field->m_Join)
                {
                    $recArr[$field->m_Name] = $this->m_Association["FieldRefVal"];
                    break;
                }
            }
        }

        return $recArr;
    }

    /**
     * Generate Id according to the IdGeneration attribute
     *
     * @param boolean $isBeforeInsert
     * @param string $tableName
     * @param string $idCloumnName
     * @return long|string|boolean
     */
    protected function generateId($isBeforeInsert=true, $tableName=null, $idCloumnName=null)
    {
        // Identity type id is generated after insert is done.
        // If this method is called before insert, return null.
        if ($isBeforeInsert && $this->m_IdGeneration == 'Identity')
            return null;

        if (!$isBeforeInsert && $this->m_IdGeneration != 'Identity')
        {
            $this->m_ErrorMessage = BizSystem::getMessage( "DATA_UNABLE_GET_ID",$this->m_Name);
            return false;
        }

        /* @var $genIdService genIdService */
        $genIdService = BizSystem::getService(GENID_SERVICE);
        if($this->m_db){
        	$db = $this->m_db;
        }else{
        	$db = $this->getDBConnection("READ");
        }
        $dbInfo = BizSystem::Configuration()->getDatabaseInfo($this->m_Database);
        $dbType = $dbInfo["Driver"];
        $table = $tableName ? $tableName : $this->m_MainTable;
        $column = $idCloumnName ? $idCloumnName : $this->getField("Id")->m_Column;

        try
        {
            $newId = $genIdService->getNewID($this->m_IdGeneration, $db, $dbType, $table, $column);
        }
        catch (Exception $e)
        {
            $this->m_ErrorMessage = $e->getMessage();
            return false;
        }
        return $newId;
    }

    /**
     * Insert record using given input record array
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @return boolean - if return false, the caller can call getErrorMessage to get the error.
     **/
    public function insertRecord($recArr)
    {
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('record',$recArr));
		if ( $this->_isNeedGenerateId($recArr) )
            $recArr["Id"] = $this->generateId();    // for certain cases, id is generated before insert

        $this->m_BizRecord->setInputRecord($recArr);

        if (!$this->validateInput()) return false;

        $db = $this->getDBConnection("WRITE");

        try
        {
            $sql = $this->getSQLHelper()->buildInsertSQL($this, $joinValues);
            if($sql)
            {
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Insert Sql = $sql");
                $db->query($sql, $bindValues);                
            }
            //$mainId = $db->lastInsertId();
            if ( $this->_isNeedGenerateId($recArr) )
            {
            	$this->m_db = $db; //compatiable for CLI mode and also speed up of it running
                $mainId = $this->generateId(false);
                $recArr["Id"] = $mainId;
            }
            BizSystem::log(LOG_DEBUG, "DATAOBJ", "New record Id is ".$recArr["Id"]);
        }
        catch (Exception $e)
        {
            BizSystem::log(LOG_ERR, "DATAOBJ", "Query Error : " . $e->getMessage());
            $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
            throw new BDOException($this->m_ErrorMessage);
            return null;
        }

        $this->m_BizRecord->setInputRecord($recArr);

        if ($this->_postUpdateLobFields($recArr) === false)
        {
            $this->m_ErrorMessage = $db->ErrorMsg();
            return false;
        }

        $this->cleanCache();

        $this->m_RecordId = $recArr["Id"];
        $this->m_CurrentRecord = null;

        $this->_postInsertRecord($recArr);
		$this->events()->trigger(__FUNCTION__ . '.post', $this, array('record',$recArr));
        return $recArr["Id"];
    }

    /**
     * Action after insert record is done
     *
     * @param array $recArr
     */
    private function _postInsertRecord($recArr)
    {
        // do trigger
        $this->_runDOTrigger("INSERT");
    }

    /**
     * Delete current record or delete the given input record
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
     **/
    public function deleteRecord($recArr)
    {    	
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('record',$recArr));
		if (!$this->canDeleteRecord())
        {            
            $this->m_ErrorMessage = BizSystem::getMessage("DATA_NO_PERMISSION_DELETE",$this->m_Name);
            throw new BDOException($this->m_ErrorMessage);
            return false;
        }

        if ($recArr) {
            $delrec = $recArr;
        } else {
            $delrec = $this->getActiveRecord();
        }
        
        $this->m_BizRecord->setInputRecord($delrec);
        
        $sql = $this->getSQLHelper()->buildDeleteSQL($this);
        if ($sql)
        {
            $db = $this->getDBConnection("WRITE");
            $db->beginTransaction();
            try
            {
                $this->cascadeDelete(); // cascade delete
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Delete Sql = $sql");
                $db->query($sql);
                $db->commit();
                $this->m_BizRecord->saveOldRecord($delrec); // save old record only if delete success
            }
            catch (Exception $e)
            {
                $db->rollBack();
                if ($e instanceof BDOException)
                    throw $e;
                else {
                    BizSystem::log(LOG_ERR, "DATAOBJ", "Query error : ".$e->getMessage());
                    $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
                    throw new BDOException($this->m_ErrorMessage);
                }
                return false;
            }
        }

        //clean cached data
        $this->cleanCache();

        $this->_postDeleteRecord($this->m_BizRecord->getKeyValue());
		$this->events()->trigger(__FUNCTION__ . '.pre', $this, array('record',$recArr));
        return true;
    }

    public function deleteRecords($condition = null)
    {
        if (!$this->canDeleteRecordCondition())
        {
            throw new BDOException( BizSystem::getMessage("DATA_NO_PERMISSION_DELETE",$this->m_Name) );
            return false;
        }

        $sql = $this->getSQLHelper()->buildDeleteSQLwithCondition($this,$condition);
        $db = $this->getDBConnection("WRITE");

        try
        {
            if($sql)
            { 	// delete joint table first then delete main table's data'
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Delete Sql = $sql");
                $db->query($sql);
            }
        }
        catch (Exception $e)
        {
            BizSystem::log(LOG_ERR, "DATAOBJ", "Query error : ".$e->getMessage());
            $db->rollBack(); //if one failed then rollback all
            $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
            throw new BDOException($this->m_ErrorMessage);
            return false;
        }

        //clean cached data
        $this->cleanCache();
        return true;
    }

    /**
     * Action after delete record is done
     *
     * @return void
     */
    private function _postDeleteRecord()
    {
        // do trigger
        $this->_runDOTrigger("DELETE");
    }
    
    // $action: Delete, Update
    protected function processCascadeAction($objRef, $cascadeType)
    {
        if (($cascadeType=='Delete' && $objRef->m_OnDelete)
            || ($cascadeType=='Update' && $objRef->m_OnUpdate))
        {
            if ($objRef->m_Relationship == "1-M" || $objRef->m_Relationship == "1-1") {
                $table = $objRef->m_Table;
                $column = $objRef->m_Column;
                $column2 = $objRef->m_Column2;
            }
            else if ($objRef->m_Relationship == "M-M" || $objRef->m_Relationship == "Self-Self") {
                $table = $objRef->m_XTable;
                $column = $objRef->m_XColumn1;
            }
            $refField = $this->getField($objRef->m_FieldRef);
            $fieldVal = $this->getFieldValue($objRef->m_FieldRef);
            
            $fieldVal2 = $this->getFieldValue($objRef->m_FieldRef2);
            if (!$fieldVal) return;      
            if($column2){     
            	if (!$fieldVal2) return;
            }

            $db = $this->getDBConnection("WRITE");
            // get the cascade action sql
            if ($cascadeType=='Delete') {
                if ($objRef->m_OnDelete == "Cascade") {
                    $sql = "DELETE FROM ".$table." WHERE ".$column."='".$fieldVal."'";
                    if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }
                }
                else if ($objRef->m_OnDelete == "SetNull") {
                    $sql = "UPDATE ".$table." SET $column=null WHERE ".$column."='".$fieldVal."'";
                	if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }
                }
                else if ($objRef->m_OnDelete == "Restrict") {
                    // check if objRef has records
                    $refObj = $this->getRefObject($objRef->m_Name);  
                	$sql = "`$column`='".$refField->m_Value."'";
                    if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }                  
                    if (count($refObj->directFetch($sql,1)) == 1) {
                        throw new BDOException($this->getMessage("DATA_UNABLE_DEL_REC_CASCADE",array($objRef->m_Name)));
                    }
                    return;
                }
            }
            else if ($cascadeType=='Update') {
                // check if the column value is actually changed
                if ($refField->m_OldValue == $refField->m_Value) return;
                
                if ($objRef->m_OnUpdate == "Cascade") {
                    $sql = "UPDATE ".$table." SET $column='".$refField->m_Value."' WHERE ".$column."='".$refField->m_OldValue."'";
               	 	if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }
                }
                else if ($objRef->m_OnUpdate == "SetNull") {
                    $sql = "UPDATE ".$table." SET $column=null WHERE ".$column."='".$refField->m_OldValue."'";
                	if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }
                }
                else if ($objRef->m_OnUpdate == "Restrict") {
                    // check if objRef has records
                    $refObj = BizSystem::getObject($objRef->m_Name);
					$sql = "[".$objRef->m_FieldRef."]='".$refField->m_OldValue."'";
                    if($column2 && $fieldVal2){
                    	$sql .= " AND ".$column2."='".$fieldVal2."'"; 	
                    }
                    if (count($refObj->directFetch($sql,1)) == 1) {
                        throw new BDOException($this->getMessage("DATA_UNABLE_UPD_REC_CASCADE",array($objRef->m_Name)));
                    }
                    return;
                }
            }
            try {
                BizSystem::log(LOG_DEBUG, "DATAOBJ", "Cascade $cascadeType Sql = $sql");
                $db->query($sql);
            }
            catch (Exception $e) {
                BizSystem::log(LOG_Err, "DATAOBJ", "Cascade $cascadeType Error: ".$e->getMessage());
                $this->m_ErrorMessage = $this->getMessage("DATA_ERROR_QUERY").": ".$sql.". ".$e->getMessage();
                throw new BDOException($this->m_ErrorMessage);
            }
        }
    }
    
    /**
     * Run cascade delete
     * @return void
     */
    protected function cascadeDelete()
    {
        foreach ($this->m_ObjReferences as $objRef) {
            $this->processCascadeAction($objRef, "Delete");
        }
    }
    
    /**
     * Run cascade update
     * @return void
     */
    protected function cascadeUpdate()
    {
        foreach ($this->m_ObjReferences as $objRef) {
            $this->processCascadeAction($objRef, "Update");
        }
    }

    /**
     * Get auditable fields
     *
     * @return array list of {@link BizField} objects who are auditable
     */
    public function getOnAuditFields()
    {
        $fieldList = array();
        foreach ($this->m_BizRecord as $field)
        {
            if ($field->m_OnAudit)
                $fieldList[] = $field;
        }
        return $fieldList;
    }

    /**
     * Run DataObject trigger
     *
     * @param string $triggerType type of the trigger
     */
    private function _runDOTrigger($triggerType)
    {
        // locate the trigger metadata file BOName_Trigger.xml
        $triggerServiceName = $this->m_Name."_Trigger";
        $xmlFile = BizSystem::GetXmlFileWithPath ($triggerServiceName);
        if (!$xmlFile) return;

        $triggerService = BizSystem::getObject($triggerServiceName);
        if ($triggerService == null)
            return;
        // invoke trigger service ExecuteTrigger($triggerType, $currentRecord)

        $triggerService->execute($this, $triggerType);

    }

    /**
     * Get all fields that belong to the same join of the input field
     *
     * @param BizDataObj $joinDataObj the join data object
     * @return array joined fields array
     */
    public function getJoinFields($joinDataObj)
    {
        // get the maintable of the joindataobj
        $joinTable = $joinDataObj->m_MainTable;
        $returnRecord = array();

        // find the proper join according to the maintable
        foreach ($this->m_TableJoins as $tableJoin)
        {
            if ($tableJoin->m_Table == $joinTable)
            {
                // populate the column-fieldvalue to columnRef-fieldvalue
                // get the field mapping to the column, then get the field value
                $joinFieldName = $joinDataObj->m_BizRecord->getFieldByColumn($tableJoin->m_Column); // joined-main table

                if (!$joinFieldName) continue;

                $refFieldName = $this->m_BizRecord->getFieldByColumn($tableJoin->m_ColumnRef); // join table
                $returnRecord[$refFieldName] = $joinFieldName;

                // populate joinRecord's field to current record
                foreach ($this->m_BizRecord as $field)
                {
                    if ($field->m_Join == $tableJoin->m_Name)
                    {
                        // use join column to match joinRecord field's column
                        $jFieldName = $joinDataObj->m_BizRecord->getFieldByColumn($field->m_Column); // joined-main table
                        $returnRecord[$field->m_Name] = $jFieldName;
                    }
                }
                break;
            }
        }
        return $returnRecord;
    }

    /**
     * Pick the joined object's current record to the current record
     *
     * @param BizDataObj $joinDataObj
     * @param string $joinName name of join (optional)
     * @return array return a modified record with joined record data
     */
    public function joinRecord($joinDataObj, $joinName="")
    {
        // get the maintable of the joindataobj
        $joinTable = $joinDataObj->m_MainTable;
        $joinRecord = null;
        $returnRecord = array();

        // find the proper join according to join name and the maintable
        foreach ($this->m_TableJoins as $tableJoin)
        {
            if (($joinName == $tableJoin->m_Name || $joinName == "")
                    && $tableJoin->m_Table == $joinTable)
            {
                // populate the column-fieldvalue to columnRef-fieldvalue
                // get the field mapping to the column, then get the field value
                $joinFieldName = $joinDataObj->m_BizRecord->getFieldByColumn($tableJoin->m_Column); // joined-main table
                if (!$joinFieldName) continue;
                if (!$joinRecord)
                    $joinRecord = $joinDataObj->getActiveRecord();
                $refFieldName = $this->m_BizRecord->getFieldByColumn($tableJoin->m_ColumnRef); // join table
                $returnRecord[$refFieldName] = $joinRecord[$joinFieldName];
                // populate joinRecord's field to current record
                foreach ($this->m_BizRecord as $fld)
                {
                    if ($fld->m_Join == $tableJoin->m_Name)
                    {
                        // use join column to match joinRecord field's column
                        $jfldname = $joinDataObj->m_BizRecord->getFieldByColumn($fld->m_Column); // joined-main table
                        $returnRecord[$fld->m_Name] = $joinRecord[$jfldname];
                    }
                }
                break;
            }
        }
        // return a modified record with joined record data
        return $returnRecord;
    }

    /**
     * Add a new record to current record set
     *
     * @param array $recArr
     * @param boolean $isParentObjUpdated
     * @return boolean
     */
    public function addRecord($recArr, &$isParentObjUpdated)
    {
    	$oldBaseSearchRule=$this->m_BaseSearchRule;
    	$this->m_BaseSearchRule="";
        $result = BizDataObj_Assoc::addRecord($this, $recArr, $isParentObjUpdated);
        //$this->m_BaseSearchRule=$oldBaseSearchRule;
        return $result;
    }

    /**
     * Remove a record from current record set of current association relationship
     *
     * @param array $recArr
     * @param boolean &$isParentObjUpdated
     * @return boolean
     */
    public function removeRecord($recArr, &$isParentObjUpdated)
    {
        return BizDataObj_Assoc::removeRecord($this, $recArr, $isParentObjUpdated);
    }

    /**
     * Clean chache
     *
     * @global BizSystem $g_BizSystem
     * @return void
     */
    public function cleanCache()
    {
        if($this->m_CacheLifeTime > 0)
        {
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name, $this->m_CacheLifeTime);
            $cacheSvc->cleanAll();
            
        }
    }

    /**
     * Is need to generate Id?
     *
     * @param array $recArr array of record
     * @return boolean
     */
    private function _isNeedGenerateId($recArr)
    {
        if ($this->m_IdGeneration != 'None' && (!$recArr["Id"] || $recArr["Id"] == "")) return true;
        if ($this->m_IdGeneration == 'Identity') return true;
    }

}

?>