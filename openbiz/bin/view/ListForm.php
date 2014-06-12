<?php
/**
 * ListForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "BaseForm.php";
 /*
  * public methods: fetchDataSet, deleteRecord, selectRecord, removeRecord, runSearch, sortRecord, gotoPage
  */
class ListForm extends BaseForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('selectrecord','sortrecord','editrecord','copyrecord','deleterecord','removerecord','runsearch','gotopage','setpagesize','gotoselectedpage','switchform','loaddialog'); 
	
	public $m_Range = 10;
	public $m_SearchRule = null;
    public $m_FixSearchRule = null; // FixSearchRule is the search rule always applying on the search
    public $m_SortRule = null;
    protected $m_DefaultFixSearchRule = null;
	protected $queryParams = array();
	
	// vars for grid(list)
    protected $m_CurrentPage = 1;
    protected $m_StartItem = 1;
    public $m_TotalPages = 1;
    protected $m_TotalRecords = 0;
    protected $m_RecordSet = null;
    protected $m_RefreshData = false;

	protected function readMetadata($xmlArr)
    {
        parent::readMetaData($xmlArr);
		$this->m_Range = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["PAGESIZE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["PAGESIZE"] : $this->m_Range;
        $this->m_FixSearchRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        $this->m_SortRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SORTRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SORTRULE"] : null;
		$this->m_DefaultFixSearchRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
	}
	
	protected function inheritParentObj()
    {
        if (!$this->m_InheritFrom) return;
        $parentObj = BizSystem::getObject($this->m_InheritFrom);
		parent::inheritParentObj();
		$this->m_Range = $this->m_Range ? $this->m_Range : $parentObj->m_Range;
        $this->m_FixSearchRule = $this->m_FixSearchRule ? $this->m_FixSearchRule : $parentObj->m_FixSearchRule;
        $this->m_DefaultFixSearchRule = $this->m_DefaultFixSearchRule ? $this->m_DefaultFixSearchRule : $parentObj->m_DefaultFixSearchRule;
	}
	
	/**
     * Get/Retrieve Session data of this object
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function getSessionVars($sessionContext)
    {
        $sessionContext->getObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        $sessionContext->getObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessionContext->getObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
        $sessionContext->getObjVar($this->m_Name, "SearchRuleBindValues", $this->m_SearchRuleBindValues);
        $sessionContext->getObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessionContext->getObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessionContext->getObjVar($this->m_Name, "PageSize", $this->m_Range);
        $sessionContext->getObjVar($this->m_Name, "SearchPanelValues", $this->m_SearchPanelValues);
    }

    /**
     * Save object variable to session context
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function setSessionVars($sessionContext)
    {
        $sessionContext->setObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        $sessionContext->setObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessionContext->setObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);        
        $sessionContext->setObjVar($this->m_Name, "SearchRuleBindValues", $this->m_SearchRuleBindValues);
        $sessionContext->setObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessionContext->setObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessionContext->setObjVar($this->m_Name, "PageSize", $this->m_Range);
        $sessionContext->setObjVar($this->m_Name, "SearchPanelValues", $this->m_SearchPanelValues);        
    }
	
	/**
     * Fetch record set
     *
     * @return array array of record
     */
    public function fetchDataSet()
    {
        $dataObj = $this->getDataObj();

        if (!$dataObj) return null;

        if ($this->m_RefreshData)
            $dataObj->resetRules();
        else
            $dataObj->clearSearchRule();
/*
        if ($this->m_FixSearchRule)
        {
            if ($this->m_SearchRule)
                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
            else
                $searchRule = $this->m_FixSearchRule;
        }
        else
            $searchRule = $this->m_SearchRule;
		
        $dataObj->setSearchRule($searchRule);
		*/
		$dataObj->setQueryParameters($this->queryParams);
        if($this->m_StartItem>1)
        {
            $dataObj->setLimit($this->m_Range, $this->m_StartItem);
        }
        else
        {
            $dataObj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
        }      
        if($this->m_SortRule && $this->m_SortRule != $this->getDataObj()->m_SortRule)
        {
			$dataObj->setSortRule($this->m_SortRule);
        }          
        $resultRecords = $dataObj->fetch();
        $this->m_TotalRecords = $dataObj->count();
        if ($this->m_Range && $this->m_Range > 0)
            $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
        $selectedIndex = 0;
        
        //if current page is large than total pages ,then reset current page to last page
        if($this->m_CurrentPage>$this->m_TotalPages && $this->m_TotalPages>0)
        {
        	$this->m_CurrentPage = $this->m_TotalPages;
        	$dataObj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
        	$resultRecords = $dataObj->fetch();
        }
        
        $this->getDataObj()->setActiveRecord($resultRecords[$selectedIndex]);

		if(!$this->m_RecordId)
		{
			$this->m_RecordId = $resultRecords[0]["Id"];
		}else{
			$foundRecordId = false;
			foreach($resultRecords as $record)
			{
				if($this->m_RecordId==$record['Id'])
				{
					$foundRecordId = true;
				}
			}
			if($foundRecordId == false)
			{
				$this->m_RecordId=$result[0]['Id'];
			}			
		}
		
        return $resultRecords;
    }
	
	public function switchForm($formName=null, $id=null)
    {    	
		if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');
		$this->formHelper->switchForm($formName, $id);
    }
	
	public function loadDialog($formName=null, $id=null)
    {    	
		if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');
		$this->formHelper->loadDialog($formName, $id);
    }
	
	/**
     * Delete Record
     * NOTE: use redirectpage attr of eventhandler to redirect or redirect to previous page by default
     *
     * @param string $id
     * @return void
     */
    public function deleteRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
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
        }
		//$this->runEventLog();
        $this->rerender();
    }

    /**
     * Select Record
     *
     * @param string $recId
     * @access remote
     * @return void
     */
    public function selectRecord($recId)
    {
        if ($recId==null || $recId=='')
            $recId = BizSystem::clientProxy()->getFormInputs('_selectedId');
        $this->m_RecordId = $recId;
        if($this->getDataObj()){
        	$this->getDataObj()->setActiveRecordId($this->m_RecordId);
        }
        $this->rerender(false); // not redraw the this form, but draw the subforms
        //$this->rerender(); 
    }
	
	/**
     * Edit Record
     * NOTE: append fld:Id=$id to the redirect page url
     *
     * @param mixed $id
     * @return void
     */
    public function editRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');
	
        if (!isset($id))
        {
            BizSystem::clientProxy()->showClientAlert($this->getMessage("PLEASE_EDIT_A_RECORD"));
            return;
        }
		
		$_REQUEST['fld:Id'] = $id;

        $this->formHelper->processPostAction();
    }
	
	/**
     * Copy record to new record
     *
     * @param mixed $id id of record that want to copy,
     * it parameter not passed, id is '_selectedId'
     * @return void
     */
    public function copyRecord($id=null)
    {
        $this->editRecord($id);
    }
	
    /**
     * Remove the record out of the associate relationship
     *
     * @return void
     */
    public function removeRecord ()
    {
    	if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
        { 
        	$rec = $this->getDataObj()->fetchById($id);
	        $ok = $this->getDataObj()->removeRecord($rec, $bPrtObjUpdated);
	        if (! $ok)
	            return $this->processDataObjError($ok);
        }        

        $this->runEventLog();
        $this->rerender();
		if($this->m_ParentFormName)
		{
			$this->renderParent();
		}
    }
	
	/**
     * Goto page specified by $page parameter, and ReRender
     * If page not specified, goto page 1
     *
     * @param number $page
     */
    public function gotoPage($page=1)
    {
        $tgtPage = intval($page);
        if ($tgtPage == 0) $tgtPage = 1;
        $this->m_CurrentPage = $tgtPage;
        $this->rerender();
    }
    public function gotoSelectedPage($elemName)
    {
        $page = BizSystem::clientProxy()->getFormInputs(str_replace(".","_", $this->m_Name).'_'.$elemName);
    	$this->gotoPage($page);
    }
    public function setPageSize($elemName)
    {
        $pagesize = BizSystem::clientProxy()->getFormInputs(str_replace(".","_", $this->m_Name).'_'.$elemName);
    	$this->m_Range=$pagesize;
    	$this->rerender();
    }  
	
    /**
     * Sort Record, for list form
     *
     * @param string $sortCol column name to sort
     * @param string $order 'dec' (decending) or 'asc' (ascending)
     * @access remote
     * @return void
     */
    public function sortRecord($sortCol, $order='ASC')
    {
        $element = $this->getElement($sortCol);
        // turn off the OnSort flag of the old onsort field
        $element->setSortFlag(null);
        // turn on the OnSort flag of the new onsort field
        if ($order == "ASC")
            $order = "DESC";
        else
            $order = "ASC";
        $element->setSortFlag($order);

        // change the sort rule and issue the query
        $this->getDataObj()->setSortRule("[" . $element->m_FieldName . "] " . $order);

        // move to 1st page
        $this->m_CurrentPage = 1;
        $this->m_SortRule = "";

        $this->rerender();
    }

    /**
     * Run Search
     *
     * @return void
     */
    public function runSearch()
    {
        /*static $isSearchHelperLoaded = false;
        
        if (!$isSearchHelperLoaded) {
            include_once(OPENBIZ_BIN."/easy/SearchHelper.php");
            $isSearchHelperLoaded = true;
        }*/
		$queryArray = array();
        foreach ($this->m_SearchPanel as $element)
        {       	
			if (!$element->m_FieldName)
				continue;

			$value = BizSystem::clientProxy()->getFormInputs($element->m_Name);  
			$this->m_SearchPanelValues[$element->m_FieldName] = $value;	// ??? neede
			if($element->m_FuzzySearch=="Y")
			{
				$value="*$value*";
			}
			if ($value!='')
			{	               
				$this->queryParams[$element->m_FieldName] = $value;
			}
        }

        $this->m_RefreshData = true;

        $this->m_CurrentPage = 1;

        BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::runSearch(), SearchRule=".$this->m_SearchRule);

		//$recArr = $this->readInputRecord();
		//$this->m_SearchPanelValues = $recArr;
        //$this->runEventLog();
        $this->rerender();
    }

    /**
     * Reset search
     * 
     * @return void
     */
    public function resetSearch()
    {
        $this->m_SearchRule = "";
        $this->m_RefreshData = true;
        $this->m_CurrentPage = 1;
        $this->runEventLog();
        $this->rerender();
    }
    
    public function setSearchRule($searchRule, $searchRuleBindValues=null)
    {
    	$this->m_SearchRule = $searchRule;
    	$this->m_SearchRuleBindValues = $searchRuleBindValues;
    	$this->m_RefreshData = true;
        $this->m_CurrentPage = 1;
    }
}
?>