<?php
/**
 * DetailForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "BaseForm.php";
 /*
  * public methods: fetchData, deleteRecord
  */
class DetailForm extends BaseForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('deleterecord','switchform'); 
	
	public $m_RecordId;
	public $m_ActiveRecord;
	
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
     * Delete Record
     * NOTE: use redirectpage attr of eventhandler to redirect or redirect to previous page by default
     *
     * @param string $id
     * @return void
     */
    public function deleteRecord($id)
	{  	
		$dataRec = $this->getDataObj()->fetchById($id);
		//$this->getDataObj()->setActiveRecord($dataRec);
		
		// take care of exception
		try {
			$dataRec->delete();
		} catch (BDOException $e) {
			// call $this->processBDOException($e);
			$this->processBDOException($e);
			return;
		}

        //$this->runEventLog();
        $this->processPostAction();
	}
}
?>