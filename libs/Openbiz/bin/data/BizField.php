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
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: BizField.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * Class BizField is the class of a logic field which mapps to a table column
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 **/
class BizField extends MetaObject
{
    /**
     * Name of {@link BizDataObj}
     *
     * @var string
     */
    public $m_BizObjName;
    public $m_Join = null;
    public $m_Column = null;
    public $m_Alias = null;
    public $m_Index;
    /**
     * Type of field in string
     *
     * @var string
     */
    public $m_Type = null;

    /**
     * Format of field
     *
     * @var string
     */
    public $m_Format = null;

    /**
     * Length of field
     *
     * @var number
     */
    public $m_Length = null;
    public $m_ValueExpression = null;// support expression

    /**
     * Is field required?
     *
     * @var mixed
     */
    public $m_Required = null;       // support expression
    public $m_Validator = null;      // support expression
    public $m_SqlExpression = null;  // support expression
	public $m_Encrypted = "N";
	public $m_ClearText = null;
    /**
     * Default value of field
     *
     * @var mixed
     */
    public $m_DefaultValue = null;
    public $m_ValueOnCreate = null;
    public $m_ValueOnUpdate = null;

    /**
     * Is on Audit?
     *
     * @var boolean
     */
    public $m_OnAudit = false;

    /**
     * The real value of the field, not from metadata
     *
     * @var mixed
     */
    public $m_Value = null; 
    public $m_OldValue = null; // the old value of the field
	public $m_IgnoreInQuery = false;
	
	protected $_prevValue, $_getValueCache;

    /**
     * Initialize BizField with xml array
     *
     * @param array $xmlArr xml array
     * @param BizDataObj $bizObj BizDataObj instance
     * @return void
     */
    function __construct(&$xmlArr, $bizObj)
    {
        $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
        $this->m_BizObjName = $bizObj->m_Name;
        $this->m_Package = $bizObj->m_Package;
        $this->m_Join = isset($xmlArr["ATTRIBUTES"]["JOIN"]) ? $xmlArr["ATTRIBUTES"]["JOIN"] : null;
        $this->m_Column = isset($xmlArr["ATTRIBUTES"]["COLUMN"]) ? $xmlArr["ATTRIBUTES"]["COLUMN"] : null;
        $this->m_Alias = isset($xmlArr["ATTRIBUTES"]["ALIAS"]) ? $xmlArr["ATTRIBUTES"]["ALIAS"] : null;
        $this->m_ValueExpression = isset($xmlArr["ATTRIBUTES"]["VALUE"]) ? $xmlArr["ATTRIBUTES"]["VALUE"] : null;
        $this->m_DefaultValue = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUE"] : null;
        $this->m_Type = isset($xmlArr["ATTRIBUTES"]["TYPE"]) ? $xmlArr["ATTRIBUTES"]["TYPE"] : null;
        $this->m_Format = isset($xmlArr["ATTRIBUTES"]["FORMAT"]) ? $xmlArr["ATTRIBUTES"]["FORMAT"] : null;
        $this->m_Length = isset($xmlArr["ATTRIBUTES"]["LENGTH"]) ? $xmlArr["ATTRIBUTES"]["LENGTH"] : null;
        $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
        $this->m_Encrypted = isset($xmlArr["ATTRIBUTES"]["ENCRYPTED"]) ? strtoupper($xmlArr["ATTRIBUTES"]["ENCRYPTED"]) :"N";
//        if($this->m_Encrypted=='Y'){
//        	$this->m_ClearText="N";
//        }else{
//        	$this->m_ClearText="Y";
//        }
        $this->m_Validator = isset($xmlArr["ATTRIBUTES"]["VALIDATOR"]) ? $xmlArr["ATTRIBUTES"]["VALIDATOR"] : null;
        $this->m_SqlExpression = isset($xmlArr["ATTRIBUTES"]["SQLEXPR"]) ? $xmlArr["ATTRIBUTES"]["SQLEXPR"] : null;
        $this->m_ValueOnCreate = isset($xmlArr["ATTRIBUTES"]["VALUEONCREATE"]) ? $xmlArr["ATTRIBUTES"]["VALUEONCREATE"] : null;
        $this->m_ValueOnUpdate = isset($xmlArr["ATTRIBUTES"]["VALUEONUPDATE"]) ? $xmlArr["ATTRIBUTES"]["VALUEONUPDATE"] : null;
        if (isset($xmlArr["ATTRIBUTES"]["ONAUDIT"]) && $xmlArr["ATTRIBUTES"]["ONAUDIT"]=='Y')
            $this->m_OnAudit = true;

        $this->m_BizObjName = $this->prefixPackage($this->m_BizObjName);

        if (!$this->m_Format) $this->useDefaultFormat();
    }

