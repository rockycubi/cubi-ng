<?php
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
 * @version   $Id: BizDataObj_Abstract.php 4108 2011-05-08 06:01:30Z jixian2003 $
 */

include_once(OPENBIZ_BIN.'data/private/BizRecord.php');
include_once(OPENBIZ_BIN.'data/private/TableJoin.php');
include_once(OPENBIZ_BIN.'data/private/ObjReference.php');
include_once(OPENBIZ_BIN.'data/private/BizDataObj_Assoc.php');
include_once(OPENBIZ_BIN.'data/private/BizDataObj_SQLHelper.php');
include_once(OPENBIZ_BIN.'data/BizField.php');

/**
 * BizDataObj_Abstract class contains data object metadata functions
 *
 * @package   openbiz.bin.data
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
abstract class BizDataObj_Abstract extends MetaObject implements iSessionObject
{
    // metadata vars are public, necessary for metadata inheritance

    /**
     * Database name
     *
     * @var string
     */
    public $m_Database;

    /**
     * Base search rule
     *
     * @var string
     */
    public $m_BaseSearchRule = null;    // support expression

    /**
     * Base sort rule
     *
     * @var string
     */
    public $m_BaseSortRule = null;      // support expression

    /**
     * Base other SQL rule
     *
     * @var string
     */
    public $m_BaseOtherSQLRule = null;  // support expression

    /**
     * Name of main table
     * 
     * @var string
     */
    public $m_MainTable = "";

    /**
     * BizRecord object
     * 
     * @var BizRecord
     */
    public $m_BizRecord = null;

    /**
     * Name of inherited form (meta-form)
     *
     * @var string
     */
    public $m_InheritFrom;
    
    /**
     * Access rule (visibility) of the records
     * Can be Openbiz query string or any expression
     * Example: [user_id]={@profile['Id']} or {@vis:self([user_id])} or {@vis:group([group_id])}
     * @var string
     */
    public $m_AccessRule = null;
    
    /**
     * Condition of user ability to update a record
     * @var string
     */
    public $m_UpdateCondition = null;   // support expression
    
    /**
     * Condition of user ability to delete a record
     * @var string
     */
    public $m_DeleteCondition = null;   // support expression
    
    /**
     * Record id generation option
     *
     * @var string
     */
    public $m_IdGeneration = null;
    
    /**
     * MetaIterator of ObjReferences
     *
     * @var MetaIterator
     */
    public $m_ObjReferences = null;

    /**
     * MetaIterator of TableJoin
     *
     * @var MetaIterator
     */
    public $m_TableJoins = null;

    /**
     *
     * @var MetaIterator
     */
    public $m_Parameters = null;
    public $m_Stateless = null;
    public $m_Uniqueness = null;

    /**
     * Search rule
     *
     * @var string
     */
    public $m_SearchRule = null;        // support expression

    /**
     * Sort rule
     * @var string
     */
    public $m_SortRule = null;          // support expression

    /**
     * Other SQL rule
     *
     * @var string
     */
    public $m_OtherSQLRule = null;      // support expression

    /**
     * Life time o cache
     *
     * @var number
     */
    public $m_CacheLifeTime = null;	    // set 0 to disbale cache function

    /**
     * Message file path
     *
     * @var string
     */
    public $m_MessageFile = null;        // 

    /**
     * Limitation of query
     *   $this->m_Limit['count'] - count of record that loaded from database (per page)
     *   $this->m_Limit['offset'] - offset of record (for paging)
     * 
     * @var array
     */
    protected $m_Limit = array();

    /**
     * Array messages that loaded from {@link $m_MessageFile}
     *
     * @var array
     */
    protected $m_Messages;
	
	protected $m_QueryParams = array();
    
    public $m_DataPermControl;
	
	public $m_EvtMgrName, $m_EventManager;
    
    /**
     * Initialize BizDataObj_Abstract with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        $this->inheritParentObj();
    }

    /**
     * Read Metadata from xml array
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_InheritFrom = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["INHERITFROM"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["INHERITFROM"] : null;
        $this->m_SearchRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        $this->m_BaseSearchRule = $this->m_SearchRule;
        $this->m_SortRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SORTRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SORTRULE"] : null;
        $this->m_BaseSortRule = $this->m_SortRule;
        $this->m_OtherSQLRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["OTHERSQLRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["OTHERSQLRULE"] : null;
        $this->m_BaseOtherSQLRule = $this->m_OtherSQLRule;
        $this->m_AccessRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["ACCESSRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["ACCESSRULE"] : null;
        $this->m_UpdateCondition = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UPDATECONDITION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UPDATECONDITION"] : null;
        $this->m_DeleteCondition = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DELETECONDITION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DELETECONDITION"] : null;
        $this->m_Database = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DBNAME"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DBNAME"] : null;
        if ($this->m_Database == null)
            $this->m_Database = "Default";
        $this->m_MainTable = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["TABLE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["TABLE"] : null;
        $this->m_IdGeneration = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["IDGENERATION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["IDGENERATION"] : null;
        $this->m_Stateless = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["STATELESS"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["STATELESS"] : "Y";

        // read in uniqueness attribute
        $this->m_Uniqueness = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UNIQUENESS"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UNIQUENESS"] : null;

        $this->m_CacheLifeTime = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";
        $this->m_Name = $this->prefixPackage($this->m_Name);
        if ($this->m_InheritFrom == '@sourceMeta') $this->m_InheritFrom = '@'.$this->m_Name;
        else $this->m_InheritFrom = $this->prefixPackage($this->m_InheritFrom);

        // build BizRecord
        $this->m_BizRecord = new BizRecord($xmlArr["BIZDATAOBJ"]["BIZFIELDLIST"]["BIZFIELD"],"BizField",$this);
        // build TableJoins
        $this->m_TableJoins = new MetaIterator($xmlArr["BIZDATAOBJ"]["TABLEJOINS"]["JOIN"],"TableJoin",$this);
        // build ObjReferences
        $this->m_ObjReferences = new MetaIterator($xmlArr["BIZDATAOBJ"]["OBJREFERENCES"]["OBJECT"],"ObjReference",$this);
        // read in parameters
        $this->m_Parameters = new MetaIterator($xmlArr["BIZDATAOBJ"]["PARAMETERS"]["PARAMETER"],"Parameter");

        $this->m_MessageFile = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile , $this->m_Package);
		
		$this->m_EvtMgrName = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["EVENTMANAGER"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["EVENTMANAGER"] : null;
        
        $this->m_DataPermControl = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DATAPERMCONTROL"]) ? strtoupper($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DATAPERMCONTROL"]) : 'N';
    }

    /**
     * Inherit from parent object. Name, Package, Class cannot be inherited
     *
     * @return void
     */
    protected function inheritParentObj()
    {
        if (!$this->m_InheritFrom) return;
        $parentObj = BizSystem::getObject($this->m_InheritFrom);

        $this->m_Description  = $this->m_Description ? $this->m_Description : $parentObj->m_Description;
        $this->m_SearchRule   = $this->m_SearchRule ? $this->m_SearchRule : $parentObj->m_SearchRule;
        $this->m_BaseSearchRule = $this->m_SearchRule;
        $this->m_SortRule     = $this->m_SortRule ? $this->m_SortRule: $parentObj->m_SortRule;
        $this->m_BaseSortRule = $this->m_SortRule;
        $this->m_OtherSQLRule = $this->m_OtherSQLRule ? $this->m_OtherSQLRule: $parentObj->m_OtherSQLRule;
        $this->m_AccessRule   = $this->m_AccessRule ? $this->m_AccessRule: $parentObj->m_AccessRule;
        $this->m_UpdateCondition = $this->m_UpdateCondition ? $this->m_UpdateCondition: $parentObj->m_UpdateCondition;
        $this->m_DeleteCondition = $this->m_DeleteCondition ? $this->m_DeleteCondition: $parentObj->m_DeleteCondition;
        $this->m_Database     = $this->m_Database ? $this->m_Database: $parentObj->m_Database;
        $this->m_MainTable    = $this->m_MainTable ? $this->m_MainTable: $parentObj->m_MainTable;
        $this->m_IdGeneration = $this->m_IdGeneration ? $this->m_IdGeneration: $parentObj->m_IdGeneration;
        $this->m_Stateless    = $this->m_Stateless ? $this->m_Stateless: $parentObj->m_Stateless;
	$this->m_DataPermControl = $this->m_DataPermControl ? $this->m_DataPermControl : $parentObj->m_DataPermControl;
        $this->m_BizRecord->merge($parentObj->m_BizRecord);

        foreach ($this->m_BizRecord as $field)
            $field->adjustBizObjName($this->m_Name);
        
        $this->m_TableJoins->merge($parentObj->m_TableJoins);
        $this->m_ObjReferences->merge($parentObj->m_ObjReferences);
        $this->m_Parameters->merge($parentObj->m_Parameters);
    }

    /**
     * Get Message
     *
     * @param <type> $msgid message Id
     * @param array $params
     * @return string
     */
    protected function getMessage($msgid, $params=array())
    {
        $message = isset($this->m_Messages[$msgid]) ? $this->m_Messages[$msgid] : constant($msgid);
        //$message = I18n::getInstance()->translate($message);
        $message = I18n::t($message, $msgid, $this->getModuleName($this->m_Name));
        return vsprintf($message,$params);
    }

    /**
     * Get session variables
     *
     * @param SessionContext $sessionContext
     */
    public function getSessionVars($sessionContext)
    {}

    /**
     * Set session variables
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function setSessionVars($sessionContext)
    {}
	
	public function events() 
	{
		if (!$this->m_EvtMgrName && defined('EVENT_MANAGER')) $this->m_EvtMgrName = EVENT_MANAGER;
		else $this->m_EventManager = new EventManager();
		if (!$this->m_EventManager) $this->m_EventManager = BizSystem::getObject($this->m_EvtMgrName);
		return $this->m_EventManager;
	}

    /**
     * Reset rules
     *
     * @return void
     */
    public function resetRules()
    {
        $this->m_SearchRule = $this->m_BaseSearchRule;
        $this->m_SortRule = $this->m_BaseSortRule;
        $this->m_OtherSQLRule = $this->m_BaseOtherSQLRule;
        return $this;
    }

    /**
     * Clear search rule.
     * Reset the search rule to default search rule set in metadata
     *
     * @return BizDataObj_Abstract
     */
    public function clearSearchRule()
    {
        $this->m_SearchRule = $this->m_BaseSearchRule;
        return $this;
    }

    /**
     * Clear sort rule.
     * Reset the sort rule to default sort rule set in metadata
     *
     * @return void
     */
    public function clearSortRule()  // reset sortrule

    {
        $this->m_SortRule = $this->m_BaseSortRule;
        return $this;
    }

    /**
     * Clear other SQL rule.
     * Reset the other SQL rule to default other SQL rule set in metadata
     *
     * @return void
     */
    public function clearOtherSQLRule()

    {
        $this->m_OtherSQLRule = $this->m_BaseOtherSQLRule;
        return $this;
    }

    /**
     * Reset all rules (search, sort, other SQL rule)
     *
     * @return void
     */
    public function clearAllRules()
    {
        $this->m_SearchRule = $this->m_BaseSearchRule;
        $this->m_SortRule = $this->m_BaseSortRule;
        $this->m_OtherSQLRule = $this->m_BaseOtherSQLRule;
        $this->m_Limit = array();
        return $this;
    }

    /**
     * Set search rule as text in sql where clause. i.e. [fieldName] opr Value
     *
     * @param string $rule search rule has format "[fieldName] opr Value"
     * @param boolean $overWrite specify if this rule should overwrite any existing rule
     * @return void
     **/
    public function setSearchRule($rule, $overWrite=false)
    {
        if (!$rule || $rule == "")
            return;
        if (!$this->m_SearchRule || $overWrite == true)
        {
            $this->m_SearchRule = $rule;
        }
        elseif (strpos($this->m_SearchRule, $rule) === false)
        {
            $this->m_SearchRule .= " AND " . $rule;
        }
    }

    /**
     * Set query parameter for parameter binding in the query
     *
     * @param array {fieldname:value} list
     * @return void
     **/
    public function setQueryParameters($paramValues)
    {
        foreach ($paramValues as $param=>$value)
            $this->m_QueryParams[$param] = $value;
    }
	
	public function getQueryParameters()
    {
        return $this->m_QueryParams;
    }

    /**
     * Set search rule as text in sql order by clause. i.e. [fieldName] DESC|ASC
     *
     * @param string $rule sort rule has format "[fieldName] DESC|ASC"
     * @return void
     **/
    public function setSortRule($rule)
    {
        // sort rule has format "[fieldName] DESC|ASC", replace [fieldName] with table.column
        $this->m_SortRule = $rule;
    }

    /**
     * Set other SQL rule, append extra SQL statment in sql. i.e. GROUP BY [fieldName]
     *
     * @param string $rule search rule with SQL format "GROUP BY [fieldName] HAVING ..."
     * @return void
     **/
    public function setOtherSQLRule($rule)
    {
        // $rule has SQL format "GROUP BY [fieldName] HAVING ...". replace [fieldName] with table.column
        $this->m_OtherSQLRule = $rule;
    }

    /**
     * Set limit of the query.
     *
     * @param int $count the number of records to return
     * @param int $offset the starting position of the result records
     * @return void
     */
    public function setLimit($count, $offset=0)
    {
    	if($count<0)
    	{
    		$count = 0;
    	}    	
        if($offset<0)
    	{
    		$offset = 0;
    	}    	
        $this->m_Limit['count'] = $count;
        $this->m_Limit['offset'] = $offset;
    }

    /**
     * Get database connection
     *
     * @return Zend_Db_Adapter_Abstract
     **/
    public function getDBConnection($type='default')
    {
    	switch(strtolower($type))
    	{
    		case "default":
    		case "read":
    			if($this->m_DatabaseforRead){
    				$dbName = $this->m_DatabaseforRead;	
    			}
    			else
    			{
    				$dbName = $this->m_Database;
    			}
    			break;
    		case "write":
    			if($this->m_DatabaseforWrite){
    				$dbName = $this->m_DatabaseforWrite;	
    			}
    			else
    			{
    				$dbName = $this->m_Database;
    			}    			
    			break;
    	}
        return BizSystem::dbConnection($dbName);
    }

    /**
     * Get the property of the object. Used in expression language
     * 
     * @param string $propertyName name of the property
     * @return BizField|string property value
     */
    public function getProperty($propertyName)
    {
        $ret = parent::getProperty($propertyName);
        if ($ret) return $ret;
        if ($propertyName == "Table") return $this->m_Table;
        if ($propertyName == "SearchRule") return $this->m_SearchRule;
        // get control object if propertyName is "Field[fldname]"
        $pos1 = strpos($propertyName, "[");
        $pos2 = strpos($propertyName, "]");
        if ($pos1>0 && $pos2>$pos1)
        {
            $propType = substr($propertyName, 0, $pos1);
            $fieldName = substr($propertyName, $pos1+1,$pos2-$pos1-1);
            if ($propType == "param")
            {   // get parameter
                return $this->m_Parameters->get($fieldName);
            }
            return $this->getField($fieldName);
        }
    }

    /**
     * Get object parameter value
     *
     * @param string $paramName name of the parameter
     * @return string parameter value
     */
    public function getParameter($paramName)
    {
        return $this->m_Parameters[$paramName]->m_Value;
    }

    /**
     * Get the object instance defined in the object reference
     *
     * @param string $objName the object name list in the ObjectReference part
     * @return BizDataObj object instance
     */
    public function getRefObject($objName)
    {
        // see if there is such object in the ObjReferences
        $objRef = $this->m_ObjReferences->get($objName);
        if (!$objRef)
            return null;

        // apply association on the object
        // $assc = $this->EvaluateExpression($objRef->m_Association);

        // get the object instance
        $obj = BizSystem::getObject($objName);
        $obj->setAssociation($objRef, $this);
        return $obj;
    }

    /**
     * Get the Association (array)
     *
     * @return array array of association
     **/
    public function getAssociation()
    {
        return $this->m_Association;
    }

    /**
     * Set the association of the object
     *
     * @param ObjReference $objRef
     * @param BizDataObj $asscObj
     * @return void
     */
    protected function setAssociation($objRef, $asscObj)
    {
        $this->m_Association["AsscObjName"] = $asscObj->m_Name;
        $this->m_Association["Relationship"] = $objRef->m_Relationship;
        $this->m_Association["Table"] = $objRef->m_Table;
        $this->m_Association["Column"] = $objRef->m_Column;
        $this->m_Association["FieldRef"] = $objRef->m_FieldRef;
        $this->m_Association["FieldRefVal"] = $asscObj->getFieldValue($objRef->m_FieldRef);
        $this->m_Association["CondColumn"] = $objRef->m_CondColumn;
        $this->m_Association["CondValue"] = $objRef->m_CondValue;
        $this->m_Association["Condition"] = $objRef->m_Condition;        
        if ($objRef->m_Relationship == "M-M" || $objRef->m_Relationship  == "Self-Self")
        {
            $this->m_Association["XTable"] = $objRef->m_XTable;
            $this->m_Association["XColumn1"] = $objRef->m_XColumn1;
            $this->m_Association["XColumn2"] = $objRef->m_XColumn2;
            $this->m_Association["XKeyColumn"] = $objRef->m_XKeyColumn;
            $this->m_Association["XDataObj"] = $objRef->m_XDataObj;
        }
    }

    /**
     * Create an new (empty) record
     *
     * @return array - empty record array with default values
     **/
    abstract public function newRecord();

    /**
     * Insert record using given input record array
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
     **/
    abstract public function insertRecord($recArr);

    /**
     * Update record using given input record array
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @param array $oldRec - associated array who is the old record field name / value pairs
     * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
     **/
    abstract public function updateRecord($recArr, $oldRec=null);

    /**
     * Delete current record or delete the given input record
     *
     * @param array $recArr - associated array whose keys are field names of this BizDataObj
     * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
     **/
    abstract public function deleteRecord($recArr);

    /**
     * Fetches SQL result rows as a sequential array according the query rules set before.
     * Sample code:
     * <pre>
     *   $do->resetRules();
     *   $do->setSearchRule($search_rule1);
     *   $do->setSearchRule($search_rule2);
     *   $do->setSortRule($sort_rule);
     *   $do->SetOtherRule($groupby);
     *   $total = $do->count();
     *   $do->setLimit($count, $offset=0);
     *   $recordSet = $do->fetch();
     * </pre>
     *
     * @return array array of records
     */
    abstract public function fetch();

    /**
     * Fetches SQL result rows as a sequential array without using query rules set before.
     * Sample code:
     * <pre>
     *   // fetch all record with firstname starting with Mike
     *   $do->directFetch("[FirstName] LIKE 'Mike%'");
     *   // fetch first 10 records with firstname starting with Mike
     *   $do->directFetch("[FirstName] LIKE 'Mike%'", 10);
     *   // fetch 20th-30th records with firstname starting with Mike
     *   $do->directFetch("[FirstName] LIKE 'Mike%'", 10, 20);
     * </pre>
     *
     * @param string $searchRule the search rule string
     * @param int $count number of records to return
     * @param int $offset the starting point of the return records
     * @return array array of records
     */
    abstract public function directFetch($searchRule="", $count=-1, $offset=0);

    /**
     * Do the search query and return results set as PDOStatement.
     * Sample code:
     * <pre>
     *   $do->resetRules();
     *   $do->setSearchRule($search_rule1);
     *   $do->setSearchRule($search_rule2);
     *   $do->setSortRule($sort_rule);
     *   $do->SetOtherRule($groupby);
     *   $total = $do->count();
     *   $do->setLimit($count, $offset=0);
     *   $resultSet = $do->find();
     *   $do->getDBConnection()->setFetchMode(PDO::FETCH_ASSOC);
     *   while ($record = $resultSet->fetch())
     *   {
     *       print_r($record);
     *   }
     * </pre>
     *
     * @return PDOStatement PDO statement object
     */
    abstract public function find();

    /**
     * Count the number of record according to the search results set before.
     * it ignores limit setting
     *
     * @return int number of records
     */
    abstract public function count();
}