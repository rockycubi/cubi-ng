<?php
/**
 * CopyForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "NewForm.php";
 /*
  * public methods: fetchData, insertRecord, 
  */
class CopyForm extends NewForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('insertrecord','switchform'); 
	
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
}
?>