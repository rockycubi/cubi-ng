<?php
/**
 * NewForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "InputForm.php";
 /*
  * public methods: fetchData, insertRecord, 
  */
class NewForm extends InputForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('insertrecord','switchform'); 
	
	public $m_RecordId;
	public $m_ActiveRecord;
	
	/**
     * Fetch single record
     *
     * @return array one record array
     */
    public function fetchData()
    {    	
        return $this->getNewRecord();
    }
	
	/**
     * Insert new record
     *
     * @return mixed
     */
    public function insertRecord()
    {
        $recArr = $this->readInputRecord();
        //$this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }

        $this->_doInsert($recArr);
        
        //$this->commitFormElements(); // commit change in FormElement

        // in case of popup form, close it, then rerender the parent form
        /*if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }*/

        $this->formHelper->processPostAction();
    }

    /**
     * Do insert record
     *
     * @param array $inputRecord
     * @return void
     */
    protected function _doInsert($inputRecord)
    {
       
        $dataRec = new DataRecord(null, $this->getDataObj());

        // $inputRecord['Id'] = null; // comment it out for name PK case 
        foreach ($inputRecord as $k => $v)
            $dataRec[$k] = $v; // or $dataRec->$k = $v;

        try
        {
           $dataRec->save();
        }
        catch (ValidationException $e)
        {
            $errElements = $this->getErrorElements($e->m_Errors);
            if(count($e->m_Errors)==count($errElements)){
            	$this->formHelper->processFormObjError($errElements);
            }else{            	
            	$errmsg = implode("<br />",$e->m_Errors);
		        BizSystem::clientProxy()->showErrorMessage($errmsg);
            }
            return;
        }
        catch (BDOException $e)
        {
            $this->processBDOException($e);
            return;
        }
		$this->m_ActiveRecord = null;
        $this->getActiveRecord($dataRec["Id"]);

        //$this->runEventLog();
        return $dataRec["Id"];
    }
	
	/**
     * Get new record
     *
     * @return array
     */
    protected function getNewRecord()
    {
    	if($this->getDataObj())
    	{
        	$recArr = $this->getDataObj()->newRecord();
    	}
        if (! $recArr)
            return null;
        // load default values if new record value is empty
        $defaultRecArr = array();
        foreach ($this->m_DataPanel as $element)
        {
            if ($element->m_FieldName)
            {
                $defaultRecArr[$element->m_FieldName] = $element->getDefaultValue();
            }
        }
        foreach ($recArr as $field => $val)
        {
            if ($val == "" && $defaultRecArr[$field] != "")
            {
                $recArr[$field] = $defaultRecArr[$field];
            }
        }
        return $recArr;
    }
}
?>