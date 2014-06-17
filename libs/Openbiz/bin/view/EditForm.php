<?php
/**
 * EditForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "InputForm.php";
 /*
  * public methods: fetchData, updateRecord, 
  */
class EditForm extends InputForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('updaterecord','switchform'); 

	// get request parameters from the url
	protected function getUrlParameters()
	{
		if (isset($_REQUEST['fld:Id'])) {
			$this->m_RecordId = $_REQUEST['fld:Id'];
		}
	}
	
	public function render()
	{
		$this->getUrlParameters();
		if (empty($this->m_RecordId))
        {
            BizSystem::clientProxy()->showClientAlert($this->getMessage("PLEASE_EDIT_A_RECORD"));
            return;
        }
		return parent::render();
	}
	
	/**
     * Fetch single record
     *
     * @return array one record array
     */
    public function fetchData()
    {    	
        // if has valid active record, return it, otherwise do a query
        if ($this->m_ActiveRecord != null)
            return $this->m_ActiveRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;
		
        // TODO: use getDataById to fetch one record
		$dataRec = $dataObj->fetchById($this->m_RecordId);
		return $dataRec->toArray();
    }

	/**
     * Update record
     *
     * @return mixed
     */
    public function updateRecord()
    {
		$recArr = $this->readInputRecord();
		
		$this->m_RecordId = $recArr['Id'];
        $currentRec = $this->getDataObj()->fetchById($this->m_RecordId);
		
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
	
		if ($this->_doUpdate($recArr, $currentRec) == false) return;
			
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
     * Do update record
     *
     * @param array $inputRecord
     * @param array $currentRecord
     * @return void
     */
    protected function _doUpdate($inputRecord, $currentRecord)
    {
        $dataRec = new DataRecord($currentRecord, $this->getDataObj());

        foreach ($inputRecord as $k => $v){
           	$dataRec[$k] = $v; // or $dataRec->$k = $v;
        }

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
            return false;
        }
        catch (BDOException $e)
        {
            $this->processBDOException($e);
            return false;
        }
		$this->m_ActiveRecord = null;
        $this->getActiveRecord($dataRec["Id"]);

        //$this->runEventLog();
        return true;
    }
}
?>