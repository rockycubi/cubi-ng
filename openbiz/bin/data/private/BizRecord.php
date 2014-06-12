<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data.private
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: BizRecord.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * BizRecord class implements basic function of handling record
 *
 * @package openbiz.bin.data.private
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 */
class BizRecord extends MetaIterator
{
    /**
     *
     * @var array
     */
    protected $m_KeyFldColMap = array();
    /**
     *
     * @var array
     */
    protected $m_ColFldMap = array();
    /**
     *
     * @var array
     */
    public $m_InputFields;
    
    protected $m_IgnoreInQuery = false;

    /**
     * Initialize BizRecord with xml array
     *
     * @param array $xmlArr array data from xml file
     * @param string $className name of the class of the object
     * @param object $parentObj parent object
     * @return void
     */
    function __construct(&$xmlArr, $className, $parentObj=null)
    {
        parent::__construct($xmlArr, $className, $parentObj);
        $this->_initSetup();
    }

    /**
     * Merge with another BizRecord object. Used in metedata inheritance.
     *
     * @param BizRecord $anotherMIObj to be merged BizRecord object
     * @return void
     */
    public function merge(&$anotherMIObj)
    {
        parent::merge($anotherMIObj);
        $this->_initSetup();
    }

    /**
     * Do some initial setup work
     *
     * @return void
     */
    private function _initSetup()
    {
        unset($this->m_ColFldMap); $this->m_ColFldMap = array();
        unset($this->m_KeyFldColMap); $this->m_KeyFldColMap = array();
        $i = 0;
        // generate column index if the column is one of the basetable (m_Column!="")
        foreach($this->m_var as $key=>$field)
        {  // $key is fieldname, $field is fieldobj
        ////////////////////////////////////////////////////////////////////
        // TODO: join fields and nonjoin fields may have same column name
        ////////////////////////////////////////////////////////////////////
            if ($field->m_Column && !$field->m_Join)  // ignore the joined field column
                $this->m_ColFldMap[$field->m_Column] = $key;
            if ($field->m_Column || $field->m_SqlExpression)
            {
                $field->m_Index = $i++;
            }
        }
        // create key field column map to support composite key
        if (isset($this->m_var["Id"]) && $this->m_var["Id"]->m_Column)
        {
            $keycols = explode(",", $this->m_var["Id"]->m_Column);
            foreach ($keycols as $col)
            {
                $field = $this->getFieldByColumn($col);  // main table
                $this->m_KeyFldColMap[$field] = $col;
            }
        }
    }

    /**
     * Get fielf by column name
     *
     * @param string $column column name
     * @param string $table table name
     * @return BizField instant of {@link BizField}
     */
    public function getFieldByColumn($column, $table=null)
    {
        // TODO: 2 columns can have the same name in case of joined fields
        if(array_key_exists($column, $this->m_ColFldMap))
            return $this->m_ColFldMap[$column];
        return null;
    }

    /**
     * Get an empty record array. Called by {@link BizDataObj::newRecord()}
     *
     * @return array record array
     **/
    final public function getEmptyRecordArr()
    {
        $recArr = array();
        foreach ($this->m_var as $key=>$field)
        {
            $recArr[$key] = $field->getDefaultValue();
        }
        return $recArr;
    }

    /**
     * Get key (Id) value.
     * If Id is defined as composite key, the returned key value
     * is the combination of key columns
     *
     * @param boolean $isUseOldValue
     * @return string key field string
     **/
    final public function getKeyValue($isUseOldValue=false)
    {
        $keyValue = "";
        foreach($this->m_KeyFldColMap as $fieldName=>$colName)
        {
            $val = $isUseOldValue ? $this->m_var[$fieldName]->m_OldValue : $this->m_var[$fieldName]->m_Value;
            if ($keyValue == "")
                $keyValue .= $val;
            else
            // use base64 (a-zA-Z1-9+-) to encode the key and connect them with "#"
                $keyValue .= CK_CONNECTOR . $val;
        }
        return $keyValue;
    }

    /**
     * Get a list of fields (name) who are defined as keys columns
     *
     * @return array array of key fields
     **/
    final public function getKeyFields()
    {
        $keyFields = array();
        foreach($this->m_KeyFldColMap as $fieldName=>$colName)
        {
            $keyFields[$fieldName] = $this->m_var[$fieldName];
        }
        return $keyFields;
    }

    /**
     * Get key search rule.
     * The key search rule is used to find a single record by given key values
     *
     * @param boolean $isUseOldValue true if old key value is used in the search rule
     * @param boolean $isUseColumnName true if column name is used in the search rule, false if [field] is used
     * @return string search rule
     */
    public function getKeySearchRule($isUseOldValue=false, $isUseColumnName=false)
    {
        $keyFields = $this->getKeyFields();
        $retStr = "";
        foreach ($keyFields as $fieldName=>$fieldObj)
        {
            if ($retStr != "") $retStr .= " AND ";
            $lhs = $isUseColumnName ? $fieldObj->m_Column : "[$fieldName]";
            $rhs = $isUseOldValue ? $fieldObj->m_OldValue : $fieldObj->m_Value;
            if ($rhs == "")
                $retStr .= "(".$lhs."='".$rhs."' or ".$lhs." is null)";
            else
                $retStr .= $lhs."='".$rhs."'";
        }
        return $retStr;
    }

    /**
     * Set record array to internal data structure
     *
     * @param array $recArr record array
     * @return void
     */
    public function setRecordArr($recArr)
    {
        if (!$recArr) return;
        foreach ($this->m_var as $key=>$field)
        {
            if (key_exists($key, $recArr)){
					$recArr[$key] = $field->setValue($recArr[$key]);
            }
        }
    }