    /**
     * Use default format if no format is given
     *
     * @return void
     */
    protected function useDefaultFormat()
    {
        if ($this->m_Type == "Date")
            $this->m_Format = '%Y-%m-%d';
        elseif ($this->m_Type == "Datetime")
            $this->m_Format = '%Y-%m-%d %H:%M:%S';
    }

    /**
     * Get property value
     * 
     * @param string $propertyName property name
     * @return mixed property value
     */
    public function getProperty($propertyName)
    {
        $ret = parent::getProperty($propertyName);
        if ($ret) return $ret;
        //if ($propertyName == "Value") return $this->getValue();
		if ($propertyName == "Value") return $this->lookupValue();
        return $this->$propertyName;
    }

    /**
     * Change the {@link BizDataObj} name. This function is used in case of the current {@link BizDataObj}
     * inheriting from another {@link BizDataObj}, BizField's {@link BizDataObj} name should be changed to
     * current {@link BizDataObj} name, not the parent object name.
     *
     * @param string $bizObjName the name of {@link BizDataObj} object
     * @return void
     */
    public function adjustBizObjName($bizObjName)
    {
        if ($this->m_BizObjName != $bizObjName)
            $this->m_BizObjName = $bizObjName;
    }

    /**
     * Get string used in sql - with single quote, or without single quote in case of number
     *
     * @param mixed $input the value to add quote. If null, use the current field value
     * @return string string used in sql
     */
    public function getSqlValue($input=null)
    {
        $value = ($input !== null) ? $input : $this->m_Value;
        if ($value === null)
        {
            return "";
        }
        /*
        if ($this->m_Type != 'Number')
        {
            if (get_magic_quotes_gpc() == 0) {
                $val = addcslashes($value, "\000\n\r\\'\"\032");
            }
            return "'$value'";
        }
        */

        return $value;
    }

    /**
     * Check if the field is a LOB type column
     *
     * @return boolean true if the field points a LOB type column
     */
    public function isLobField()
    {
        return ($this->m_Type == 'Blob' || $this->m_Type == 'Clob');
    }

    /**
     * Get insert lob value when execute insert SQL. For a lob column, insert SQL first inserts
     * an empty entry in the lob column. Then use update to actually add the lob data.
     *
     * @param string $dbType database type
     * @return string the insert string for the lob column
     */
    public function getInsertLobValue($dbType)
    {
        if ($dbType == 'oracle' || $dbType == 'oci8')
        {
            if ($this->m_Type != 'Blob') return 'empty_blob()';
            if ($this->m_Type != 'Clob') return 'empty_clob()';
        }
        return 'null';
    }

	/**
     * Lookup the value of the field. Typically used in expression @:Field[name].Value
     *
     * @param boolean $formatted true if want to get the formatted value
     * @return mixed string or number depending on the field type
     */ 
	public function lookupValue()
	{
		$this->getDataObj()->getActiveRecord();
		return $this->getValue();
	}
	
    /**
     * Get the value of the field.
     *
     * @param boolean $formatted true if want to get the formatted value
     * @return mixed string or number depending on the field type
     */
    public function getValue($formatted=true)
    {
        // need to ensure that value are retrieved from source/cache
        //if ($this->getDataObj()->CheckDataRetrieved() == false)    	
        //$this->getDataObj()->getActiveRecord();

		if ($this->_prevValue == $this->m_Value) return $this->_getValueCache;
		
        $value = stripcslashes($this->m_Value);

        $value = $this->m_Value;
        if ($this->m_ValueExpression && trim($this->m_Column) == "")
        {
            $value = Expression::evaluateExpression($this->m_ValueExpression,$this->getDataObj());
        }
        if ($this->m_Format && $formatted)
        {
            $value = BizSystem::typeManager()->valueToFormattedString($this->m_Type, $this->m_Format, $value);
        }
		$this->_prevValue = $this->m_Value;
		$this->_getValueCache = $value;
        return $value;
    }

    /**
     * Set the value of the field.
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {    	
        $this->m_Value = $value;
    }

    /**
     * Save the old value to an internal variable
     *
     * @param mixed $oldValue
     * @return void
     */
    public function saveOldValue($oldValue=null)
    {
        if ($oldValue)
            $this->m_OldValue = $oldValue;
        else
            $this->m_OldValue = $this->m_Value;
    }

    /**
     * Get default value of the field
     *
     * @return string the default value of the field
     */
    public function getDefaultValue()
    {
        if($this->m_DefaultValue !== null)
            return Expression::evaluateExpression($this->m_DefaultValue, $this->getDataObj());
        return "";
    }

    /**
     * Get the value when a new record is created
     *
     * @return mixed the value of the field
     */
    public function getValueOnCreate()
    {
        if($this->m_ValueOnCreate !== null)
            return $this->getSqlValue(Expression::evaluateExpression($this->m_ValueOnCreate, $this->getDataObj()));
        return "";
    }

    /**
     * Get the value when a record is updated
     *
     * @return mixed the value of the field
     */
    public function getValueOnUpdate()
    {
        if($this->m_ValueOnUpdate !== null)
            return $this->getSqlValue(Expression::evaluateExpression($this->m_ValueOnUpdate, $this->getDataObj()));
        return "";
    }

    /**
     * Get the {@link BizDataObj} instance
     *
     * @return BizDataObj {@link BizDataObj} instance
     */
    protected function getDataObj()
    {
        return BizSystem::getObject($this->m_BizObjName);
    }

    /**
     * Check if the field is a required field
     *
     * @return boolean true if the field is a required field
     */
    public function checkRequired()
    {
        if (!$this->m_Required || $this->m_Required == "")
            return false;
        elseif ($this->m_Required == "Y")
            $required = true;
        elseif($required != "N")
            $required = false;
        else
            $required = Expression::evaluateExpression($this->m_Required, $this->getDataObj());

        return $required;
    }

    /**
     * Check value type
     *
     * @param mixed $value
     * @return mixed|boolean
     */
    public function checkValueType($value = null)
    {
        if(!$value)
        {
            $value = $this->m_Value;
        }
        $validator = BizSystem::getService(VALIDATE_SERVICE);
        switch ($this->m_Type)
        {
            case "Number":
                $result = is_numeric($value);
                break;

            case "Text":
                $result = is_string($value);
                break;

            case "Date":
                $result = $validator->date($value);
                break;
            /*
            case "Datetime":    // zend doesn't support date time
            	$result = $validator->date($value); 
            	break;
            
            case "Currency": 
            	$result = $validator->date($value); 
            	break;
            */
            case "Phone":
                $result = $validator->phone($value);
                break;

            default:
                $result = true;
                break;
        }

        return $result;
    }

    /**
     * Check if the field has valid value
     *
     * @return boolean true if validation is good
     */
    public function validate()
    {
        $ret = true;
        if ($this->m_Validator)
            $ret = Expression::evaluateExpression($this->m_Validator, $this->getDataObj());
        return $ret;
    }

}
?>