    /**
     * Assign a record array as the internal record of the {@link BizRecord}
     *
     * @param array $inpuArr
     * @return void
     */
    final public function setInputRecord(&$inputArr)
    {
        // unformat the inputs
        unset($this->m_InputFields);
        foreach($inputArr as $key=>$value)
        {   
            // if allow changing key field, need to keep the old value which is also useful for audit trail
            // if (!$value)
            //    continue;
            $bizField = $this->m_var[$key];
            if (!$bizField) continue;

            $realVal = BizSystem::typeManager()->formattedStringToValue($bizField->m_Type, $bizField->m_Format, $value);
            if(strtoupper($bizField->m_Encrypted)=='Y'){
					$svcobj = BizSystem::getService(CRYPT_SERVICE);					
					$realVal = $svcobj->encrypt($realVal);
            		$bizField->setValue($realVal);
            }
            // todo: need to optimize on lob column            
            $bizField->setValue($realVal);
            
            $this->m_InputFields[] = $key;
        }
        //$this->m_var["Id"]->setValue($this->getKeyValue());
    }

    /**
     * Save old recrod,
     * used in update record when old record value is needed in the action
     *
     * @param array $inputArr old record array
     * @return avoid
     */
    final public function saveOldRecord(&$inputArr)
    {
        if (!$inputArr)
            return;
        foreach($inputArr as $key=>$value)
        {
            $bizField = $this->m_var[$key];
            if (!$bizField) continue;
            $bizField->saveOldValue($value);
        }
    }

    /**
     * Get record array by converting input indexed-Value array to Field-Value pairs
     *
     * @param array $sqlArr column value pair array
     * @return array record array
     **/
    final public function getRecordArr($sqlArr=null)
    {
        if ($sqlArr)
            $this->_setSqlRecord($sqlArr);
        $recArr = array();
        foreach ($this->m_var as $key=>$field){
        	if($field->m_Encrypted=='Y'){
            	$svcobj = BizSystem::getService(CRYPT_SERVICE);        	
        		$value = $svcobj->decrypt($field->getValue()); 
            	$recArr[$key] = $value;
        	}else{
        		$recArr[$key] = $field->getValue();	
        	}
        }
        return $recArr;
    }

    /**
     * Conver sql array to record array
     * 
     * @param array $sqlArr indexed-value pair array
     * @return array field-value record array
     */
    public function convertSqlArrToRecArr($sqlArr)
    {
        $recArr = array();
        foreach ($this->m_var as $key=>$field)
        {
            if ($field->m_Column || $field->m_SqlExpression)
            {
                $recArr[$key] = $sqlArr[$field->m_Index];
            }
            else
                $recArr[$key] = "";
        }
        return $recArr;
    }

    /**
     * Set sql record array to internal data
     * 
     * @param array $sqlArr indexed-value array from sql result
     * @return avoid
     */
    private function _setSqlRecord($sqlArr)
    {
        foreach ($this->m_var as $key=>$field)
        {
            if ($field->m_Column || $field->m_SqlExpression)
            {
                $field->setValue($sqlArr[$field->m_Index]);
            }
        }
        if (isset($this->m_var["Id"]))
            $this->m_var["Id"]->setValue($this->getKeyValue());
    }

    /**
     * Get join input record
     *
     * @param <type> $join
     * @return array
     */
    public function getJoinInputRecord($join)
    {
        $inputFields = $this->m_InputFields;  // Added by Jixian on 2009-02-15 for implement onSaveDataObj
        $recArr = array();
        foreach ($this->m_var as $key=>$value)
        {
            // do not consider joined columns
            // Added by Jixian on 2009-02-15 for implement onSaveDataObj
            /*
             * It's the time to consider about joined columns
             */

            $field = $value;

            if ($field->m_Join == $join)
            {
                $recArr[$key] = $value;
            }
        }
        return $recArr;
    }

    
    /**
     * Get join search rule
     * 
     * NOTE: Added by Jixian on 2009-02-16 for implement onSaveDataObj
     * 
     * @param TableJoin $tableJoin
     * @param boolean $isUseOldValue
     * @return string
     */
    public function getJoinSearchRule($tableJoin, $isUseOldValue=false)
    {
        $joinFieldName = $this->getFieldByColumn($tableJoin->m_ColumnRef, $tableJoin->m_Table);
        $joinField=$this->m_var[$joinFieldName];
        $rhs = $isUseOldValue ? $joinField->m_OldValue : $joinField->m_Value;
        $retStr = $tableJoin->m_Column . "='" . $rhs . "'";
        return $retStr;
    }

    /**
     * Get insert/update fields.
     * Ignore unchanged field in UPDATE case
     *
     * @return array field value pair array
     **/
    final public function getToSaveFields($type)
    {
        // TODO: if join != null, get columns only for the join
        $sqlFields = array();

        // expand input fields with oncreate or onupdate fields
        $inputFields = $this->m_InputFields;
        foreach ($this->m_var as $key=>$field)
        {
            if (($type=='UPDATE' && $field->m_ValueOnUpdate != null)
                || ($type=='CREATE' && $field->m_ValueOnCreate != null))
            {
                if (!in_array($key, $this->m_InputFields))
                    $inputFields[] = $key;
            }
        }

        foreach ($inputFields as $key)
        {
            // ignore the composite key Id field
            if ($key == "Id" && count($this->m_KeyFldColMap) > 1)
                continue;
            $field = $this->m_var[$key];
            // do not consider joined columns
            if ($field->m_Column && !$field->m_Join)
            {
                $sqlFields[] = $field;
            }
        }
        return $sqlFields;
    }
}
?>